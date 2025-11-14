<?php
// display_schedule.php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

// Check login
if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit();
}

$org_name = $_SESSION['org_name'] ?? 'Organization';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Load timetable.json if exists
$timetablePath = __DIR__ . '/timetable.json';
$timetableData = [];
if (file_exists($timetablePath)) {
    $jsonStr = file_get_contents($timetablePath);
    $timetableData = json_decode($jsonStr, true) ?: [];
}

// Helpers
function toDateTime($hhmm) {
    return DateTime::createFromFormat('H:i', $hhmm) ?: new DateTime('00:00');
}

// Accepts "HH:MM"  OR  "HH:MM-HH:MM"  and returns ["HH:MM","HH:MM"]
function normalizeRange($timeStr) {
    $timeStr = trim((string)$timeStr);
    if (strpos($timeStr, '-') !== false) {
        [$s, $e] = array_map('trim', explode('-', $timeStr, 2));
        return [$s, $e];
    }
    // If only start is given, infer +1 hour for end
    $start = toDateTime($timeStr);
    $end   = clone $start;
    $end->modify('+1 hour');
    return [$start->format('H:i'), $end->format('H:i')];
}

function to12h($hhmm) {
    return date('gA', strtotime($hhmm));
}

// Group by class and sort by start time; assign periods
$grouped = [];  // class => [ [entry + start_dt + end_dt] ... ]
foreach ($timetableData as $entry) {
    $class = $entry['class'] ?? null;
    $time  = $entry['time']  ?? null;
    if (!$class || !$time) continue;

    [$start, $end] = normalizeRange($time);
    $entry['_start'] = $start;
    $entry['_end']   = $end;

    $grouped[$class][] = $entry;
}

// Sort and assign periods starting from 1
foreach ($grouped as $cls => &$rows) {
    usort($rows, function($a, $b) {
        return strcmp($a['_start'], $b['_start']);
    });
    $p = 1;
    foreach ($rows as &$r) {
        $r['_period'] = $p++;
    }
}
unset($rows, $r);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($org_name) ?> — Schedule</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/sa_dashboard.css" rel="stylesheet" />
    <link href="css/footer.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        html, body { height: 100%; margin: 0; background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1; padding: 30px 20px; }
        .table-responsive { margin-top: 20px; }
        .class-table { margin-bottom: 60px; box-shadow: 0 0 15px rgb(0 0 0 / 0.1); background: #fff; border-radius: 8px; padding: 15px; }
        .class-table h4 { border-bottom: 2px solid #0d6efd; padding-bottom: 6px; margin-bottom: 15px; color: #0d6efd; font-weight: 700; letter-spacing: 0.03em; }
        table { border-radius: 8px; overflow: hidden; }
        thead tr th { background-color: #0d6efd; color: white; text-align: center; font-weight: 600; padding: 12px 8px; border: none; }
        tbody tr td { vertical-align: middle; font-size: 0.95rem; padding: 10px 8px; border-top: 1px solid #dee2e6; text-align: center; min-width: 140px; }
        tbody tr td .subject { display: block; color: #212529; font-weight: 500; font-size: 1rem; margin-bottom: 6px; }
        tbody tr td .teacher { font-weight: 700; color: #0d6efd; font-size: 0.9rem; }
        @media (max-width: 768px) {
            thead tr th, tbody tr td { font-size: 10px; padding: 6px 4px; }
        }
        @media print {
            body * { visibility: hidden; }
            #schedule-body, #schedule-body * { visibility: visible; }
            #schedule-body { position: absolute; left: 0; top: 0; width: 100%; }
        }

        /* floating controls */
        #top-buttons {
            position: fixed; bottom: 20px; right: 15px; z-index: 1050;
            background: white; border: 1px solid #ccc; padding: 8px 12px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <!-- Navbar -->
    <header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="sa_dashboard.php"><?= htmlspecialchars($org_name) ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="sa_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="sa_profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="member.php">Member</a></li>
                    <li class="nav-item"><a class="nav-link active" href="display_schedule.php">Schedule</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button" data-bs-toggle="dropdown">Manage</a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="manageDropdown">
                            <li><a class="dropdown-item" href="manage_teachers.php">Teachers</a></li>
                            <li><a class="dropdown-item" href="manage_students.php">Students</a></li>
                            <li><a class="dropdown-item" href="classes.php">Classes</a></li>
                            <li><a class="dropdown-item" href="subjects.php">Subjects</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="btn btn-danger ms-2" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Floating controls -->
    <div id="top-buttons" class="btn-group" role="group" aria-label="Schedule controls">
        <form method="post" action="generate_schedule.php" style="display:inline;">
            <button type="submit" class="btn btn-primary" title="Generate timetable">
                <i class="bi bi-arrow-repeat"></i> Generate
            </button>
        </form>
        <button id="share-btn" class="btn btn-success" title="Share schedule as image">
            <i class="bi bi-share-fill"></i> Share
        </button>
        <button id="print-btn" class="btn btn-secondary" title="Print schedule">
            <i class="bi bi-printer-fill"></i> Print
        </button>
    
        <form method="post" action="save.php">
            <input type="hidden" name="save_schedule" value="1">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-save-fill"></i> Save
            </button>
        </form>
    </div>

    <main class="container-fluid">
        <h1 class="mb-4">📅 Generated Class Timetable</h1>

        <?php if (!empty($_SESSION['gen_error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['gen_error']) ?></div>
            <?php unset($_SESSION['gen_error']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['gen_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['gen_success']) ?></div>
            <?php unset($_SESSION['gen_success']); ?>
        <?php endif; ?>

        <div id="schedule-body">
            <?php
            if (empty($grouped)) {
                echo "<div class='alert alert-warning'>No schedule found. Click <strong>Generate</strong> to create a timetable.</div>";
            } else {
                foreach ($grouped as $className => $rows) {
                    echo "<div class='class-table'>";
                    echo "<h4>Class: " . htmlspecialchars($className) . "</h4>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-bordered table-striped align-middle text-center'>";
                    echo "<thead><tr>";

                    // Period headers with time ranges
                    foreach ($rows as $r) {
                        [$start, $end] = normalizeRange($r['time'] ?? ($r['_start'] . '-' . $r['_end']));
                        $label = "Period " . intval($r['_period']);
                        $timeLabel = to12h($start) . " - " . to12h($end);
                        echo "<th><strong>{$label}</strong><br><small>" . htmlspecialchars($timeLabel) . "</small></th>";
                    }

                    echo "</tr></thead><tbody><tr>";

                    // Cells: subject + teacher
                    foreach ($rows as $r) {
                        $subject = htmlspecialchars($r['subject'] ?? '');
                        $teacher = htmlspecialchars($r['teacher'] ?? '');
                        echo "<td><div class='subject'>{$subject}</div><div class='teacher fw-bold'>👨‍🏫 {$teacher}</div></td>";
                    }
                    echo "</tr></tbody></table></div></div>";
                }
            }
            ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('share-btn').addEventListener('click', function () {
    const scheduleBody = document.getElementById('schedule-body');
    html2canvas(scheduleBody).then(canvas => {
        const link = document.createElement('a');
        link.download = 'schedule.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
});

document.getElementById('print-btn').addEventListener('click', function () {
    window.print();
});
</script>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

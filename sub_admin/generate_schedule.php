<?php
// generate_schedule.php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/config.php';

// --- SECURITY: Check login + org ---
if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit();
}

$sub_admin_id = $_SESSION['sub_admin_id'];

// Fetch org_name if not already in session
if (empty($_SESSION['org_name'])) {
    $stmt = $conn->prepare("SELECT org_name FROM sub_admin WHERE id = ?");
    $stmt->bind_param("i", $sub_admin_id);
    $stmt->execute();
    $stmt->bind_result($org_name);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['org_name'] = $org_name ?: 'Organization';
} else {
    $org_name = $_SESSION['org_name'];
}

// Increase execution time (Python may take a while)
set_time_limit(300);

// === STEP 1: FETCH CLASSES ===
$stmt = $conn->prepare("
    SELECT 
        id, 
        class_name, 
        subjects, 
        TIME_FORMAT(start_time, '%H:%i') AS start_time, 
        TIME_FORMAT(end_time, '%H:%i')   AS end_time 
    FROM classes 
    WHERE sub_admin_id = ?
");
$stmt->bind_param("i", $sub_admin_id);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    $row['subjects'] = array_values(array_filter(array_map('trim', explode(',', (string)$row['subjects']))));
    $classes[] = $row;
}
$stmt->close();

// === STEP 2: FETCH TEACHERS ===
$tq = $conn->prepare("
    SELECT 
        id,
        reg_no,
        name,
        department,
        subjects,
        available,
        phone,
        email
    FROM teacher 
    WHERE org_id = ?
");
$tq->bind_param("i", $sub_admin_id);
$tq->execute();
$resT = $tq->get_result();

$teachers = [];
while ($row = $resT->fetch_assoc()) {
    $row['subjects']  = array_values(array_filter(array_map('trim', explode(',', (string)$row['subjects']))));
    $row['available'] = array_values(array_filter(array_map('trim', explode(',', (string)$row['available']))));
    $teachers[] = $row;
}
$tq->close();

// === STEP 3: WRITE timetable_data.json ===
$data = [
    'organization' => $org_name,
    'classes'      => $classes,
    'teachers'     => $teachers
];

$jsonPath = __DIR__ . '/timetable_data.json';
if (file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    $_SESSION['gen_error'] = "Failed to write timetable_data.json.";
    header("Location: display_schedule.php");
    exit();
}

// === STEP 4: CLEAR old timetable.json ===
$timetablePath = __DIR__ . '/timetable.json';
if (file_exists($timetablePath)) {
    @unlink($timetablePath);
}

// === STEP 5: RUN PYTHON GA ===
$python = PYTHON_PATH; // from config.php
$script = __DIR__ . DIRECTORY_SEPARATOR . 'genetic_algorithm.py';

// Escape both
$pythonEsc = escapeshellcmd($python);
$scriptEsc = escapeshellarg($script);
$command   = $pythonEsc . ' ' . $scriptEsc;

// Run and capture output (stderr redirected)
$output = shell_exec($command . " 2>&1");

// Log output for debugging
$logLine = date('Y-m-d H:i:s') . " | Command: {$command}\n" . ($output ?: "(no output)") . "\n\n";
file_put_contents(__DIR__ . '/python_log.txt', $logLine, FILE_APPEND);

// === STEP 6: CHECK RESULT ===
if (!file_exists($timetablePath)) {
    $_SESSION['gen_error'] = "Python script failed to create timetable.json. Check python_log.txt for details.";
} else {
    $_SESSION['gen_success'] = "Schedule generated successfully.";
}

// Redirect back to display
header("Location: display_schedule.php");
exit();

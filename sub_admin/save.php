<?php
// save.php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

// Ensure sub_admin is logged in
if (!isset($_SESSION['sub_admin_id'])) {
    $_SESSION['gen_error'] = "Organization ID missing. Please log in again.";
    header("Location: sa_login.php");
    exit;
}

$org_id = $_SESSION['sub_admin_id']; // should be numeric DB id
$timetablePath = __DIR__ . '/timetable.json';

// Make sure we read the latest file contents
clearstatcache(true, $timetablePath);

if (!is_file($timetablePath)) {
    $_SESSION['gen_error'] = "No timetable found. Please generate one first.";
    header("Location: display_schedule.php");
    exit;
}

$jsonStr = @file_get_contents($timetablePath);
if ($jsonStr === false) {
    $_SESSION['gen_error'] = "Cannot read timetable.json.";
    header("Location: display_schedule.php");
    exit;
}

$timetableData = json_decode($jsonStr, true);
if (!is_array($timetableData)) {
    $_SESSION['gen_error'] = "Invalid timetable format.";
    header("Location: display_schedule.php");
    exit;
}

/** Helpers **/
function normalizeRangeTo24($timeStr) {
    $timeStr = trim((string)$timeStr);
    if ($timeStr === '') return ['00:00', '00:00'];

    if (strpos($timeStr, '-') !== false) {
        [$s, $e] = array_map('trim', explode('-', $timeStr, 2));
        $s24 = date('H:i', strtotime($s));
        $e24 = date('H:i', strtotime($e));
        return [$s24, $e24];
    }
    $start = date('H:i', strtotime($timeStr));
    $endTs = strtotime($timeStr . ' +1 hour');
    return [$start, date('H:i', $endTs)];
}

/** Group rows by class and normalize time to 24h **/
$classWise = [];
foreach ($timetableData as $row) {
    $cls = trim($row['class'] ?? '');
    if ($cls === '') $cls = 'Unassigned';

    $timeRaw = $row['time'] ?? '';
    [$start24, $end24] = normalizeRangeTo24($timeRaw);

    $classWise[$cls][] = [
        'time'    => $start24 . '-' . $end24,          // store as 24h "HH:MM-HH:MM"
        'subject' => (string)($row['subject'] ?? ''),
        'teacher' => (string)($row['teacher'] ?? '')
    ];
}

/** Sort each class by start time so displays come out right later **/
foreach ($classWise as &$rows) {
    usort($rows, function ($a, $b) {
        $sa = explode('-', $a['time'])[0] ?? '00:00';
        $sb = explode('-', $b['time'])[0] ?? '00:00';
        return strcmp($sa, $sb);
    });
}
unset($rows);

/** Persist: delete then insert, in a transaction **/
$conn->begin_transaction();
try {
    // Hard delete everything for this org
    $del = $conn->prepare("DELETE FROM schedule WHERE org_id = ?");
    if (!$del) throw new Exception("Prepare delete failed: " . $conn->error);
    // If your org_id is non-numeric in DB, change 'i' to 's'
    $del->bind_param("i", $org_id);
    if (!$del->execute()) throw new Exception("Delete failed: " . $del->error);
    $deleted = $del->affected_rows;
    $del->close();

    // (Re)insert one row per class
    $ins = $conn->prepare("INSERT INTO schedule (org_id, class_name, schedule_json, created_at) VALUES (?, ?, ?, NOW())");
    if (!$ins) throw new Exception("Prepare insert failed: " . $conn->error);

    foreach ($classWise as $className => $scheduleRows) {
        $scheduleJson = json_encode($scheduleRows, JSON_UNESCAPED_UNICODE);
        if ($scheduleJson === false) {
            throw new Exception("JSON encode failed for class {$className}");
        }
        // org_id i, class_name s, json s
        $ins->bind_param("iss", $org_id, $className, $scheduleJson);
        if (!$ins->execute()) {
            throw new Exception("Insert failed for class {$className}: " . $ins->error);
        }
    }
    $ins->close();

    $conn->commit();
    $_SESSION['gen_success'] = "Schedule saved successfully! ";
} catch (Throwable $e) {
    $conn->rollback();
    error_log("save.php error (org_id={$org_id}): " . $e->getMessage());
    $_SESSION['gen_error'] = "Failed to save schedule. " . $e->getMessage();
}

header("Location: display_schedule.php");
exit;

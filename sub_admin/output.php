<?php

include('includes/db_connect.php');

// --- SECURITY: Check if sub-admin is logged in ---
if (!isset($_SESSION['sub_admin_id']) || !isset($_SESSION['org_name'])) {
    die("❌ Missing session ID or organization name.");
}

$sub_admin_id = $_SESSION['sub_admin_id'];
$org_name = $_SESSION['org_name'];

// Increase execution time for Python (5 mins)
set_time_limit(300);

// === STEP 1: FETCH CLASSES ===
$stmt = $conn->prepare("
    SELECT 
        id, 
        class_name, 
        subjects, 
        TIME_FORMAT(start_time, '%H:%i') AS start_time, 
        TIME_FORMAT(end_time, '%H:%i') AS end_time 
    FROM classes 
    WHERE sub_admin_id = ?
");
$stmt->bind_param("i", $sub_admin_id);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    $row['subjects'] = array_map('trim', explode(',', $row['subjects']));
    $classes[] = $row;
}
$stmt->close();

// === STEP 2: FETCH TEACHERS ===
$teacher_query = $conn->prepare("
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
$teacher_query->bind_param("i", $sub_admin_id);
$teacher_query->execute();
$teacher_result = $teacher_query->get_result();

$teachers = [];
while ($row = $teacher_result->fetch_assoc()) {
    $row['subjects'] = array_map('trim', explode(',', $row['subjects']));
    $row['available'] = array_map('trim', explode(',', $row['available']));
    $teachers[] = $row;
}
$teacher_query->close();

// === STEP 3: SAVE JSON FILE FOR PYTHON ===
$data = [
    'organization' => $org_name,
    'classes' => $classes,
    'teachers' => $teachers
];

$json_file = __DIR__ . '/timetable_data.json';
if (!file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT))) {
    die("❌ Failed to write $json_file.");
}

// Clear old timetable.json if exists
$timetable_json = __DIR__ . '/timetable.json';
if (file_exists($timetable_json)) {
    unlink($timetable_json);
}

// === STEP 4: RUN PYTHON SCRIPT ===
$pythonPath = 'py';  // Using py launcher found at C:\Windows\py.exe
$scriptPath = __DIR__ . '\\genetic_algorithm.py';

$command = escapeshellcmd("$pythonPath \"$scriptPath\"");
$output = shell_exec($command . " 2>&1");

// Log Python output to file for debugging
file_put_contents(__DIR__ . '/python_log.txt', date('Y-m-d H:i:s') . "\n" . $output . "\n\n", FILE_APPEND);

// === STEP 5: CHECK TIMETABLE OUTPUT ===
if (!file_exists($timetable_json)) {
    die("❌ Python script failed to create timetable.json. Check python_log.txt for details.");
}

// === STEP 6: REDIRECT TO DISPLAY ===
header('Location: display_schedule.php');
exit;
?>

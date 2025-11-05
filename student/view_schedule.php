<?php
session_start();
include('../includes/db_connect.php'); // adjust path

if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Please log in first.'); window.location.href='../s_login.php';</script>";
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student info (name, semester, module)
$stmt = $conn->prepare("SELECT name, semester, module FROM student WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Student not found.'); window.location.href='../s_login.php';</script>";
    exit;
}

$student = $result->fetch_assoc();

// Debug info: show student info
echo "<pre>Student Info:\n";
print_r($student);
echo "</pre>";

// Dynamically fetch schedule based on module and semester
$schedule_stmt = $conn->prepare("SELECT period, time, subject, teacher FROM schedule WHERE semester = ? AND module = ? ORDER BY period");
$schedule_stmt->bind_param("ss", $student['semester'], $student['module']);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

// Debug info: show query results
echo "<pre>Schedule Query Results:\n";
print_r($schedule_result->fetch_all(MYSQLI_ASSOC));
echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($student['name']) ?> - Schedule</title>
</head>
<body>
    <h2><?= htmlspecialchars($student['name']) ?>'s Schedule (<?= htmlspecialchars($student['semester']) ?>)</h2>
    <?php if ($schedule_result->num_rows > 0): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Period</th>
                <th>Time</th>
                <th>Subject</th>
                <th>Teacher</th>
            </tr>
            <?php 
            // Re-run fetch loop because fetch_all() above moved the pointer
            $schedule_stmt->execute();
            $schedule_result = $schedule_stmt->get_result();
            while ($row = $schedule_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['period']) ?></td>
                    <td><?= htmlspecialchars($row['time']) ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= htmlspecialchars($row['teacher']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No schedule found for your module or semester.</p>
    <?php endif; ?>
</body>
</html>

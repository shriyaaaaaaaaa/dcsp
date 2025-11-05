<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['student_id'])) {
    die("Please login first");
}

$student_id = $_SESSION['student_id'];

// Get student info
$query = $conn->prepare("SELECT * FROM student WHERE id = ?");
$query->bind_param("i", $student_id);
$query->execute();
$student = $query->get_result()->fetch_assoc();

echo "<h2>Debug Information</h2>";
echo "<h3>Student Info:</h3>";
echo "<pre>";
print_r($student);
echo "</pre>";

$org_id = $student['org_id'];

echo "<h3>Searching for classes with org_id: " . htmlspecialchars($org_id) . "</h3>";

// Try different query methods
echo "<h4>Method 1: Direct comparison</h4>";
$result1 = $conn->query("SELECT * FROM schedule WHERE org_id = '$org_id'");
echo "Found: " . $result1->num_rows . " classes<br>";
while($row = $result1->fetch_assoc()) {
    echo "- " . $row['class_name'] . " (ID: " . $row['id'] . ", org_id: " . $row['org_id'] . ")<br>";
}

echo "<h4>Method 2: CAST comparison</h4>";
$result2 = $conn->query("SELECT * FROM schedule WHERE CAST(org_id AS CHAR) = CAST('$org_id' AS CHAR)");
echo "Found: " . $result2->num_rows . " classes<br>";
while($row = $result2->fetch_assoc()) {
    echo "- " . $row['class_name'] . " (ID: " . $row['id'] . ", org_id: " . $row['org_id'] . ")<br>";
}

echo "<h4>All schedules in database:</h4>";
$all = $conn->query("SELECT id, org_id, class_name FROM schedule ORDER BY org_id");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Org ID</th><th>Class Name</th></tr>";
while($row = $all->fetch_assoc()) {
    echo "<tr><td>" . $row['id'] . "</td><td>" . $row['org_id'] . "</td><td>" . $row['class_name'] . "</td></tr>";
}
echo "</table>";
?>
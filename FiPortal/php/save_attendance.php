<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$sessionId = $_POST["sessionId"];
$attendanceData = $_POST["attendance"];

$stmt = $conn->prepare("
    INSERT INTO attendance (sessionId, studentId, status)
    VALUES (?, ?, ?)
");

foreach ($attendanceData as $studentId => $status) {
    $stmt->execute([$sessionId, $studentId, $status]);
}

$_SESSION['message'] = "Attendance recorded successfully!";
header("Location: faculty_dashboard.php");
exit;
?>

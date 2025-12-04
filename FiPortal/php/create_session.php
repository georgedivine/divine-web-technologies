<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "config.php";

//faculty only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index.html");
    exit;
}

$faculty_id = $_SESSION['user_id'];

//fetch courses from this faculty
$courses = $conn->prepare("SELECT * FROM courses WHERE faculty_id = ?");
$courses->execute([$faculty_id]);


$attendance_code = rand(100000, 999999);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $course_id = $_POST["courseId"];
    $date = date("Y-m-d");
    $attendance_code = rand(100000, 999999);

    $stmt = $conn->prepare("
        INSERT INTO class_sessions (courseId, facultyId, sessionDate, attendanceCode)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$course_id, $faculty_id, $date, $attendance_code]);

    $_SESSION['message'] = "Session created! Attendance code: $attendance_code";
    header("Location: faculty_dashboard.php");
    exit;
}

?>

<!DOCTYPE html>

<html>
<head>
    <title>Create Class Session</title>
</head>
<link rel="stylesheet" href="../style/style.css">

<body>

<h2>Create New Class Session</h2>

<form method="POST">
    <label>Select Course:</label>
    <select name="courseId" required>
        <?php while ($c = $courses->fetch(PDO::FETCH_ASSOC)) : ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <button type="submit">Create Session</button>
</form>

</body>
</html>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require "config.php";

//faculty only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index.html");
    exit;
}

if (!isset($_GET['session'])) {
    die("Session not specified.");
}

$sessionId = $_GET['session'];

$session = $conn->prepare("SELECT * FROM class_sessions WHERE id = ?");
$session->execute([$sessionId]);
$sessionData = $session->fetch(PDO::FETCH_ASSOC);

if (!$sessionData) {
    die("Session not found.");
}

$courseId = $sessionData['courseId'];

//get students enrolled in the course
$students = $conn->prepare("
    SELECT u.id, u.email 
    FROM users u
    JOIN course_requests cr ON u.id = cr.student_id
    WHERE cr.course_id = ? AND cr.status = 'approved'
");
$students->execute([$courseId]);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>

<h2>Mark Attendance for Session #<?= $sessionId ?></h2>

<form method="POST" action="save_attendance.php">

    <input type="hidden" name="sessionId" value="<?= $sessionId ?>">

    <?php while ($s = $students->fetch(PDO::FETCH_ASSOC)) : ?>
        <div class="attendance-row">
            <label><?= htmlspecialchars($s['email']) ?></label>

            <select name="attendance[<?= $s['id'] ?>]" required>
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
            </select>
        </div>
        <br>
    <?php endwhile; ?>

    <button type="submit">Save Attendance</button>
</form>

</body>
</html>

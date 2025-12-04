<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    header("Location: login_process.php");
    exit();
}

$student_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $session_id = $_POST['session_id'];
    $input_code = trim($_POST['attendance_code']);

    //fetch actual code
    $stmt = $conn->prepare("SELECT attendanceCode FROM class_sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$session){
        die("Invalid session.");
    }

    if($input_code === $session['attendanceCode']){
        //mark attendance
        $stmt2 = $conn->prepare("INSERT INTO attendance (studentId, sessionId, status) VALUES (?, ?, 'present')");
        $stmt2->execute([$student_id, $session_id]);
        $_SESSION['message'] = "Attendance marked successfully!";
    } else {
        $_SESSION['message'] = "Incorrect code!";
    }

    header("Location: dashboard.php");
    exit();
}
?>

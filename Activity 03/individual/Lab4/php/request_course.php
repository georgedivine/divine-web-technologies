<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "config.php";


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.html");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'];

    
    $check = $conn->prepare("SELECT * FROM course_requests WHERE student_id = ? AND course_id = ?");
    $check->execute([$student_id, $course_id]);
    
    if ($check->rowCount() > 0) {
        $_SESSION['message'] = "You have already requested this course.";
    } else {
        //insert a new request with status = 'pending'
        $stmt = $conn->prepare("INSERT INTO course_requests (student_id, course_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$student_id, $course_id]);
        $_SESSION['message'] = "Course request submitted successfully.";
    }

    //Redirect back to student dashboard
    header("Location: dashboard.php");
    exit;
} else {
    header("Location: dashboard.php");
    exit;
}
?>

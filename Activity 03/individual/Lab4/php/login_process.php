<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_NONE) {
    session_unset();
    session_destroy();
}

session_start();
require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if (empty($email) || empty($password)) {
        header("Location: ../index.html?error=Please+enter+both+email+and+password");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.html?error=Invalid+email+format");
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($password, $user["password"])) {
        header("Location: ../index.html?error=Incorrect+email+or+password");
        exit;
    }


        if (password_verify($password, $user["password"])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user'] = $user['email'];

            if ($user['role'] === 'faculty') {
                header("Location: faculty_dashboard.php");
                exit;
            } else {
                header("Location: dashboard.php");
            exit;
            }
        }
    }
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email";
        die("Registration failed: Invalid email");

    } elseif (strlen($password) < 6) {
        $error = "Password too short";
        die("Registration failed: Password too short");
    }
    
    else {
        //check if email already exists
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "Email already registered";
            die('Email already registered! <a href="../register.html">Go back</a>');
            //redirect to registration page
        }
        
        else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            $insert->execute([$email, $hashed, $role]);

            //Redirect to login page
            header("Location: ../index.html");
            exit;
        }
    }
}
?>

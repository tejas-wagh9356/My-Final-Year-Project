<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "certificate_db", 3306);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$fullname  = trim($_POST['fullname']);
$username  = trim($_POST['username']);
$email     = trim($_POST['email']);
$password  = $_POST['password'];
$cpassword = $_POST['cpassword'];

/* 🔐 Password match check */
if ($password !== $cpassword) {
    echo "<script>alert('Passwords do not match'); window.history.back();</script>";
    exit();
}

/* 🔍 Check duplicate email */
$checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    echo "<script>alert('Email already registered'); window.history.back();</script>";
    exit();
}

/* 🔍 Check duplicate username */
$checkUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
$checkUser->bind_param("s", $username);
$checkUser->execute();
$checkUser->store_result();

if ($checkUser->num_rows > 0) {
    echo "<script>alert('Username already exists'); window.history.back();</script>";
    exit();
}

/* 🔐 Password hashing */
$hash = password_hash($password, PASSWORD_DEFAULT);

/* ✅ Insert new user */
$stmt = $conn->prepare(
    "INSERT INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $fullname, $username, $email, $hash);

if ($stmt->execute()) {
    echo "<script>
            alert('Profile Created Successfully');
            window.location.href='main.html';
          </script>";
} else {
    echo "<script>alert('Registration failed');</script>";
}

$stmt->close();
$conn->close();
?>

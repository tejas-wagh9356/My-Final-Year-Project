<?php
session_start();

$conn = new mysqli("localhost","root","","certificate_db");
if ($conn->connect_error) die("DB Error");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT password FROM admin WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();

    if (password_verify($password, $row['password'])) {
        $_SESSION['admin'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid Password');history.back();</script>";
    }
} else {
    echo "<script>alert('Admin not found');history.back();</script>";
}

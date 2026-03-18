<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit();
}

$conn = new mysqli("localhost","root","","certificate_db");
if ($conn->connect_error) die("DB Error");

$admin = $_SESSION['admin'];
$old = $_POST['old_password'];
$new = $_POST['new_password'];
$confirm = $_POST['confirm_password'];

if ($new !== $confirm) {
    echo "<script>alert('Passwords do not match');history.back();</script>";
    exit();
}

/* Fetch current hash */
$stmt = $conn->prepare("SELECT password FROM admin WHERE username=?");
$stmt->bind_param("s", $admin);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!password_verify($old, $row['password'])) {
    echo "<script>alert('Old password incorrect');history.back();</script>";
    exit();
}

/* Update password */
$newHash = password_hash($new, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE admin SET password=? WHERE username=?");
$upd->bind_param("ss", $newHash, $admin);
$upd->execute();

if ($upd->affected_rows !== 1) {
    die("Password update failed (username mismatch)");
}

/* Logout */
session_unset();
session_destroy();

echo "<script>
alert('Password changed successfully. Login again.');
window.location.href='admin.html';
</script>";

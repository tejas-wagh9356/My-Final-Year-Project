<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "certificate_db");
if ($conn->connect_error) {
    die("Database connection failed");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: main.html");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

/* User check */
$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $row = $result->fetch_assoc();

    if (password_verify($password, $row['password'])) {

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];

        /* 🔥 LOGIN LOG INSERT (GUARANTEED) */
        $uid = $row['id'];
        $uname = $row['username'];

        $log = $conn->prepare(
            "INSERT INTO login_logs (user_id, username, login_time) VALUES (?, ?, NOW())"
        );
        $log->bind_param("is", $uid, $uname);

        if (!$log->execute()) {
            die("LOGIN LOG INSERT FAILED: " . $conn->error);
        }

        header("Location: dashboard.php");
        exit();

    } else {
        echo "<script>alert('Invalid Password'); history.back();</script>";
    }

} else {
    echo "<script>alert('Username not found'); history.back();</script>";
}

$conn->close();
?>

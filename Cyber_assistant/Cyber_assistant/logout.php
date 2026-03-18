<?php
session_start();

/* DB connection */
$conn = new mysqli("localhost", "root", "", "certificate_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* Logout time update */
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        UPDATE login_logs 
        SET logout_time = NOW() 
        WHERE user_id = ? 
        ORDER BY id DESC 
        LIMIT 1
    ");

    $stmt->bind_param("i", $uid);
    $stmt->execute();
}

/* Destroy session */
session_unset();
session_destroy();

/* Redirect */
header("Location: main.html?logout=success");
exit();
?>

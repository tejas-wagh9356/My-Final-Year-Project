<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* DB connection */
$conn = new mysqli("localhost", "root", "", "certificate_db");
if ($conn->connect_error) {
    die("Database connection failed");
}

/* POST check */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot_password.html");
    exit();
}

/* Form data */
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

/* Password match check */
if ($new_password !== $confirm_password) {
    echo "<script>
            alert('Passwords do not match');
            history.back();
          </script>";
    exit();
}

/* Check username + email */
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE username = ? AND email = ?"
);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {

    /* Password hash */
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    /* Update password */
    $update = $conn->prepare(
        "UPDATE users SET password = ? WHERE username = ? AND email = ?"
    );
    $update->bind_param("sss", $hash, $username, $email);

    if ($update->execute()) {

        /* 🔁 LOG FOR ADMIN (forgot_logs) */
        $conn->query("
            INSERT INTO forgot_logs (username, email)
            VALUES ('$username', '$email')
        ");

        echo "<script>
                alert('Password changed successfully');
                window.location.href='main.html';
              </script>";
    } else {
        echo "<script>alert('Error updating password');</script>";
    }

} else {
    echo "<script>
            alert('Username and Email do not match');
            history.back();
          </script>";
}

$conn->close();
?>

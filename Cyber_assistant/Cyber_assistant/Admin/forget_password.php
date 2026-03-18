<?php
session_start();

/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "certificate_db");
if (!$conn) {
    die("Database connection failed");
}

$msg = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Forgot Password</title>

<style>
body{
    font-family: Arial, sans-serif;
    background:#020617;
    color:#fff;
}
.box{
    width:420px;
    margin:120px auto;
    background:#020617;
    padding:30px;
    border-radius:12px;
    box-shadow:0 0 20px rgba(0,0,0,.7);
    border:1px solid #1e293b;
}
h2{
    text-align:center;
    color:#38bdf8;
}
input{
    width:100%;
    padding:12px;
    margin-top:15px;
    border:none;
    border-radius:6px;
    outline:none;
}
button{
    width:100%;
    padding:12px;
    margin-top:20px;
    border:none;
    border-radius:6px;
    background:#38bdf8;
    font-weight:bold;
    cursor:pointer;
}
.msg{
    margin-top:15px;
    text-align:center;
}
.success{ color:#22c55e; }
.error{ color:#ef4444; }
</style>
</head>

<body>

<div class="box">
    <h2>🔐 Admin Forget Password</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Enter Admin Username" required>
        <button type="submit">Reset Password</button>
    </form>

    <div class="msg">
        <?php echo $msg; ?>
    </div>
</div>

</body>
</html>

<?php
/* RESET LOGIC */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = mysqli_real_escape_string($conn, $_POST['username']);

    // CHECK ADMIN EXISTS
    $check = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");

    if (mysqli_num_rows($check) === 1) {

        // NEW PASSWORD (STATIC FOR DEMO)
        $new_password = "admin123";

        // HASH PASSWORD
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // UPDATE PASSWORD
        mysqli_query(
            $conn,
            "UPDATE admin SET password='$hashed' WHERE username='$username'"
        );

        $msg = "<span class='success'>
                ✅ Password Reset Successful <br>
                New Password: <b>admin123</b>
               </span>";

    } else {
        $msg = "<span class='error'>❌ Username not found</span>";
    }
}
?>

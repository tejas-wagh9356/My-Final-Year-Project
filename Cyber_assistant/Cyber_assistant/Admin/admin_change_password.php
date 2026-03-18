<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Change Admin Password</title>

<style>
body{
    font-family: Arial;
    background:#f4f6f9;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}
.box{
    background:#fff;
    padding:30px;
    width:350px;
    border-radius:10px;
    box-shadow:0 10px 20px rgba(0,0,0,0.2);
}
h2{text-align:center;margin-bottom:20px}
input{
    width:100%;
    padding:10px;
    margin:10px 0;
}
button{
    width:100%;
    padding:10px;
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:6px;
    font-size:16px;
}
</style>
</head>

<body>

<div class="box">
<h2>🔑 Change Password</h2>

<form action="admin_change_password_action.php" method="post">
    <input type="password" name="old_password" placeholder="Old Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Update Password</button>
</form>
</div>

</body>
</html>

<?php
session_start();

/* 🔒 CACHE DISABLE (IMPORTANT FOR LOGOUT) */
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

/* 🔐 Admin session check */
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit();
}

$conn = new mysqli("localhost","root","","certificate_db");
if ($conn->connect_error) die("DB Error");

/* COUNTS */
$users  = ($r=$conn->query("SELECT COUNT(*) t FROM users")) ? $r->fetch_assoc()['t'] : 0;
$logins = ($r=$conn->query("SELECT COUNT(*) t FROM login_logs")) ? $r->fetch_assoc()['t'] : 0;
$forgot = ($r=$conn->query("SELECT COUNT(*) t FROM forgot_logs")) ? $r->fetch_assoc()['t'] : 0;

/* AVG USE TIME */
$avg = 0;
$q = $conn->query("
    SELECT AVG(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) a
    FROM login_logs
    WHERE logout_time IS NOT NULL
");
if ($q && ($row=$q->fetch_assoc()) && $row['a'] !== null) {
    $avg = round($row['a']);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
body{
    margin:0;
    font-family:Arial, Helvetica, sans-serif;
    background:#f4f6f9;
}

/* LAYOUT */
.container{
    display:flex;
    height:100vh;
}

/* LEFT SIDE (ONLY BUTTONS) */
.left{
    width:220px;
    background:#1f2937;
    color:#fff;
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:20px;
}
.left h3{
    text-align:center;
    margin-bottom:30px;
}
.left a{
    display:block;
    text-align:center;
    text-decoration:none;
    color:#fff;
    padding:14px;
    margin:10px 0;
    border-radius:6px;
    background:#374151;
}
.left a:hover{
    background:#4b5563;
}
.logout{
    background:#dc2626 !important;
}

/* RIGHT SIDE (DASHBOARD BOXES) */
.right{
    flex:1;
    padding:50px;
}
.right h2{
    margin-bottom:30px;
}

.cards{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:30px;
    max-width:900px;
}

.card{
    padding:35px;
    border-radius:14px;
    color:#fff;
    box-shadow:0 10px 20px rgba(0,0,0,0.15);
}
.card h1{
    margin:15px 0;
    font-size:38px;
}
.card a{
    color:#fff;
    text-decoration:underline;
    font-size:14px;
}

/* COLORS */
.blue{background:#2563eb;}
.green{background:#16a34a;}
.red{background:#dc2626;}
.gray{background:#6b7280;}
</style>
</head>

<body>

<div class="container">

    <!-- 🔹 LEFT SIDE -->
    <div class="left">
        <h3>👑 Admin</h3>
        <a href="admin_change_password.php">Change Password</a>
        <a href="admin_logout.php" class="logout">Logout</a>
    </div>

    <!-- 🔹 RIGHT SIDE -->
    <div class="right">
        <h2>Dashboard</h2>

        <div class="cards">

            <div class="card blue">
                👥 Total Registered Users
                <h1><?php echo $users; ?></h1>
                <a href="admin_users.php">View Details</a>
            </div>

            <div class="card green">
                🔐 Total Login Logs
                <h1><?php echo $logins; ?></h1>
                <a href="admin_login_logs.php">View Details</a>
            </div>

            <div class="card red">
                🔁 Forgot Password Requests
                <h1><?php echo $forgot; ?></h1>
                <a href="admin_forgot_logs.php">View Details</a>
            </div>

            <div class="card gray">
                ⏱️ Average Use Time
                <h1><?php echo $avg; ?> min</h1>
                <a href="admin_avg_time.php">View Details</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>

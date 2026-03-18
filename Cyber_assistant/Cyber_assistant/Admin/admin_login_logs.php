<?php
session_start();

/* 🔐 Admin session check */
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit();
}

/* 🔗 DB connection */
$conn = new mysqli("localhost", "root", "", "certificate_db");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* 📥 Fetch login logs (NO logout_time) */
$sql = "SELECT id, user_id, username, login_time 
        FROM login_logs 
        ORDER BY id DESC";

$result = $conn->query($sql);
if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | Login Logs</title>

<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f4f6f9;
    padding:30px;
}
h2{
    margin-bottom:15px;
}
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
}
th, td{
    padding:10px;
    border:1px solid #ccc;
    text-align:left;
}
th{
    background:#1f2937;
    color:#fff;
}
tr:nth-child(even){
    background:#f2f2f2;
}
.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#2563eb;
}
</style>
</head>

<body>

<a class="back" href="admin_dashboard.php">← Back to Dashboard</a>

<h2>🔐 Login Logs</h2>

<table>
<tr>
    <th>ID</th>
    <th>User ID</th>
    <th>Username</th>
    <th>Login Time</th>
    <th>Logout Time</th>
</tr>

<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['user_id']."</td>";
        echo "<td>".$row['username']."</td>";
        echo "<td>".$row['login_time']."</td>";
        echo "<td>N/A</td>";   // 👈 logout_time column nahi hai
        echo "</tr>";

    }
} else {
    echo "<tr><td colspan='5'>No login records found</td></tr>";
}
?>

</table>

</body>
</html>

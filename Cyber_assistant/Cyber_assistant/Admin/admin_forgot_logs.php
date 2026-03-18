<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit();
}

$conn = new mysqli("localhost","root","","certificate_db");
if ($conn->connect_error) {
    die("Database connection failed");
}

$sql = "SELECT username, email, request_time 
        FROM forgot_logs 
        ORDER BY id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password Requests</title>

<style>
body{
    font-family:Arial, Helvetica, sans-serif;
    background:#f4f6f9;
    padding:30px;
}
.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#2563eb;
    font-size:14px;
}
h2{
    margin-bottom:15px;
}
table{
    border-collapse:collapse;
    width:100%;
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
</style>
</head>

<body>

<!-- 🔙 Back Arrow -->
<a href="admin_dashboard.php" class="back">← Back to Dashboard</a>

<h2>🔁 Forgot Password Requests</h2>

<table>
<tr>
    <th>Username</th>
    <th>Email</th>
    <th>Date</th>
</tr>

<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row['username']."</td>";
        echo "<td>".$row['email']."</td>";
        echo "<td>".$row['request_time']."</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No forgot password requests found</td></tr>";
}
?>

</table>

</body>
</html>

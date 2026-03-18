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

$sql = "
    SELECT username, 
           TIMESTAMPDIFF(MINUTE, login_time, logout_time) AS minutes
    FROM login_logs
    WHERE logout_time IS NOT NULL
    ORDER BY minutes DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Average Use Time</title>

<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f4f6f9;
    padding:30px;
}

/* Back link */
.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#2563eb;
    font-size:14px;
}

/* Heading */
h2{
    margin-bottom:15px;
}

/* Table */
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
</style>
</head>

<body>

<!-- 🔙 Back to dashboard -->
<a href="admin_dashboard.php" class="back">← Back to Dashboard</a>

<h2>⏱️ Average Use Time (Minutes)</h2>

<table>
<tr>
    <th>User</th>
    <th>Minutes Used</th>
</tr>

<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row['username']."</td>";
        echo "<td>".$row['minutes']."</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='2'>No usage data available</td></tr>";
}
?>

</table>

</body>
</html>

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

$sql = "SELECT id, fullname, username, email, created_at FROM users ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Registered Users</title>

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

<!-- 🔙 Back to Dashboard -->
<a href="admin_dashboard.php" class="back">← Back to Dashboard</a>

<h2>👥 Registered Users</h2>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Username</th>
    <th>Email</th>
    <th>Joined</th>
</tr>

<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['fullname']."</td>";
        echo "<td>".$row['username']."</td>";
        echo "<td>".$row['email']."</td>";
        echo "<td>".$row['created_at']."</td>";
        echo "</tr>";

    }
} else {
    echo "<tr><td colspan='5'>No users found</td></tr>";
}
?>

</table>

</body>
</html>

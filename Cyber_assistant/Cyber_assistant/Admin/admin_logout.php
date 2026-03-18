<?php
session_start();

/* 🔐 Admin session destroy */
if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
}

session_destroy();

/* 🚀 Redirect to admin login */
header("Location: admin.html");
exit();
?>

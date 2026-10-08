<?php
// admin/logout.php - Admin Logout
session_start();
unset($_SESSION['user_id']);
unset($_SESSION['user_role']);
unset($_SESSION['user_name']);
session_destroy();
header("Location: login.php");
exit();
?>

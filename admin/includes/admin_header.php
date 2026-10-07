<?php
// admin/includes/admin_header.php - Shared Admin Layout Header & Sidebar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/db.php';

// Verify Admin privileges
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    // If not logged in as admin, redirect to admin login
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Admin Dashboard' : 'Admin Panel - Indoor Plants'; ?></title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background: var(--cream-bg);">

<div class="admin-layout">
  
  <!-- Admin Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-logo">
      🌿 Indoor Admin
    </div>

    <nav class="admin-menu">
      <a href="index.php" class="admin-menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
        Dashboard Overview
      </a>
      <a href="products.php" class="admin-menu-link <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'add-product.php', 'edit-product.php'])) ? 'active' : ''; ?>">
        Plant Products
      </a>
      <a href="categories.php" class="admin-menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : ''; ?>">
         Plant Categories
      </a>
      <a href="variations.php" class="admin-menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'variations.php') ? 'active' : ''; ?>">
         Pot Variations
      </a>
      <a href="orders.php" class="admin-menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
        Customer Orders
      </a>
      <a href="users.php" class="admin-menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
         Registered Users
      </a>

      <div style="margin-top:40px; border-top:1px solid rgba(255,255,255,0.15); padding-top:20px;">
        <a href="../index.php" target="_blank" class="admin-menu-link" style="color:var(--accent-gold);">
          View Storefront ➔
        </a>
        <a href="logout.php" class="admin-menu-link" style="color:#ef4444;">
          Sign Out Admin
        </a>
      </div>
    </nav>
  </aside>

  <!-- Admin Main Content Area -->
  <main class="admin-content">

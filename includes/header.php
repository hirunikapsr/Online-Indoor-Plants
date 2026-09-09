<?php
// includes/header.php - Shared Navigation Header with Live Search & Dynamic Cart Badge
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
$wishlist_count = get_wishlist_count($pdo);
$cart_count = get_cart_count($pdo);


// Determine base path for subpages vs root
$in_subpage = defined('IS_SUBPAGE') && IS_SUBPAGE;
$base_url = $in_subpage ? '../' : './';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Online Indoor Plants' : 'Online Indoor Plants - Premium Indoor Greenery'; ?></title>
  <meta name="description" content="Shop premium indoor plants, air-purifying foliage, desk succulents, and low-light flora with custom ceramic, terracotta, and plastic pots.">
  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">
  <!-- Pass Base URL to JavaScript -->
  <script>window.SITE_BASE_URL = '<?php echo $base_url; ?>';</script>
</head>
<body>

  <div class="top-announcement">
    🌿 Free Delivery on Orders Over Rs. 5,000 | Bring Nature Home Today!
  </div>

  <!-- Primary Navigation Bar -->
  <nav class="navbar">
    <div class="container nav-container">
      
      <!-- Brand Logo -->
      <a href="<?php echo $base_url; ?>index.php" class="logo">
        <div class="logo-icon">🌿</div>
        <span>Indoor Plants</span>
      </a>

      <!-- Navigation Links -->
      <ul class="nav-links" id="navLinks">
        <li>
          <a href="<?php echo $base_url; ?>index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Home</a>
        </li>
        <li>
          <a href="<?php echo $base_url; ?>pages/shop.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'shop.php') ? 'active' : ''; ?>">Shop Plants</a>
        </li>
        <li>
          <a href="<?php echo $base_url; ?>pages/about.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">About Us</a>
        </li>
        <li>
          <a href="<?php echo $base_url; ?>pages/contact.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">Contact</a>
        </li>
        <!-- Admin only-->
  <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <li>
      <a href="<?php echo $base_url; ?>admin/index.php" class="nav-link">Admin Panel</a>
    </li>
  <?php endif; ?>
</ul>
      <!-- Header Search Bar with Live Suggestions Dropdown -->
      <div class="header-search-wrapper">
        <form method="GET" action="<?php echo $base_url; ?>pages/shop.php" class="header-search-form" id="headerSearchForm">
          <input type="text" 
                 name="search" 
                 id="headerSearchInput" 
                 placeholder="Search plants (e.g. Snake, Lily)..." 
                 autocomplete="off"
                 value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
          <button type="submit" class="search-submit-btn" title="Search Plants">🔍</button>
        </form>
        
        <!-- Live AJAX Search Suggestions Container -->
        <div id="searchSuggestions" class="search-suggestions-dropdown"></div>
      </div>


      <!-- Action Icons (User Account & Cart) -->
      <div class="nav-actions">
        <!-- User Account / Profile -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="<?php echo $base_url; ?>pages/profile.php" class="nav-action-btn" title="My Profile">
            👤 <span style="font-size: 0.9rem; font-weight:600;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Account'); ?></span>
          </a>
          <a href="<?php echo $base_url; ?>pages/logout.php" class="nav-action-btn" title="Logout" style="font-size:0.9rem; color:#ef4444;">
    Logout
</a>
        <?php else: ?>
          <a href="<?php echo $base_url; ?>pages/login.php" class="nav-action-btn" title="Login / Register">
            👤 <span style="font-size: 0.9rem; font-weight:600;">Sign In</span>
          </a>
        <?php endif; ?>

        <!-- Wishlist Icon with Dynamic Count Badge -->
        <?php $wishlist_url = isset($_SESSION['user_id']) ? $base_url . 'pages/wishlist.php' : $base_url . 'pages/login.php?msg=login_required_wishlist'; ?>
        <a href="<?php echo $wishlist_url; ?>" class="nav-action-btn" title="Wishlist" id="wishlistNavBtn">
          ❤️ <span class="nav-btn-label">Wishlist</span>
          <span class="badge" id="headerWishlistBadge" style="<?php echo ($wishlist_count > 0) ? 'display:flex;' : 'display:none;'; ?>">
            <?php echo $wishlist_count; ?>
          </span>
        </a>


        <!-- Cart Icon with Dynamic Count Badge -->
        <a href="<?php echo $base_url; ?>pages/cart.php" class="nav-action-btn" title="Shopping Cart" id="cartNavBtn">
          🛒
          <span class="badge" id="headerCartBadge" style="<?php echo ($cart_count > 0) ? 'display:flex;' : 'display:none;'; ?>">
            <?php echo $cart_count; ?>
          </span>
        </a>

        <!-- Mobile Toggle Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation">
          ☰
        </button>
      </div>

    </div>
  </nav>

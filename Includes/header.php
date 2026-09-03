<!DOCTYPE html>
<html lang="en">

<?php

$base_path = (basename(dirname($_SERVER['PHP_SELF'])) == 'pages') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Indoor Plants</title>
    
    <!-- Dynamic CSS Links -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/header.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/responsive.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="site-header">
    <div class="top-header container">
        <a href="/Online-Indoor-Plants/index.php" class="logo">
            <span class="logo-icon-wrapper">
                <i class="fa-solid fa-leaf logo-icon"></i>
            </span>
            <span class="logo-text">Online Indoor Plants</span>
        </a>

        <div class="search-bar">
            <form action="/Online-Indoor-Plants/pages/shop.php" method="GET">
                <input type="text" name="search" placeholder="Search plants...">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <div class="icon-actions">
            <a href="/Online-Indoor-Plants/pages/login.php"><i class="fa-regular fa-user"></i><span>Login</span></a>
            <a href="#"><i class="fa-regular fa-heart"></i><span>Wishlist</span></a>
            <a href="/Online-Indoor-Plants/pages/cart.php" class="cart-icon"><i class="fa-solid fa-cart-shopping"></i><span>Cart (0)</span></a>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container nav-row">
            <ul>
                <li><a href="/Online-Indoor-Plants/index.php">Home</a></li>
                <li><a href="/Online-Indoor-Plants/pages/shop.php">Shop</a></li>
                <li><a href="/Online-Indoor-Plants/pages/about.php">About</a></li>
                <li><a href="/Online-Indoor-Plants/pages/contact.php">Contact</a></li>
            </ul>
            <div class="delivery-info">
                <i class="fa-solid fa-truck-fast"></i> Island wide delivery
            </div>
        </div>
    </nav>
</header>
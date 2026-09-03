<?php 
// 1. Include Header Section
include 'includes/header.php'; 
?>

<!-- Hero Background Video Section -->
<section class="hero-video-section">
    <video autoplay loop muted playsinline class="hero-bg-video">
        <source src="image/hero-video.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <div class="hero-video-overlay">
        <div class="container hero-video-content">
            <h1>Bring Nature Home<br>Beautiful Indoor Plants<br>for a Greener Life!</h1>
            <p>Find the perfect indoor plants to brighten your space and refresh your mind.</p>
            <a href="pages/shop.php" class="btn-primary">Shop Now &rarr;</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features container">
    <div class="feature-box">
        <i class="fa-solid fa-truck-fast"></i>
        <div>
            <h4>Island Wide Delivery</h4>
            <p>We deliver plants to all areas across Sri Lanka.</p>
        </div>
    </div>
    <div class="feature-box">
        <i class="fa-solid fa-shield-halved"></i>
        <div>
            <h4>Secure Payment</h4>
            <p>100% secure payments with trusted methods.</p>
        </div>
    </div>
    <div class="feature-box">
        <i class="fa-solid fa-seedling"></i>
        <div>
            <h4>Plant Care Support</h4>
            <p>Get expert advice and care tips for your plants.</p>
        </div>
    </div>
</section>

<!-- Shop by Plant Type Section -->
<section class="plant-types container">
    <h2>Shop by Plant Type</h2>
    <div class="type-grid">
        <div class="type-card">
            <img src="https://images.unsplash.com/photo-1545241047-6083a3684587?q=80&w=500" alt="Air Purifying Plants">
            <h3>Air Purifying Plants</h3>
        </div>
        <div class="type-card">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRjBhfWqh-yjZtmwPPIMQcxiub3QixAmKNaj_lmMgeH-j4pp1ynpm5DIPnA&s=10" 
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1512428559087-560fa5ceab42?q=80&w=500';" 
                 alt="Low Light Plants">
            <h3>Low Light Plants</h3>
        </div>
        <div class="type-card">
            <img src="https://images.unsplash.com/photo-1509423350716-97f9360b4e09?q=80&w=500" alt="Succulents">
            <h3>Succulents</h3>
        </div>
        <div class="type-card">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcShiOjNNAJnsfcymcWsxXEiIGpcbruiQS-bxiqiqMLWs4mjMMJBhntnOfo&s=10" alt="Flowering Plants">
            <h3>Flowering Plants</h3>
        </div>
        <div class="type-card">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS7YgCOgsy_jH4TfaTztwKXJnoEjUrqN48mBrN0N8qXCA&s=10" 
                 onerror="this.onerror=null; this.src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS7YgCOgsy_jH4TfaTztwKXJnoEjUrqN48mBrN0N8qXCA&s=10';" 
                 alt="Hanging Plants">
            <h3>Hanging Plants</h3>
        </div>
    </div>
</section>

<!-- Popular Plants Section -->
<section class="popular-plants container">
    <h2>Popular Plants</h2>
    <div class="product-grid">
        <div class="product-card">
            <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXwdT9wiKudtE7hnhW3TccjwZmwvU62YIgIbarvyllrA&s=10" alt="Snake Plant">
            <h3>Snake Plant</h3>
            <p class="price">Rs. 1,500.00</p>
            <button class="add-to-cart-btn"><i class="fa-solid fa-cart-shopping"></i> Add to cart</button>
        </div>
        <div class="product-card">
            <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
            <img src="https://images.unsplash.com/photo-1614594975525-e45190c55d0b?q=80&w=500" alt="Monstera Deliciosa">
            <h3>Monstera Deliciosa</h3>
            <p class="price">Rs. 2,200.00</p>
            <button class="add-to-cart-btn"><i class="fa-solid fa-cart-shopping"></i> Add to cart</button>
        </div>
        <div class="product-card">
            <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSulMbom260jR2ehjaIDfme1C-GDMoqtMX4FICI0f44Xwm6oM5sIf-nudWo&s=10" 
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1512428559087-560fa5ceab42?q=80&w=500';" 
                 alt="Spider Plant">
            <h3>Spider Plant</h3>
            <p class="price">Rs. 1,800.00</p>
            <button class="add-to-cart-btn"><i class="fa-solid fa-cart-shopping"></i> Add to cart</button>
        </div>
        <div class="product-card">
            <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQQ0LmWiyM_N19-Jw5muWN67OU3rL-lhi0l02552Lj_zA&s=10" 
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1485955900006-10f4d324d411?q=80&w=500';" 
                 alt="Swiss Cheese Vine">
            <h3>Swiss Cheese Vine</h3>
            <p class="price">Rs. 2,500.00</p>
            <button class="add-to-cart-btn"><i class="fa-solid fa-cart-shopping"></i> Add to cart</button>
        </div>
    </div>
    <div class="view-all-wrapper">
        <a href="pages/shop.php" class="btn-secondary">View All Plants</a>
    </div>
</section>

<?php 
// 2. Include Footer Section
include 'includes/footer.php'; 
?>
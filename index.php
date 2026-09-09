<?php
// index.php - Master Home Page
$page_title = "Home - Premium Indoor Greenery";
require_once __DIR__ . '/includes/db.php';
/** @var PDO $pdo */
// Fetch Featured Products from Database
$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.is_featured = 1 AND p.status = 'active' LIMIT 4");
$stmt->execute();
$featured_products = $stmt->fetchAll();
// Static images mapping for categories
$static_category_images = [
    1 => 'image/categories/air_purifying.jpg',
    2 => 'image/categories/low_light.jpg',
    3 => 'image/categories/succulents.jpg',
    4 => 'image/categories/flowering.jpg',
];
// Fetch All Categories
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id ASC");
$categories = $cat_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Background Video Section (Parana Code Structural Setup) -->
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

<!-- Features Bar / Service Highlights -->
<section class="features-section">
  <div class="container">
    <div class="features-card">
      
      <!-- Feature 1: Delivery -->
      <div class="feature-item">
        <div class="feature-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
          </svg>
        </div>
        <div class="feature-text">
          <h4>Island Wide Delivery</h4>
          <p>We deliver plants to all areas across Sri Lanka.</p>
        </div>
      </div>

      <!-- Feature 2: Secure Payment -->
      <div class="feature-item">
        <div class="feature-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-5.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
          </svg>
        </div>
        <div class="feature-text">
          <h4>Secure Payment</h4>
          <p>100% secure payments with trusted methods.</p>
        </div>
      </div>

      <!-- Feature 3: Plant Care Support -->
      <div class="feature-item">
        <div class="feature-icon">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22C7.58 17.3 9.47 12 17 10V13L22 9L17 5V8Z"/>
            <path d="M3.5 11c0-3.5 2.5-6.5 6-7 1.2-.2 2.5 0 3.5.5-2 1.5-3 3.5-3 6 0 .5 0 1 .2 1.5-2.2-.2-4.2-1.1-5.7-2.5-.6 1.3-.9 2.8-.8 4.3 1.2.8 2.6 1.2 4 1.2h.3c-.5 1.2-1.2 2.3-2 3.3-1.5-2.3-2.5-4.8-2.5-7.3z"/>
          </svg>
        </div>
        <div class="feature-text">
        
          <h4>Plant Care Support</h4>
          <p>Get expert advice and care tips for your plants.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Categories Showcase Section -->
<section class="section">
  <div class="container">
    <div class="section-header text-center">
      <h2 class="section-title">Shop by Category</h2>
      <p class="section-subtitle">Curated indoor flora suited for every room, lighting condition, and lifestyle.</p>
    </div>

    <div class="category-grid">
      <?php foreach ($categories as $cat): 
        // ID එකට අදාළ Static Image එකක් තිබේ නම් එය ගන්නවා, නැතහොත් DB Image එක ගන්නවා
        $cat_id = $cat['category_id'];
        $image_src = isset($static_category_images[$cat_id]) ? $static_category_images[$cat_id] : $cat['image'];
      ?>
        <div class="category-card">
          <div class="category-img-wrapper">
            <a href="pages/shop.php?category=<?php echo $cat['category_id']; ?>">
              <!-- Static/Fallback Image Path -->
              <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($cat['category_name']); ?>">
            </a>
          </div>
          <div class="category-content">
            <h3 class="category-title"><?php echo htmlspecialchars($cat['category_name']); ?></h3>
            <p class="category-desc"><?php echo htmlspecialchars($cat['description']); ?></p>
            <a href="pages/shop.php?category=<?php echo $cat['category_id']; ?>" class="btn btn-outline-dark btn-sm" style="margin-top:auto;">
              Browse Category ➔
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Featured Plants Showcase Section -->
<section class="section section-bg-alt">
  <div class="container">
    <div class="section-header text-center">
      <h2 class="section-title">Popular Indoor Plants</h2>
      
      <p class="section-subtitle">Customer favorite indoor greenery chosen for easy care and striking aesthetics.</p>
      
    </div>

    <div class="product-grid">
      <?php foreach ($featured_products as $product): ?>
        <div class="product-card">
          <span class="product-badge">Featured</span>
          
          <div class="product-img-wrapper">
            <a href="pages/product-details.php?id=<?php echo $product['product_id']; ?>">
              <img src="<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            </a>
          </div>

          <div class="product-info">
            <span class="product-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <h3 class="product-name">
              <a href="pages/product-details.php?id=<?php echo $product['product_id']; ?>">
                <?php echo htmlspecialchars($product['product_name']); ?>
              </a>
            </h3>

            <div class="product-meta">
              <span class="product-meta-item">📏 <?php echo htmlspecialchars($product['size']); ?></span>
              <span class="product-meta-item">☀️ <?php echo htmlspecialchars($product['light_requirement']); ?></span>
            </div>

            <div class="product-price-row">
              <div class="product-price">Rs. <?php echo number_format($product['price']); ?></div>
              <a href="pages/product-details.php?id=<?php echo $product['product_id']; ?>" class="btn btn-emerald btn-sm">
                View & Customize
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top: 50px;">
      <a href="pages/shop.php" class="btn btn-primary">
        View Full Plant Catalog (20+ Plants) ➔
      </a>
    </div>

  </div>
</section>

<!-- Why Choose Us / Benefits Section -->
<section class="section">
  <div class="container">
    <div class="section-header text-center">
      <h2 class="section-title">Why Decorate With Indoor Plants?</h2>
      <p class="section-subtitle">Indoor plants do more than beautify space—they enhance your wellbeing.</p>
    </div>

    <div class="features-grid">
      
      <div class="feature-box">
        <div class="feature-icon">🌬️</div>
        <h3>Air Purification</h3>
        <p>Filters out harmful indoor toxins such as formaldehyde, benzene, and carbon dioxide while releasing clean oxygen.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon">🧠</div>
        <h3>Mood & Focus</h3>
        <p>Studies show that living plants reduce stress levels, improve mental focus, and increase daily productivity by up to 15%.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon">🚚</div>
        <h3>Safe Plant Delivery</h3>
        <p>Specialized eco-friendly packaging guarantees your plants arrive healthy, fresh, and hydrated at your door.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon">📖</div>
        <h3>Free Plant Care Guide</h3>
        <p>Every plant comes with detailed watering, light, and potting guidance so your greenery thrives effortlessly.</p>
      </div>

    </div>
  </div>
</section>

<!-- Plant Care & Consultation Banner -->
<section class="section section-bg-alt" style="padding: 60px 0;">
  <div class="container">
    <div class="care-grid">
      <div>
        <span style="color:var(--emerald); font-weight:700; text-transform:uppercase; letter-spacing:1px;">PLANT CARE GUIDE</span>
        <h2 style="font-size: 2.4rem; margin: 15px 0 20px;">Simple Steps to Keep Your Plants Healthy</h2>
        <p style="color:var(--text-muted); margin-bottom:25px;">Whether you are a beginner or experienced plant parent, follow our essential care principles:</p>
        
        <div class="care-card-list">
          <div class="care-card">
            <div class="care-icon">💧</div>
            <div>
              <h4 style="margin-bottom:4px;">Smart Watering</h4>
              <p style="font-size:0.9rem; color:var(--text-muted);">Check soil moisture 2 inches deep before watering. Avoid overwatering roots.</p>
            </div>
          </div>

          <div class="care-card">
            <div class="care-icon">☀️</div>
            <div>
              <h4 style="margin-bottom:4px;">Optimal Lighting</h4>
              <p style="font-size:0.9rem; color:var(--text-muted);">Place sun-loving succulents near windows; low-light plants thrive in indirect shade.</p>
            </div>
          </div>
        </div>
      </div>

      <div style="background:var(--sage-light); padding:40px; border-radius:var(--radius-lg); text-align:center;">
        <img src="image/person_caring_plant.jpg" alt="Person Caring for Indoor Plant" style="max-height:240px; width: 100%; object-fit: cover; border-radius: var(--radius-md); margin:0 auto 20px;">
        <h3 style="margin-bottom:10px;">Need Plant Advice?</h3>
        <p style="color:var(--text-muted); margin-bottom:20px;">Send a message to our botanical experts for personalized plant care recommendations.</p>
        <a href="pages/contact.php" class="btn btn-emerald">Get Free Consultation</a>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
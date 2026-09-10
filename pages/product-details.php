<?php
// pages/product-details.php - Product Detail View & Pot Variation Switcher
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
/** @var PDO $pdo */
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Product details
$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ? AND p.status = 'active'");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: shop.php");
    exit();
}

$page_title = $product['product_name'];
require_once __DIR__ . '/../includes/header.php';

// Fetch Pot Types available for this product
$pt_stmt = $pdo->prepare("SELECT DISTINCT pt.* FROM pot_types pt JOIN product_variations pv ON pt.pot_type_id = pv.pot_type_id WHERE pv.product_id = ?");
$pt_stmt->execute([$product_id]);
$available_pot_types = $pt_stmt->fetchAll();

// If no specific variations exist, fallback to all pot types
if (empty($available_pot_types)) {
    $available_pot_types = $pdo->query("SELECT * FROM pot_types")->fetchAll();
}

// Fetch Pot Colours available
$pc_stmt = $pdo->prepare("SELECT DISTINCT pc.* FROM pot_colours pc JOIN product_variations pv ON pc.pot_colour_id = pv.pot_colour_id WHERE pv.product_id = ?");
$pc_stmt->execute([$product_id]);
$available_pot_colours = $pc_stmt->fetchAll();

if (empty($available_pot_colours)) {
    $available_pot_colours = $pdo->query("SELECT * FROM pot_colours")->fetchAll();
}

// Fetch all variation records for client-side JS image switching
$var_stmt = $pdo->prepare("SELECT * FROM product_variations WHERE product_id = ?");
$var_stmt->execute([$product_id]);
$variations_json = json_encode($var_stmt->fetchAll());
?>

<!-- Breadcrumb Header -->
<div style="background: var(--white); padding: 15px 0; border-bottom: 1px solid var(--border-color);">
  <div class="container" style="font-size: 0.9rem; color: var(--text-muted);">
    <a href="../index.php">Home</a> &nbsp;/&nbsp; 
    <a href="shop.php">Shop</a> &nbsp;/&nbsp; 
    <a href="shop.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> &nbsp;/&nbsp; 
    <span style="color: var(--primary-forest); font-weight: 600;"><?php echo htmlspecialchars($product['product_name']); ?></span>
  </div>
</div>

<!-- Main Product Details Section -->
<section class="section">
  <div class="container">
    <div class="product-detail-grid">
      
      <!-- Product Gallery / Main Image View -->
      <div class="product-gallery">
        <img src="../<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" id="mainProductImg" class="main-product-img">
        <div style="margin-top: 15px; font-size: 0.85rem; color: var(--text-muted);">
          📷 Live Pot Variation Preview (Plant + Pot Type + Pot Colour)
        </div>
      </div>

      <!-- Product Details & Variation Form -->
      <div class="product-detail-info">
        <span class="product-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
        
        <div class="product-price-large" id="productPriceDisplay">
          Rs. <?php echo number_format($product['price']); ?>
        </div>

        <p class="product-description">
          <?php echo htmlspecialchars($product['description']); ?>
        </p>

        <!-- Plant Care & Specifications Grid -->
        <div class="specs-grid">
          <div class="spec-item">
            <div class="spec-icon">📏</div>
            <div>
              <div class="spec-label">Plant Size</div>
              <div class="spec-val"><?php echo htmlspecialchars($product['size']); ?></div>
            </div>
          </div>

          <div class="spec-item">
            <div class="spec-icon">☀️</div>
            <div>
              <div class="spec-label">Light Need</div>
              <div class="spec-val"><?php echo htmlspecialchars($product['light_requirement']); ?></div>
            </div>
          </div>

          <div class="spec-item">
            <div class="spec-icon">💧</div>
            <div>
              <div class="spec-label">Water Schedule</div>
              <div class="spec-val"><?php echo htmlspecialchars($product['watering_requirement']); ?></div>
            </div>
          </div>

          <div class="spec-item">
            <div class="spec-icon">📍</div>
            <div>
              <div class="spec-label">Suitable Location</div>
              <div class="spec-val"><?php echo htmlspecialchars($product['suitable_location'] ?? 'Living Room & Office'); ?></div>
            </div>
          </div>
        </div>

        <!-- Add To Cart Form with Pot Type & Colour Variations -->
        <form method="POST" action="cart.php" id="addToCartForm">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
          <input type="hidden" name="variation_id" id="selectedVariationId" value="">

          <!-- 1. Select Pot Type -->
          <div class="variation-group">
            <label class="variation-label">Step 1: Select Pot Type (with Image Preview)</label>
            <div class="variation-options">
              <?php foreach ($available_pot_types as $index => $pt): ?>
                <button type="button" 
                        class="variation-btn pot-type-btn <?php echo ($index === 0) ? 'active' : ''; ?>" 
                        data-type-id="<?php echo $pt['pot_type_id']; ?>">
                  <?php if (!empty($pt['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($pt['image']); ?>" alt="" class="pot-thumb-img">
                  <?php else: ?>
                    🪴
                  <?php endif; ?>
                  <span><?php echo htmlspecialchars($pt['pot_type_name']); ?></span>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- 2. Select Pot Colour -->
          <div class="variation-group">
            <label class="variation-label">Step 2: Select Pot Finish Colour</label>
            <div class="variation-options">
              <?php foreach ($available_pot_colours as $index => $pc): ?>
                <button type="button" 
                        class="variation-btn pot-colour-btn color-swatch-btn <?php echo ($index === 0) ? 'active' : ''; ?>" 
                        data-colour-id="<?php echo $pc['pot_colour_id']; ?>">
                  <span class="color-dot" style="background-color: <?php echo htmlspecialchars($pc['hex_code']); ?>;"></span>
                  <span><?php echo htmlspecialchars($pc['colour_name']); ?></span>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Stock Indicator -->
          <div style="margin-bottom: 20px; font-weight: 500; font-size: 0.95rem;">
            Availability: <span id="variationStockDisplay" style="color: #10b981;">In Stock</span>
          </div>

          <!-- Quantity Selector & Action Buttons -->
          <div class="action-controls">
            <div class="quantity-control">
              <button type="button" class="qty-btn" data-action="minus">-</button>
              <input type="number" name="quantity" value="1" min="1" max="50" readonly>
              <button type="button" class="qty-btn" data-action="plus">+</button>
            </div>

            <button type="submit" name="buy_now" value="0" class="btn btn-emerald" style="padding: 14px 35px;">
              🛒 Add to Cart
            </button>

            <button type="submit" name="buy_now" value="1" class="btn btn-primary" style="padding: 14px 30px;">
              ⚡ Buy Now
            </button>
          </div>

        </form>

      </div>

    </div>
  </div>
</section>

<!-- Initialize Dynamic Variation Image Switcher JS -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const basePrice = <?php echo floatval($product['price']); ?>;
    const variationsData = <?php echo $variations_json; ?>;
    
    if (typeof window.initProductVariationSwitcher === 'function') {
      window.initProductVariationSwitcher(basePrice, variationsData);
    }
  });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
// pages/shop.php - Product Listing & Search/Filtering
define('IS_SUBPAGE', true);
$page_title = "Shop Indoor Plants";
require_once __DIR__ . '/../includes/header.php';
/** @var PDO $pdo */

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

if ($category_id > 0) {
    $stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.category_id = ? AND p.status = 'active' ORDER BY p.product_id DESC");
    $stmt->execute([$category_id]);
} else {
    $stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.status = 'active' ORDER BY p.product_id DESC");
    $stmt->execute();
}

$products = $stmt->fetchAll();

// Parse query parameters for filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$pot_type_id = isset($_GET['pot_type']) ? intval($_GET['pot_type']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 5000.00;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build SQL Query dynamically
$where_clauses = ["p.status = 'active'"];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($category_id > 0) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_id;
}

if ($max_price > 0) {
    $where_clauses[] = "p.price <= ?";
    $params[] = $max_price;
}

if ($pot_type_id > 0) {
    $where_clauses[] = "p.product_id IN (SELECT product_id FROM product_variations WHERE pot_type_id = ?)";
    $params[] = $pot_type_id;
}

$where_sql = implode(' AND ', $where_clauses);

$order_sql = "ORDER BY p.product_id DESC";
if ($sort === 'price_low') {
    $order_sql = "ORDER BY p.price ASC";
} elseif ($sort === 'price_high') {
    $order_sql = "ORDER BY p.price DESC";
} elseif ($sort === 'name') {
    $order_sql = "ORDER BY p.product_name ASC";
}

$sql = "SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE {$where_sql} 
        {$order_sql}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch Categories for Sidebar Filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

// Fetch Pot Types for Filter
$pot_types = $pdo->query("SELECT * FROM pot_types ORDER BY pot_type_id ASC")->fetchAll();
?>

<!-- Shop Header Banner -->
<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 50px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.8rem; margin-bottom: 10px;">Indoor Plant Shop</h1>
    <p style="color: rgba(255,255,255,0.85); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
      Discover healthy air-purifying foliage, low-light succulents, and flowering indoor plants. Customize your pot style and colour!
    </p>
  </div>
</section>

<!-- Shop Layout: Sidebar Filters + Main Product Grid -->
<section class="section">
  <div class="container">
    <div class="shop-layout">
      
      <!-- Filter Sidebar -->
      <aside class="filter-sidebar">
        <form method="GET" action="shop.php" id="filterForm">
          
          <!-- Search Box -->
          <div class="filter-group">
            <h4 class="filter-title">Search Plants</h4>
            <div class="filter-search-box">
              <input type="text" name="search" placeholder="Search Snake, Lily, ZZ..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
          </div>

          <!-- Category Filter -->
          <div class="filter-group">
            <h4 class="filter-title">Categories</h4>
            <div class="filter-list">
              <label class="filter-checkbox">
                <input type="radio" name="category" value="0" <?php echo ($category_id === 0) ? 'checked' : ''; ?> onchange="this.form.submit();">
                All Categories
              </label>
              <?php foreach ($categories as $cat): ?>
                <label class="filter-checkbox">
                  <input type="radio" name="category" value="<?php echo $cat['category_id']; ?>" <?php echo ($category_id === $cat['category_id']) ? 'checked' : ''; ?> onchange="this.form.submit();">
                  <?php echo htmlspecialchars($cat['category_name']); ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Pot Type Filter -->
          <div class="filter-group">
            <h4 class="filter-title">Pot Type</h4>
            <div class="filter-list">
              <label class="filter-checkbox">
                <input type="radio" name="pot_type" value="0" <?php echo ($pot_type_id === 0) ? 'checked' : ''; ?> onchange="this.form.submit();">
                All Pot Types
              </label>
              <?php foreach ($pot_types as $pt): ?>
                <label class="filter-checkbox">
                  <input type="radio" name="pot_type" value="<?php echo $pt['pot_type_id']; ?>" <?php echo ($pot_type_id === $pt['pot_type_id']) ? 'checked' : ''; ?> onchange="this.form.submit();">
                  <?php echo htmlspecialchars($pt['pot_type_name']); ?> Pots
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Price Range Filter -->
          <div class="filter-group">
            <h4 class="filter-title">Max Price</h4>
            <div class="price-range-wrapper">
              <input type="range" id="priceRange" name="max_price" min="500" max="5000" step="100" value="<?php echo $max_price; ?>" onchange="this.form.submit();">
              <div class="price-values">
                <span>Rs. 500</span>
                <span id="priceVal">Rs. <?php echo number_format($max_price); ?></span>
              </div>
            </div>
          </div>

          <!-- Filter Action Buttons -->
          <button type="submit" class="btn btn-emerald btn-block" style="margin-bottom: 10px;">Apply Filters</button>
          <a href="shop.php" class="btn btn-outline-dark btn-block btn-sm" style="text-align:center;">Reset Filters</a>

        </form>
      </aside>

      <!-- Main Product Grid & Controls -->
      <div>
        
        <!-- Grid Header Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: var(--white); padding: 15px 20px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
          <div style="font-weight: 500; color: var(--text-muted);">
            Showing <strong style="color: var(--primary-forest);"><?php echo count($products); ?></strong> indoor plants
          </div>

          <!-- Sorting Selector -->
          <div style="display: flex; align-items: center; gap: 10px;">
            <label style="font-size: 0.9rem; font-weight: 500;">Sort By:</label>
            <select name="sort" style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-family: inherit;" onchange="document.getElementById('filterForm').sort.value=this.value; document.getElementById('filterForm').submit();">
              <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
              <option value="price_low" <?php echo ($sort === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
              <option value="price_high" <?php echo ($sort === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
              <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>Plant Name (A-Z)</option>
            </select>
          </div>
        </div>

        <!-- Products Grid -->
        <?php if (count($products) > 0): ?>
          <div class="product-grid">
            <?php foreach ($products as $product): ?>
              <div class="product-card">
                <span class="product-badge"><?php echo htmlspecialchars($product['size']); ?></span>
                
                <div class="product-img-wrapper">
                  <a href="product-details.php?id=<?php echo $product['product_id']; ?>">
                    <img src="../<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                  </a>
                </div>

                <div class="product-info">
                  <span class="product-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                  <h3 class="product-name">
                    <a href="product-details.php?id=<?php echo $product['product_id']; ?>">
                      <?php echo htmlspecialchars($product['product_name']); ?>
                    </a>
                  </h3>

                  <div class="product-meta">
                    <span class="product-meta-item">☀️ <?php echo htmlspecialchars($product['light_requirement']); ?></span>
                  </div>

                  <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php echo htmlspecialchars($product['description']); ?>
                  </p>

                  <div class="product-price-row">
                    <div class="product-price">Rs. <?php echo number_format($product['price']); ?></div>
                    <a href="product-details.php?id=<?php echo $product['product_id']; ?>" class="btn btn-emerald btn-sm">
                      View Details
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div style="background: var(--white); padding: 50px; text-align: center; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="font-size: 3rem; margin-bottom: 15px;">🪴</div>
            <h3>No Indoor Plants Match Your Filter</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Try loosening your search terms or clearing your price/category filters.</p>
            <a href="shop.php" class="btn btn-emerald">Clear All Filters</a>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

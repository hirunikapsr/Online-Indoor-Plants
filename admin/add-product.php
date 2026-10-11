<?php
// admin/add-product.php - Add New Indoor Plant
$page_title = "Add New Plant Product";
require_once __DIR__ . '/includes/admin_header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $size = $_POST['size'];
    $light_requirement = trim($_POST['light_requirement']);
    $watering_requirement = trim($_POST['watering_requirement']);
    $care_level = $_POST['care_level'];
    $stock_quantity = intval($_POST['stock_quantity']);
    $status = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Default main image SVG fallback if not uploaded
    $main_image = 'image/products/snake_plant.svg';
    if (!empty($_POST['main_image_preset'])) {
        $main_image = $_POST['main_image_preset'];
    }

    if (empty($product_name) || $category_id <= 0 || $price <= 0) {
        $error = 'Please enter a valid plant name, category, and price.';
    } else {
        $ins = $pdo->prepare("INSERT INTO products 
            (category_id, product_name, description, price, size, light_requirement, watering_requirement, care_level, stock_quantity, main_image, status, is_featured) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $category_id, $product_name, $description, $price, $size, 
            $light_requirement, $watering_requirement, $care_level, 
            $stock_quantity, $main_image, $status, $is_featured
        ]);

        $new_product_id = $pdo->lastInsertId();

        // Create default variations for ceramic/terracotta/plastic
        $pdo->prepare("INSERT INTO product_variations (product_id, pot_type_id, pot_colour_id, image, additional_price, stock_quantity) VALUES 
            (?, 1, 1, 'image/product_variations/snake_ceramic_white.svg', 0.00, 20),
            (?, 1, 2, 'image/product_variations/snake_ceramic_black.svg', 2.00, 15),
            (?, 2, 3, 'image/product_variations/snake_terracotta_brown.svg', -2.00, 15),
            (?, 3, 1, 'image/product_variations/snake_plastic_black.svg', -4.00, 20)
        ")->execute([$new_product_id, $new_product_id, $new_product_id, $new_product_id]);

        header("Location: products.php?msg=created");
        exit();
    }
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
?>

<div style="margin-bottom:20px;">
  <a href="products.php" class="btn btn-outline-dark btn-sm">← Back to Plant Catalog</a>
</div>

<div class="form-card" style="max-width:800px;">
  <h2 style="margin-bottom:25px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Add New Indoor Plant</h2>

  <?php if ($error): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #ef4444;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="add-product.php">
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Plant Name *</label>
        <input type="text" name="product_name" class="form-control" required placeholder="e.g. Fiddle Leaf Fig">
      </div>

      <div class="form-group">
        <label class="form-label">Plant Category *</label>
        <select name="category_id" class="form-control" required>
          <option value="">Select Category</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Description & Features *</label>
      <textarea name="description" class="form-control" rows="3" required placeholder="Describe leaf foliage, air purifying benefits..."></textarea>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Base Price ($) *</label>
        <input type="number" step="0.01" name="price" class="form-control" required placeholder="29.99">
      </div>

      <div class="form-group">
        <label class="form-label">Initial Stock Quantity *</label>
        <input type="number" name="stock_quantity" class="form-control" value="50" required>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Plant Size</label>
        <select name="size" class="form-control">
          <option value="Small">Small</option>
          <option value="Medium" selected>Medium</option>
          <option value="Large">Large</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Care Level</label>
        <select name="care_level" class="form-control">
          <option value="Easy" selected>Easy</option>
          <option value="Moderate">Moderate</option>
          <option value="Expert">Expert</option>
        </select>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Light Need</label>
        <input type="text" name="light_requirement" class="form-control" value="Bright Indirect Light">
      </div>

      <div class="form-group">
        <label class="form-label">Water Schedule</label>
        <input type="text" name="watering_requirement" class="form-control" value="Once a week">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Main Image SVG Illustration</label>
      <select name="main_image_preset" class="form-control">
        <option value="image/products/snake_plant.svg">Snake Plant SVG</option>
        <option value="image/products/spider_plant.svg">Spider Plant SVG</option>
        <option value="image/products/peace_lily.svg">Peace Lily SVG</option>
        <option value="image/products/zz_plant.svg">ZZ Plant SVG</option>
        <option value="image/products/jade_plant.svg">Jade Plant SVG</option>
        <option value="image/products/orchid.svg">Orchid SVG</option>
        <option value="image/products/anthurium.svg">Anthurium SVG</option>
      </select>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Publication Status</label>
        <select name="status" class="form-control">
          <option value="active" selected>Active (Visible in Store)</option>
          <option value="inactive">Inactive (Hidden)</option>
        </select>
      </div>

      <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top:30px;">
        <input type="checkbox" name="is_featured" id="is_featured" value="1" checked style="width:20px; height:20px;">
        <label for="is_featured" style="font-weight:600;">Display on Home Page Featured Section</label>
      </div>
    </div>

    <button type="submit" class="btn btn-emerald" style="padding:14px 35px; margin-top:15px;">
      Save & Publish Plant
    </button>
  </form>
</div>

</main>
</div>
</body>
</html>

<?php
// admin/edit-product.php - Edit Plant Product Details
$page_title = "Edit Plant Product";
require_once __DIR__ . '/includes/admin_header.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit();
}

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

    if (empty($product_name) || $category_id <= 0 || $price <= 0) {
        $error = 'Please enter a valid plant name, category, and price.';
    } else {
        $upd = $pdo->prepare("UPDATE products SET 
            category_id = ?, product_name = ?, description = ?, price = ?, size = ?, 
            light_requirement = ?, watering_requirement = ?, care_level = ?, 
            stock_quantity = ?, status = ?, is_featured = ? 
            WHERE product_id = ?");
        $upd->execute([
            $category_id, $product_name, $description, $price, $size,
            $light_requirement, $watering_requirement, $care_level,
            $stock_quantity, $status, $is_featured, $product_id
        ]);

        header("Location: products.php?msg=updated");
        exit();
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
?>

<div style="margin-bottom:20px;">
  <a href="products.php" class="btn btn-outline-dark btn-sm">← Back to Plant Catalog</a>
</div>

<div class="form-card" style="max-width:800px;">
  <h2 style="margin-bottom:25px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Edit Plant #<?php echo $product_id; ?> - <?php echo htmlspecialchars($product['product_name']); ?></h2>

  <?php if ($error): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #ef4444;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="edit-product.php?id=<?php echo $product_id; ?>">
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Plant Name *</label>
        <input type="text" name="product_name" class="form-control" required value="<?php echo htmlspecialchars($product['product_name']); ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Plant Category *</label>
        <select name="category_id" class="form-control" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($product['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat['category_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Description & Care Info *</label>
      <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Price ($) *</label>
        <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $product['price']; ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Stock Quantity *</label>
        <input type="number" name="stock_quantity" class="form-control" required value="<?php echo $product['stock_quantity']; ?>">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Plant Size</label>
        <select name="size" class="form-control">
          <option value="Small" <?php echo ($product['size'] == 'Small') ? 'selected' : ''; ?>>Small</option>
          <option value="Medium" <?php echo ($product['size'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
          <option value="Large" <?php echo ($product['size'] == 'Large') ? 'selected' : ''; ?>>Large</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Care Level</label>
        <select name="care_level" class="form-control">
          <option value="Easy" <?php echo ($product['care_level'] == 'Easy') ? 'selected' : ''; ?>>Easy</option>
          <option value="Moderate" <?php echo ($product['care_level'] == 'Moderate') ? 'selected' : ''; ?>>Moderate</option>
          <option value="Expert" <?php echo ($product['care_level'] == 'Expert') ? 'selected' : ''; ?>>Expert</option>
        </select>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Light Requirement</label>
        <input type="text" name="light_requirement" class="form-control" value="<?php echo htmlspecialchars($product['light_requirement']); ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Watering Requirement</label>
        <input type="text" name="watering_requirement" class="form-control" value="<?php echo htmlspecialchars($product['watering_requirement']); ?>">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="active" <?php echo ($product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
          <option value="inactive" <?php echo ($product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
        </select>
      </div>

      <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top:30px;">
        <input type="checkbox" name="is_featured" id="is_featured" value="1" <?php echo ($product['is_featured'] == 1) ? 'checked' : ''; ?> style="width:20px; height:20px;">
        <label for="is_featured" style="font-weight:600;">Featured on Home Page</label>
      </div>
    </div>

    <button type="submit" class="btn btn-emerald" style="padding:14px 35px; margin-top:15px;">
      Update Plant Details
    </button>
  </form>
</div>

</main>
</div>
</body>
</html>

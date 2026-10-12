<?php
// admin/variations.php - Pot Type & Pot Colour Variation Management
$page_title = "Manage Pot Variations";
require_once __DIR__ . '/includes/admin_header.php';

$filter_product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$msg = '';
$error = '';

// Handle Add Variation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_variation') {
    $product_id = intval($_POST['product_id']);
    $pot_type_id = intval($_POST['pot_type_id']);
    $pot_colour_id = intval($_POST['pot_colour_id']);
    $additional_price = floatval($_POST['additional_price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $image = trim($_POST['image']);

    if ($product_id <= 0 || $pot_type_id <= 0 || $pot_colour_id <= 0) {
        $error = 'Please select a valid product, pot type, and pot colour.';
    } else {
        try {
            $ins = $pdo->prepare("INSERT INTO product_variations (product_id, pot_type_id, pot_colour_id, image, additional_price, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->execute([$product_id, $pot_type_id, $pot_colour_id, $image, $additional_price, $stock_quantity]);
            $msg = 'Pot variation added successfully!';
        } catch (PDOException $e) {
            $error = 'Variation already exists for this plant + pot type + colour combination.';
        }
    }
}

// Handle Delete Variation
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $var_id = intval($_GET['id']);
    $del = $pdo->prepare("DELETE FROM product_variations WHERE variation_id = ?");
    $del->execute([$var_id]);
    header("Location: variations.php?msg=deleted" . ($filter_product_id ? "&product_id=" . $filter_product_id : ""));
    exit();
}

// Fetch Variation Records
$where_sql = $filter_product_id > 0 ? "WHERE pv.product_id = {$filter_product_id}" : "";

$sql = "SELECT pv.*, p.product_name, pt.pot_type_name, pc.colour_name, pc.hex_code
        FROM product_variations pv
        JOIN products p ON pv.product_id = p.product_id
        JOIN pot_types pt ON pv.pot_type_id = pt.pot_type_id
        JOIN pot_colours pc ON pv.pot_colour_id = pc.pot_colour_id
        {$where_sql}
        ORDER BY pv.product_id DESC, pv.variation_id ASC";

$variations = $pdo->query($sql)->fetchAll();

// Fetch Products, Pot Types, Pot Colours for Form dropdowns
$products_list = $pdo->query("SELECT product_id, product_name FROM products ORDER BY product_name ASC")->fetchAll();
$pot_types_list = $pdo->query("SELECT * FROM pot_types")->fetchAll();
$pot_colours_list = $pdo->query("SELECT * FROM pot_colours")->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
  <div>
    <h1 style="font-size:2rem;">Pot Type & Colour Variation Management</h1>
    <p style="color:var(--text-muted);">Manage Plant + Pot Type + Pot Colour combination images and pricing.</p>
  </div>
</div>

<?php if (isset($_GET['msg']) || $msg): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #10b981;">
    ✓ <?php echo htmlspecialchars($_GET['msg'] ?? $msg); ?>
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #ef4444;">
    <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<div class="shop-layout" style="grid-template-columns: 1fr 360px;">
  
  <!-- Variations Table -->
  <div class="cart-table-wrapper">
    <table class="cart-table">
      <thead>
        <tr>
          <th>Plant & Variation Preview</th>
          <th>Pot Material</th>
          <th>Pot Colour</th>
          <th>Price Adj.</th>
          <th>Stock</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($variations as $v): ?>
          <tr>
            <td>
              <div class="cart-item-flex">
                <img src="../<?php echo htmlspecialchars($v['image']); ?>" alt="" class="cart-item-img">
                <div>
                  <strong style="font-size:1.05rem; display:block;"><?php echo htmlspecialchars($v['product_name']); ?></strong>
                  <span style="font-size:0.8rem; color:var(--text-muted);">Var ID: #<?php echo $v['variation_id']; ?></span>
                </div>
              </div>
            </td>

            <td>
              <span style="background:var(--sage-light); color:var(--primary-forest); font-weight:600; padding:4px 10px; border-radius:12px; font-size:0.85rem;">
                 <?php echo htmlspecialchars($v['pot_type_name']); ?>
              </span>
            </td>

            <td>
              <div style="display:flex; align-items:center; gap:6px; font-weight:600; font-size:0.9rem;">
                <span style="width:14px; height:14px; border-radius:50%; background:<?php echo htmlspecialchars($v['hex_code']); ?>; border:1px solid #ccc; display:inline-block;"></span>
                <?php echo htmlspecialchars($v['colour_name']); ?>
              </div>
            </td>

            <td style="font-weight:600; color:var(--emerald);">
              <?php echo ($v['additional_price'] >= 0) ? '+$' . number_format($v['additional_price'], 2) : '-$' . number_format(abs($v['additional_price']), 2); ?>
            </td>

            <td>
              <?php echo $v['stock_quantity']; ?> units
            </td>

            <td>
              <a href="variations.php?action=delete&id=<?php echo $v['variation_id']; ?><?php echo $filter_product_id ? '&product_id=' . $filter_product_id : ''; ?>" onclick="return confirm('Delete this variation?');" class="btn btn-sm" style="background:#fee2e2; color:#991b1b;">
                Del
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Add Variation Form -->
  <div class="form-card">
    <h3 style="margin-bottom:20px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Add New Pot Variation</h3>
    
    <form method="POST" action="variations.php<?php echo $filter_product_id ? '?product_id=' . $filter_product_id : ''; ?>">
      <input type="hidden" name="action" value="add_variation">

      <div class="form-group">
        <label class="form-label">Select Plant Product *</label>
        <select name="product_id" class="form-control" required>
          <option value="">Select Plant</option>
          <?php foreach ($products_list as $pl): ?>
            <option value="<?php echo $pl['product_id']; ?>" <?php echo ($filter_product_id == $pl['product_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($pl['product_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Pot Type (Material) *</label>
        <select name="pot_type_id" class="form-control" required>
          <?php foreach ($pot_types_list as $pt): ?>
            <option value="<?php echo $pt['pot_type_id']; ?>"><?php echo htmlspecialchars($pt['pot_type_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Pot Colour *</label>
        <select name="pot_colour_id" class="form-control" required>
          <?php foreach ($pot_colours_list as $pc): ?>
            <option value="<?php echo $pc['pot_colour_id']; ?>"><?php echo htmlspecialchars($pc['colour_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Variation Image Path *</label>
        <input type="text" name="image" class="form-control" required value="image/product_variations/snake_ceramic_white.svg">
      </div>

      <div class="form-grid-2">
        <div class="form-group">
          <label class="form-label">Price Adj ($)</label>
          <input type="number" step="0.50" name="additional_price" class="form-control" value="0.00">
        </div>

        <div class="form-group">
          <label class="form-label">Stock Units</label>
          <input type="number" name="stock_quantity" class="form-control" value="20">
        </div>
      </div>

      <button type="submit" class="btn btn-emerald btn-block" style="padding:12px;">
        + Save Pot Variation
      </button>
    </form>
  </div>

</div>

</main>
</div>
</body>
</html>

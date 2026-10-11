<?php
// admin/products.php - Plant Product Management List
$page_title = "Manage Plant Products";
require_once __DIR__ . '/includes/admin_header.php';

// Handle Product Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_id = intval($_GET['id']);
    $del_stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $del_stmt->execute([$del_id]);
    header("Location: products.php?msg=deleted");
    exit();
}

// Fetch all products with category names
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
$products = $pdo->query($sql)->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
  <div>
    <h1 style="font-size:2rem;">Indoor Plant Catalog Management</h1>
    <p style="color:var(--text-muted);">Manage product listings, prices, stock levels, and category assignments.</p>
  </div>
  <a href="add-product.php" class="btn btn-emerald">+ Add New Plant</a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #10b981;">
    <?php 
      if ($_GET['msg'] === 'created') echo "✓ New indoor plant created successfully!";
      if ($_GET['msg'] === 'updated') echo "✓ Plant product updated successfully.";
      if ($_GET['msg'] === 'deleted') echo "✓ Plant product deleted from store catalog.";
    ?>
  </div>
<?php endif; ?>

<div class="cart-table-wrapper">
  <table class="cart-table">
    <thead>
      <tr>
        <th>Product</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Size & Care</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td>
            <div class="cart-item-flex">
              <img src="../<?php echo htmlspecialchars($p['main_image']); ?>" alt="" class="cart-item-img">
              <div>
                <strong style="font-size:1.05rem; display:block;"><?php echo htmlspecialchars($p['product_name']); ?></strong>
                <span style="font-size:0.8rem; color:var(--text-muted);">ID: #<?php echo $p['product_id']; ?></span>
              </div>
            </div>
          </td>

          <td>
            <span style="background:var(--sage-light); color:var(--emerald); font-weight:600; padding:4px 10px; border-radius:12px; font-size:0.85rem;">
              <?php echo htmlspecialchars($p['category_name']); ?>
            </span>
          </td>

          <td style="font-weight:700; color:var(--primary-forest);">
            Rs. <?php echo number_format($p['price']); ?>
          </td>

          <td>
            <?php if ($p['stock_quantity'] > 10): ?>
              <span style="color:#10b981; font-weight:600;"><?php echo $p['stock_quantity']; ?> in stock</span>
            <?php else: ?>
              <span style="color:#ef4444; font-weight:700;"><?php echo $p['stock_quantity']; ?> Low Stock</span>
            <?php endif; ?>
          </td>

          <td style="font-size:0.85rem; color:var(--text-muted);">
            📏 <?php echo htmlspecialchars($p['size']); ?><br>
            🪴 <?php echo htmlspecialchars($p['care_level']); ?>
          </td>

          <td>
            <span style="background:<?php echo ($p['status'] === 'active') ? '#d1fae5' : '#fee2e2'; ?>; color:<?php echo ($p['status'] === 'active') ? '#065f46' : '#991b1b'; ?>; font-weight:600; padding:4px 10px; border-radius:12px; font-size:0.8rem;">
              <?php echo ucfirst($p['status']); ?>
            </span>
          </td>

          <td>
            <div style="display:flex; gap:8px;">
              <a href="edit-product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-outline-dark btn-sm">Edit</a>
              <a href="variations.php?product_id=<?php echo $p['product_id']; ?>" class="btn btn-emerald btn-sm">Pots</a>
              <a href="products.php?action=delete&id=<?php echo $p['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this plant?');" class="btn btn-sm" style="background:#fee2e2; color:#991b1b;">Del</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</main>
</div>
</body>
</html>

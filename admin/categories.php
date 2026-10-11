<?php
// admin/categories.php - Category Management Page
$page_title = "Manage Plant Categories";
require_once __DIR__ . '/includes/admin_header.php';

// Handle Add Category
$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $category_name = trim($_POST['category_name']);
    $description = trim($_POST['description']);
    $image = 'image/products/snake_plant.svg';

    if (empty($category_name)) {
        $error = 'Category name cannot be empty.';
    } else {
        $ins = $pdo->prepare("INSERT INTO categories (category_name, description, image) VALUES (?, ?, ?)");
        $ins->execute([$category_name, $description, $image]);
        $msg = 'Category added successfully!';
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $cat_id = intval($_GET['id']);
    $del = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    $del->execute([$cat_id]);
    header("Location: categories.php?msg=deleted");
    exit();
}

$categories = $pdo->query("SELECT c.*, COUNT(p.product_id) AS product_count FROM categories c LEFT JOIN products p ON c.category_id = p.category_id GROUP BY c.category_id ORDER BY c.category_id ASC")->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
  <div>
    <h1 style="font-size:2rem;">Plant Categories Management</h1>
    <p style="color:var(--text-muted);">Organize indoor flora into curated collections.</p>
  </div>
</div>

<?php if (isset($_GET['msg']) || $msg): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #10b981;">
    ✓ <?php echo htmlspecialchars($_GET['msg'] ?? $msg); ?>
  </div>
<?php endif; ?>

<div class="shop-layout" style="grid-template-columns: 1fr 340px;">
  
  <!-- Categories Table -->
  <div class="cart-table-wrapper">
    <table class="cart-table">
      <thead>
        <tr>
          <th>Category Name</th>
          <th>Description</th>
          <th>Total Products</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td>
              <strong style="font-size:1.05rem; color:var(--primary-forest);">
                <?php echo htmlspecialchars($cat['category_name']); ?>
              </strong>
            </td>
            <td style="font-size:0.9rem; color:var(--text-muted);">
              <?php echo htmlspecialchars($cat['description']); ?>
            </td>
            <td>
              <span style="background:var(--sage-light); color:var(--emerald); font-weight:700; padding:4px 12px; border-radius:12px;">
                <?php echo $cat['product_count']; ?> plants
              </span>
            </td>
            <td>
              <a href="categories.php?action=delete&id=<?php echo $cat['category_id']; ?>" onclick="return confirm('Delete category?');" class="btn btn-sm" style="background:#fee2e2; color:#991b1b;">
                Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Add Category Form -->
  <div class="form-card">
    <h3 style="margin-bottom:20px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Add New Category</h3>
    
    <form method="POST" action="categories.php">
      <input type="hidden" name="action" value="add_category">

      <div class="form-group">
        <label class="form-label">Category Title *</label>
        <input type="text" name="category_name" class="form-control" required placeholder="e.g. Hanging Vine Plants">
      </div>

      <div class="form-group">
        <label class="form-label">Short Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Description for category card..."></textarea>
      </div>

      <button type="submit" class="btn btn-emerald btn-block" style="padding:12px;">
        + Save Category
      </button>
    </form>
  </div>

</div>

</main>
</div>
</body>
</html>

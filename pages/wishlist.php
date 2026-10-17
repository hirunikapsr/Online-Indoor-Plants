<?php
// pages/wishlist.php - Customer Wishlist Management
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';

// Require Customer Authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=login_required_wishlist");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Handle Wishlist Actions (Add, Remove, Move to Cart)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($action === 'add' && $product_id > 0) {
        // Prevent duplicates
        $chk = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $chk->execute([$user_id, $product_id]);
        if (!$chk->fetch()) {
            $ins = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $ins->execute([$user_id, $product_id]);
            $msg = 'added';
        } else {
            $msg = 'exists';
        }
    }

    if ($action === 'remove' && $product_id > 0) {
        $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $del->execute([$user_id, $product_id]);
        $msg = 'removed';
    }

    if ($action === 'move_to_cart' && $product_id > 0) {
        // Find default variation or main product price
        $p_stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
        $p_stmt->execute([$product_id]);
        $price = $p_stmt->fetchColumn();

        if ($price !== false) {
            // Get or create cart ID for user
            $c_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
            $c_stmt->execute([$user_id]);
            $cart_id = $c_stmt->fetchColumn();

            if (!$cart_id) {
                $c_ins = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
                $c_ins->execute([$user_id]);
                $cart_id = $pdo->lastInsertId();
            }

            // Check if product already in cart
            $item_chk = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variation_id IS NULL");
            $item_chk->execute([$cart_id, $product_id]);
            $existing = $item_chk->fetch();

            if ($existing) {
                $upd = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE cart_item_id = ?");
                $upd->execute([$existing['cart_item_id']]);
            } else {
                $ins_cart = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, 1, ?)");
                $ins_cart->execute([$cart_id, $product_id, $price]);
            }

            // Remove from wishlist
            $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $del->execute([$user_id, $product_id]);
            $msg = 'moved';
        }
    }

    // Check if request is AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'msg' => $msg,
            'wishlist_count' => get_wishlist_count($pdo),
            'cart_count' => get_cart_count($pdo)
        ]);
        exit();
    } else {
        header("Location: wishlist.php?msg=" . $msg);
        exit();
    }
}

// Fetch Wishlist Items
$sql = "SELECT w.wishlist_id, w.created_at AS added_date, p.*, c.category_name 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.product_id 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE w.user_id = ? 
        ORDER BY w.wishlist_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();

$page_title = "My Wishlist";
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Banner -->
<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 40px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.4rem;">My Saved Wishlist</h1>
    <p style="color: rgba(255,255,255,0.85);">Keep track of your favorite indoor greenery and move them to cart anytime.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <!-- Notification Toasts -->
    <?php if (isset($_GET['msg'])): ?>
      <div style="background:#d1fae5; color:#065f46; padding:15px 20px; border-radius:var(--radius-sm); margin-bottom:30px; border-left:4px solid #10b981;">
        <?php 
          if ($_GET['msg'] === 'added') echo "✓ Plant added to your wishlist!";
          if ($_GET['msg'] === 'exists') echo "Plant is already in your wishlist.";
          if ($_GET['msg'] === 'removed') echo "✓ Plant removed from your wishlist.";
          if ($_GET['msg'] === 'moved') echo "✓ Plant moved to your shopping cart successfully!";
        ?>
      </div>
    <?php endif; ?>

    <?php if (count($wishlist_items) > 0): ?>
      <div class="product-grid">
        <?php foreach ($wishlist_items as $item): ?>
          <div class="product-card">
            <span class="product-badge"><?php echo htmlspecialchars($item['size']); ?></span>
            
            <div class="product-img-wrapper">
              <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                <img src="../<?php echo htmlspecialchars($item['main_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
              </a>
            </div>

            <div class="product-info">
              <span class="product-category-tag"><?php echo htmlspecialchars($item['category_name']); ?></span>
              <h3 class="product-name">
                <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                  <?php echo htmlspecialchars($item['product_name']); ?>
                </a>
              </h3>

              <div class="product-meta">
                <span class="product-meta-item"> <?php echo htmlspecialchars($item['light_requirement']); ?></span>
                <span class="product-meta-item"> <?php echo htmlspecialchars($item['watering_requirement']); ?></span>
              </div>

              <div class="product-price-row" style="margin-bottom:15px;">
                <div class="product-price">Rs. <?php echo number_format($item['price']); ?></div>
                <div style="font-size:0.85rem; font-weight:600; color:<?php echo ($item['stock_quantity'] > 0) ? '#10b981' : '#ef4444'; ?>;">
                  <?php echo ($item['stock_quantity'] > 0) ? 'In Stock' : 'Out of Stock'; ?>
                </div>
              </div>

              <div style="display:grid; grid-template-columns: 1fr auto; gap:10px;">
                <form method="POST" action="wishlist.php">
                  <input type="hidden" name="action" value="move_to_cart">
                  <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                  <button type="submit" class="btn btn-emerald btn-sm btn-block" <?php echo ($item['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                     Move to Cart
                  </button>
                </form>

                <form method="POST" action="wishlist.php" onsubmit="return confirm('Remove this plant from your wishlist?');">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                  <button type="submit" class="btn btn-sm" style="background:#fee2e2; color:#ef4444; border:none; padding:8px 12px;" title="Remove from Wishlist">
                    🗑️
                  </button>
                </form>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="background: var(--white); padding: 60px; text-align: center; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
        <h2>Your Wishlist is Empty</h2>
        <p style="color: var(--text-muted); margin-bottom: 25px;">You haven't saved any indoor plants to your wishlist yet.</p>
        <a href="shop.php" class="btn btn-emerald">Explore Plant Shop</a>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

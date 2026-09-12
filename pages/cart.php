<?php
// pages/cart.php - Shopping Cart View & Management
define('IS_SUBPAGE', true);

require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo) || !$pdo) {
    die("Database Connection Error: Please check MySQL server and database setup.");
}
/** @var PDO $pdo */
// Helper to get or create active cart ID
function get_or_create_cart_id($pdo) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_id = $stmt->fetchColumn();

        if (!$cart_id) {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
        }
        return $cart_id;
    } else {
        $session_id = session_id();
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $cart_id = $stmt->fetchColumn();

        if (!$cart_id) {
            $stmt = $pdo->prepare("INSERT INTO cart (session_id) VALUES (?)");
            $stmt->execute([$session_id]);
            $cart_id = $pdo->lastInsertId();
        }
        return $cart_id;
    }
}

$cart_id = get_or_create_cart_id($pdo);

// Handle Actions (Add, Update, Remove, Clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $product_id = intval($_POST['product_id']);
        $variation_id = !empty($_POST['variation_id']) ? intval($_POST['variation_id']) : null;
        $quantity = max(1, intval($_POST['quantity']));
        $buy_now = isset($_POST['buy_now']) && $_POST['buy_now'] == 1;
/** @var PDO $pdo */
        // Fetch product & variation price
        $p_stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
        $p_stmt->execute([$product_id]);
        $unit_price = floatval($p_stmt->fetchColumn());

        if ($variation_id) {
            $v_stmt = $pdo->prepare("SELECT additional_price FROM product_variations WHERE variation_id = ?");
            $v_stmt->execute([$variation_id]);
            $add_price = floatval($v_stmt->fetchColumn());
            $unit_price += $add_price;
        }

        // Check if item already exists in cart
        if ($variation_id) {
            $chk = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variation_id = ?");
            $chk->execute([$cart_id, $product_id, $variation_id]);
        } else {
            $chk = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variation_id IS NULL");
            $chk->execute([$cart_id, $product_id]);
        }
        $existing = $chk->fetch();

        if ($existing) {
            $new_qty = $existing['quantity'] + $quantity;
            $upd = $pdo->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE cart_item_id = ?");
            $upd->execute([$new_qty, $unit_price, $existing['cart_item_id']]);
        } else {
            $ins = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, variation_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$cart_id, $product_id, $variation_id, $quantity, $unit_price]);
        }

        if ($buy_now) {
            header("Location: checkout.php");
            exit();
        } else {
            header("Location: cart.php?msg=added");
            exit();
        }
    }/** @var PDO $pdo */

    if ($action === 'update') {
        $cart_item_id = intval($_POST['cart_item_id']);
        $quantity = max(1, intval($_POST['quantity']));
        $upd = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?");
        $upd->execute([$quantity, $cart_item_id, $cart_id]);
        header("Location: cart.php?msg=updated");
        exit();
    }

    if ($action === 'remove') {
        $cart_item_id = intval($_POST['cart_item_id']);
        $del = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id = ?");
        $del->execute([$cart_item_id, $cart_id]);
        header("Location: cart.php?msg=removed");
        exit();
    }

    if ($action === 'clear') {
        $clr = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $clr->execute([$cart_id]);
        header("Location: cart.php?msg=cleared");
        exit();
    }
}

// Fetch Cart Items with detailed product & variation data
$sql = "SELECT ci.*, p.product_name, p.main_image, 
               pv.image AS variation_image, pt.pot_type_name, pc.colour_name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        LEFT JOIN product_variations pv ON ci.variation_id = pv.variation_id
        LEFT JOIN pot_types pt ON pv.pot_type_id = pt.pot_type_id
        LEFT JOIN pot_colours pc ON pv.pot_colour_id = pc.pot_colour_id
        WHERE ci.cart_id = ?
        ORDER BY ci.cart_item_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$cart_id]);
$cart_items = $stmt->fetchAll();

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}

$page_title = "Your Shopping Cart";
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Cart Header Banner -->
<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 40px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.4rem;">Your Shopping Cart</h1>
    <p style="color: rgba(255,255,255,0.85);">Review your indoor plant selections and customized pot variations.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <!-- Toast Notification Messages -->
    <?php if (isset($_GET['msg'])): ?>
      <div style="background:#d1fae5; color:#065f46; padding:15px 20px; border-radius:var(--radius-sm); margin-bottom:30px; border-left:4px solid #10b981;">
        <?php 
          if ($_GET['msg'] === 'added') echo "✓ Plant added to your cart successfully!";
          if ($_GET['msg'] === 'updated') echo "✓ Cart item quantity updated.";
          if ($_GET['msg'] === 'removed') echo "✓ Item removed from your cart.";
          if ($_GET['msg'] === 'cleared') echo "✓ Your shopping cart has been emptied.";
        ?>
      </div>
    <?php endif; ?>

    <?php if (count($cart_items) > 0): ?>
      <div class="shop-layout" style="grid-template-columns: 1fr 340px;">
        
        <!-- Cart Items Table -->
        <div>
          <div class="cart-table-wrapper">
            <table class="cart-table">
              <thead>
                <tr>
                  <th>Product Details</th>
                  <th>Unit Price</th>
                  <th>Quantity</th>
                  <th>Subtotal</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cart_items as $item): 
                  $item_img = !empty($item['variation_image']) ? $item['variation_image'] : $item['main_image'];
                  $item_total = $item['price'] * $item['quantity'];
                ?>
                  <tr>
                    <td>
                      <div class="cart-item-flex">
                        <img src="../<?php echo htmlspecialchars($item_img); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="cart-item-img">
                        <div>
                          <h4 style="font-size:1.1rem; margin-bottom:4px;">
                            <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                              <?php echo htmlspecialchars($item['product_name']); ?>
                            </a>
                          </h4>
                          <?php if ($item['pot_type_name'] && $item['colour_name']): ?>
                            <div style="font-size:0.85rem; color:var(--emerald); font-weight:600;">
                              🪴 <?php echo htmlspecialchars($item['pot_type_name']); ?> Pot • <?php echo htmlspecialchars($item['colour_name']); ?> Finish
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>

                    <td style="font-weight:600; color:var(--primary-forest);">
                      Rs. <?php echo number_format($item['price']); ?>
                    </td>

                    <td>
                      <form method="POST" action="cart.php" style="display:flex; align-items:center; gap:8px;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                        
                        <div class="quantity-control" style="transform:scale(0.85); transform-origin:left;">
                          <button type="button" class="qty-btn" data-action="minus" onclick="this.form.submit();">-</button>
                          <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="50" onchange="this.form.submit();">
                          <button type="button" class="qty-btn" data-action="plus" onclick="this.form.submit();">+</button>
                        </div>
                      </form>
                    </td>

                    <td style="font-size:1.1rem; font-weight:700; color:var(--primary-forest);">
                      Rs. <?php echo number_format($item_total); ?>
                    </td>

                    <td>
                      <form method="POST" action="cart.php" onsubmit="return confirm('Remove this item from cart?');">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:1.2rem;" title="Remove Item">
                          🗑️
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center;">
            <a href="shop.php" class="btn btn-outline-dark">← Continue Shopping</a>
            
            <form method="POST" action="cart.php" onsubmit="return confirm('Empty your cart?');">
              <input type="hidden" name="action" value="clear">
              <button type="submit" class="btn btn-sm" style="background:#fee2e2; color:#991b1b;">Clear Shopping Cart</button>
            </form>
          </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="cart-summary-card">
          <h3 style="margin-bottom:20px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Order Summary</h3>
          
          <div class="summary-row">
            <span>Subtotal</span>
            <span>Rs. <?php echo number_format($subtotal); ?></span>
          </div>

          <div class="summary-row">
            <span>Estimated Shipping</span>
            <span><?php echo ($subtotal >= 5000) ? '<strong style="color:var(--emerald);">FREE</strong>' : 'Rs. 350'; ?></span>
          </div>

          <?php 
            $shipping = ($subtotal >= 5000) ? 0.00 : 350.00;
            $grand_total = $subtotal + $shipping;
          ?>

          <div class="summary-row total">
            <span>Total Price</span>
            <span>Rs. <?php echo number_format($grand_total); ?></span>
          </div>

          <p style="font-size:0.85rem; color:var(--text-muted); margin: 15px 0 25px;">
            🔒 Secure 256-Bit SSL Encrypted Checkout.
          </p>

          <a href="checkout.php" class="btn btn-emerald btn-block" style="padding:14px;">
            Proceed to Checkout ➔
          </a>
        </div>

      </div>
    <?php else: ?>
      <div style="background:var(--white); padding:60px; text-align:center; border-radius:var(--radius-md); border:1px solid var(--border-color);">
        <div style="font-size:3.5rem; margin-bottom:15px;">🛒</div>
        <h2>Your Shopping Cart is Empty</h2>
        <p style="color:var(--text-muted); margin-bottom:25px;">Looks like you haven't added any indoor plants to your cart yet.</p>
        <a href="shop.php" class="btn btn-emerald">Explore Indoor Plants Catalog</a>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

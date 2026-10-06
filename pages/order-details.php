<?php

// pages/order-details.php - Customer Single Order View
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
/** @var PDO $pdo */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Fetch order items with variations
$sql = "SELECT oi.*, p.product_name, p.main_image, 
               pv.image AS variation_image, pt.pot_type_name, pc.colour_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN product_variations pv ON oi.variation_id = pv.variation_id
        LEFT JOIN pot_types pt ON pv.pot_type_id = pt.pot_type_id
        LEFT JOIN pot_colours pc ON pv.pot_colour_id = pc.pot_colour_id
        WHERE oi.order_id = ?";

$i_stmt = $pdo->prepare($sql);
$i_stmt->execute([$order_id]);
$order_items = $i_stmt->fetchAll();

$page_title = "Order Details #" . $order_id;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width:850px;">
    
    <div style="margin-bottom:20px;">
      <a href="orders.php" class="btn btn-outline-dark btn-sm">← Back to Order History</a>
    </div>

    <div class="form-card">
      <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid var(--sage-light); padding-bottom:15px; margin-bottom:25px;">
        <h2>Order Details #<?php echo $order_id; ?></h2>
        <span style="background:var(--emerald); color:var(--white); font-weight:700; padding:6px 16px; border-radius:20px; font-size:0.85rem;">
          <?php echo htmlspecialchars($order['order_status']); ?>
        </span>
      </div>

      <div class="form-grid-2" style="margin-bottom:30px;">
        <div>
          <h4 style="color:var(--emerald); margin-bottom:6px;">Delivery Details</h4>
          <p style="font-weight:600;"><?php echo htmlspecialchars($order['delivery_name']); ?></p>
          <p style="color:var(--text-muted); font-size:0.9rem;">
            <?php echo htmlspecialchars($order['delivery_address']); ?><br>
            <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['province']); ?> <?php echo htmlspecialchars($order['postal_code']); ?><br>
             <?php echo htmlspecialchars($order['phone']); ?>
          </p>
        </div>

        <div>
          <h4 style="color:var(--emerald); margin-bottom:6px;">Payment Information</h4>
          <p style="font-size:0.9rem; color:var(--text-muted);">
            <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?><br>
            <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['payment_status']); ?><br>
            <strong>Placed On:</strong> <?php echo date('F j, Y - g:i A', strtotime($order['created_at'])); ?>
          </p>
        </div>
      </div>

      <div class="cart-table-wrapper" style="margin-bottom:25px;">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Plant Variation</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($order_items as $item): 
              $img = !empty($item['variation_image']) ? $item['variation_image'] : $item['main_image'];
            ?>
              <tr>
                <td>
                  <div class="cart-item-flex">
                    <img src="../<?php echo htmlspecialchars($img); ?>" alt="" class="cart-item-img">
                    <div>
                      <h4 style="font-size:1rem; margin-bottom:2px;"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                      <?php if ($item['pot_type_name']): ?>
                        <div style="font-size:0.85rem; color:var(--emerald);">
                           <?php echo htmlspecialchars($item['pot_type_name']); ?> Pot (<?php echo htmlspecialchars($item['colour_name']); ?>)
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td>Rs. <?php echo number_format($item['price']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td style="font-weight:700; color:var(--primary-forest);">Rs. <?php echo number_format($item['price'] * $item['quantity']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; background:var(--sage-light); padding:18px 24px; border-radius:var(--radius-sm);">
        <span style="font-size:1.1rem; font-weight:700; color:var(--primary-forest);">Grand Total</span>
        <span style="font-size:1.4rem; font-weight:700; color:var(--emerald);">Rs. <?php echo number_format($order['total_amount']); ?></span>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

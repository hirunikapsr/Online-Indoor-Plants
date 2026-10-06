<?php
// pages/order-confirmation.php - Order Receipt & Confirmation Page
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
/** @var PDO $pdo */
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Order record
$o_stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$o_stmt->execute([$order_id]);
$order = $o_stmt->fetch();

if (!$order) {
    header("Location: shop.php");
    exit();
}

// Fetch Order Items with variations
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

$page_title = "Order Confirmation #" . $order_id;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section text-center" style="background:var(--sage-light); padding:50px 0;">
  <div class="container">
    <div style="width:80px; height:80px; background:var(--emerald); color:var(--white); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2.5rem; margin:0 auto 20px;">
      ✓
    </div>
    <h1 style="font-size:2.6rem; margin-bottom:10px;">Thank You For Your Order!</h1>
    <p style="font-size:1.1rem; color:var(--text-muted);">
      Your order <strong>#<?php echo $order_id; ?></strong> has been placed and is currently being prepared for safe delivery.
    </p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:850px;">
    
    <div class="form-card">
      <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid var(--sage-light); padding-bottom:15px; margin-bottom:25px;">
        <h3>Order Receipt Details</h3>
        <span style="background:var(--sage-accent); color:var(--primary-forest); font-weight:700; padding:6px 16px; border-radius:20px; font-size:0.85rem;">
          Status: <?php echo htmlspecialchars($order['order_status']); ?>
        </span>
      </div>

      <!-- Customer & Shipping Summary Grid -->
      <div class="form-grid-2" style="margin-bottom:30px;">
        <div>
          <h4 style="color:var(--emerald); margin-bottom:8px;">Shipping Address</h4>
          <p style="font-weight:600;"><?php echo htmlspecialchars($order['delivery_name']); ?></p>
          <p style="color:var(--text-muted); font-size:0.95rem;">
            <?php echo htmlspecialchars($order['delivery_address']); ?><br>
            <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['province']); ?> <?php echo htmlspecialchars($order['postal_code']); ?><br>
             <?php echo htmlspecialchars($order['phone']); ?>
          </p>
        </div>

        <div>
          <h4 style="color:var(--emerald); margin-bottom:8px;">Payment & Date</h4>
          <p style="font-size:0.95rem; color:var(--text-muted);">
            <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?><br>
            <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['payment_status']); ?><br>
            <strong>Order Date:</strong> <?php echo date('F j, Y - g:i A', strtotime($order['created_at'])); ?>
          </p>
        </div>
      </div>

      <!-- Itemized Order Table -->
      <div class="cart-table-wrapper" style="margin-bottom:30px;">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Plant & Pot Variation</th>
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
                          🪴 <?php echo htmlspecialchars($item['pot_type_name']); ?> Pot (<?php echo htmlspecialchars($item['colour_name']); ?>)
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

      <!-- Grand Total Row -->
      <div style="display:flex; justify-content:space-between; align-items:center; background:var(--sage-light); padding:20px; border-radius:var(--radius-sm); margin-bottom:30px;">
        <span style="font-size:1.2rem; font-weight:700; color:var(--primary-forest);">Total Amount Paid</span>
        <span style="font-size:1.5rem; font-weight:700; color:var(--emerald);">Rs. <?php echo number_format($order['total_amount']); ?></span>
      </div>

      <div style="display:flex; justify-content:center; gap:20px;">
        <a href="orders.php" class="btn btn-emerald">View Order History</a>
        <a href="shop.php" class="btn btn-outline-dark">Continue Shopping</a>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

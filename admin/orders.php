<?php
// admin/orders.php - Order Management & Status Workflow
$page_title = "Manage Customer Orders";
require_once __DIR__ . '/includes/admin_header.php';

$msg = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id']);
    $order_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'];

    $upd = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?");
    $upd->execute([$order_status, $payment_status, $order_id]);
    $msg = "Order #{$order_id} status updated to {$order_status}!";
}

// Single Order Detail Modal/View if order_id parameter passed
$selected_order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$selected_order = null;
$selected_items = [];

if ($selected_order_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$selected_order_id]);
    $selected_order = $stmt->fetch();

    if ($selected_order) {
        $i_stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.main_image, 
                       pv.image AS variation_image, pt.pot_type_name, pc.colour_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN product_variations pv ON oi.variation_id = pv.variation_id
                LEFT JOIN pot_types pt ON pv.pot_type_id = pt.pot_type_id
                LEFT JOIN pot_colours pc ON pv.pot_colour_id = pc.pot_colour_id
                WHERE oi.order_id = ?");
        $i_stmt->execute([$selected_order_id]);
        $selected_items = $i_stmt->fetchAll();
    }
}

// Fetch all orders
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_sql = !empty($status_filter) ? "WHERE o.order_status = " . $pdo->quote($status_filter) : "";

$orders = $pdo->query("SELECT o.*, u.name AS user_name, u.email AS user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    {$where_sql}
    ORDER BY o.order_id DESC")->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
  <div>
    <h1 style="font-size:2rem;">Customer Order Management</h1>
    <p style="color:var(--text-muted);">Process pending orders, update dispatch states, and inspect delivery info.</p>
  </div>
</div>

<?php if ($msg): ?>
  <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #10b981;">
    ✓ <?php echo htmlspecialchars($msg); ?>
  </div>
<?php endif; ?>

<!-- Status Filter Tabs -->
<div style="display:flex; gap:10px; margin-bottom:25px; flex-wrap:wrap;">
  <a href="orders.php" class="btn btn-sm <?php echo empty($status_filter) ? 'btn-emerald' : 'btn-outline-dark'; ?>">All Orders</a>
  <a href="orders.php?status=Pending" class="btn btn-sm <?php echo ($status_filter === 'Pending') ? 'btn-emerald' : 'btn-outline-dark'; ?>">Pending</a>
  <a href="orders.php?status=Confirmed" class="btn btn-sm <?php echo ($status_filter === 'Confirmed') ? 'btn-emerald' : 'btn-outline-dark'; ?>">Confirmed</a>
  <a href="orders.php?status=Processing" class="btn btn-sm <?php echo ($status_filter === 'Processing') ? 'btn-emerald' : 'btn-outline-dark'; ?>">Processing</a>
  <a href="orders.php?status=Shipped" class="btn btn-sm <?php echo ($status_filter === 'Shipped') ? 'btn-emerald' : 'btn-outline-dark'; ?>">Shipped</a>
  <a href="orders.php?status=Delivered" class="btn btn-sm <?php echo ($status_filter === 'Delivered') ? 'btn-emerald' : 'btn-outline-dark'; ?>">Delivered</a>
  <a href="orders.php?status=Cancelled" class="btn btn-sm <?php echo ($status_filter === 'Cancelled') ? 'btn-emerald' : 'btn-outline-dark'; ?>">Cancelled</a>
</div>

<!-- If Single Order Selected for Inspection -->
<?php if ($selected_order): ?>
  <div class="form-card" style="margin-bottom:40px; border:2px solid var(--emerald);">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid var(--sage-light); padding-bottom:15px; margin-bottom:20px;">
      <h2>Inspect Order #<?php echo $selected_order['order_id']; ?></h2>
      <a href="orders.php" class="btn btn-outline-dark btn-sm">Close Inspection</a>
    </div>

    <form method="POST" action="orders.php" style="display:flex; gap:20px; align-items:center; background:var(--sage-light); padding:15px; border-radius:var(--radius-sm); margin-bottom:25px;">
      <input type="hidden" name="action" value="update_status">
      <input type="hidden" name="order_id" value="<?php echo $selected_order['order_id']; ?>">
      
      <div>
        <label style="font-weight:600; font-size:0.9rem;">Order Status:</label>
        <select name="order_status" class="form-control" style="width:auto; display:inline-block; margin-left:8px;">
          <option value="Pending" <?php echo ($selected_order['order_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
          <option value="Confirmed" <?php echo ($selected_order['order_status'] === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
          <option value="Processing" <?php echo ($selected_order['order_status'] === 'Processing') ? 'selected' : ''; ?>>Processing</option>
          <option value="Shipped" <?php echo ($selected_order['order_status'] === 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
          <option value="Delivered" <?php echo ($selected_order['order_status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
          <option value="Cancelled" <?php echo ($selected_order['order_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
        </select>
      </div>

      <div>
        <label style="font-weight:600; font-size:0.9rem;">Payment Status:</label>
        <select name="payment_status" class="form-control" style="width:auto; display:inline-block; margin-left:8px;">
          <option value="Pending" <?php echo ($selected_order['payment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
          <option value="Paid" <?php echo ($selected_order['payment_status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
          <option value="Failed" <?php echo ($selected_order['payment_status'] === 'Failed') ? 'selected' : ''; ?>>Failed</option>
        </select>
      </div>

      <button type="submit" class="btn btn-emerald btn-sm">Update Order Status</button>
    </form>

    <div class="form-grid-2" style="margin-bottom:25px;">
      <div>
        <h4 style="color:var(--emerald); margin-bottom:5px;">Recipient & Delivery Address</h4>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($selected_order['delivery_name']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($selected_order['delivery_address']); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($selected_order['city']); ?>, <?php echo htmlspecialchars($selected_order['province']); ?> <?php echo htmlspecialchars($selected_order['postal_code']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($selected_order['phone']); ?></p>
      </div>

      <div>
        <h4 style="color:var(--emerald); margin-bottom:5px;">Payment Details</h4>
        <p><strong>Method:</strong> <?php echo htmlspecialchars($selected_order['payment_method']); ?></p>
        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($selected_order['payment_status']); ?></p>
        <p><strong>Total Amount:</strong> Rs. <?php echo number_format($selected_order['total_amount']); ?></p>
        <p><strong>Order Date:</strong> <?php echo date('M d, Y - g:i A', strtotime($selected_order['created_at'])); ?></p>
      </div>
    </div>

    <!-- Items list -->
    <div class="cart-table-wrapper" style="margin-bottom:0;">
      <table class="cart-table">
        <thead>
          <tr>
            <th>Plant Variation</th>
            <th>Unit Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($selected_items as $item): 
            $img = !empty($item['variation_image']) ? $item['variation_image'] : $item['main_image'];
          ?>
            <tr>
              <td>
                <div class="cart-item-flex">
                  <img src="../<?php echo htmlspecialchars($img); ?>" alt="" class="cart-item-img">
                  <div>
                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
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
              <td style="font-weight:700;">Rs. <?php echo number_format($item['price'] * $item['quantity']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
<?php endif; ?>

<!-- Orders Master Table -->
<div class="cart-table-wrapper">
  <table class="cart-table">
    <thead>
      <tr>
        <th>Order #</th>
        <th>Customer</th>
        <th>Date</th>
        <th>Payment</th>
        <th>Order Status</th>
        <th>Total</th>
        <th>Manage</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td style="font-weight:700; color:var(--primary-forest);">#<?php echo $o['order_id']; ?></td>

          <td>
            <strong><?php echo htmlspecialchars($o['delivery_name']); ?></strong><br>
            <span style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($o['city']); ?> • 📞 <?php echo htmlspecialchars($o['phone']); ?></span>
          </td>

          <td style="font-size:0.85rem; color:var(--text-muted);">
            <?php echo date('M d, Y', strtotime($o['created_at'])); ?>
          </td>

          <td style="font-size:0.85rem;">
            <?php echo htmlspecialchars($o['payment_method']); ?><br>
            <span style="font-weight:600; color:<?php echo ($o['payment_status'] === 'Paid') ? '#10b981' : '#f59e0b'; ?>;">
              <?php echo htmlspecialchars($o['payment_status']); ?>
            </span>
          </td>

          <td>
            <form method="POST" action="orders.php" style="display:flex; gap:6px;">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
              <input type="hidden" name="payment_status" value="<?php echo $o['payment_status']; ?>">
              
              <select name="order_status" onchange="this.form.submit();" style="padding:4px 8px; border-radius:12px; font-size:0.8rem; font-weight:700; background:var(--sage-light); border:1px solid var(--emerald); cursor:pointer;">
                <option value="Pending" <?php echo ($o['order_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Confirmed" <?php echo ($o['order_status'] === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                <option value="Processing" <?php echo ($o['order_status'] === 'Processing') ? 'selected' : ''; ?>>Processing</option>
                <option value="Shipped" <?php echo ($o['order_status'] === 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                <option value="Delivered" <?php echo ($o['order_status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                <option value="Cancelled" <?php echo ($o['order_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
              </select>
            </form>
          </td>

          <td style="font-weight:700; color:var(--primary-forest);">
            Rs. <?php echo number_format($o['total_amount']); ?>
          </td>

          <td>
            <a href="orders.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-emerald btn-sm">Inspect</a>
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

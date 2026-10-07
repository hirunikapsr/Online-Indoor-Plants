<?php
// admin/index.php - Admin Dashboard Overview
$page_title = "Dashboard Overview";
require_once __DIR__ . '/includes/admin_header.php';

// Fetch metrics
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Delivered'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid' OR order_status != 'Cancelled'")->fetchColumn() ?: 0.00;

$low_stock_count = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM products WHERE stock_quantity <= 5 AND status = 'active') +
        (SELECT COUNT(*) FROM product_variations WHERE stock_quantity <= 5)
")->fetchColumn() ?: 0;

// Fetch 5 Recent Orders
$recent_orders = $pdo->query("SELECT o.*, u.name AS customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_id DESC LIMIT 5")->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
  <div>
    <h1 style="font-size:2.2rem; margin-bottom:5px;">Admin Dashboard</h1>
    <p style="color:var(--text-muted);">Overview of store activity, product inventory, and customer orders.</p>
  </div>
  <a href="add-product.php" class="btn btn-emerald">+ Add New Plant Product</a>
</div>

<!-- Metrics Cards Grid -->
<div class="admin-stats-grid" style="grid-template-columns: repeat(5, 1fr);">
  <div class="stat-card">
    <div style="display:flex; justify-content:space-between; color:var(--emerald); font-size:1.5rem;">
      <span></span>
      <span style="font-size:0.8rem; background:var(--sage-light); padding:2px 8px; border-radius:10px;">Revenue</span>
    </div>
    <div class="stat-val">Rs. <?php echo number_format($total_revenue); ?></div>
    <div style="font-size:0.85rem; color:var(--text-muted); margin-top:5px;">Total Sales Revenue</div>
    
  </div>

  <div class="stat-card">
    <div style="display:flex; justify-content:space-between; color:var(--emerald); font-size:1.5rem;">
      <span></span>
      <span style="font-size:0.8rem; background:var(--sage-light); padding:2px 8px; border-radius:10px;">Orders</span>
    </div>
    <div class="stat-val"><?php echo $total_orders; ?></div>
    <div style="font-size:0.85rem; color:var(--text-muted); margin-top:5px;"><?php echo $pending_orders; ?> Pending Action</div>
  </div>

  <div class="stat-card">
    <div style="display:flex; justify-content:space-between; color:var(--emerald); font-size:1.5rem;">
      <span></span>
      <span style="font-size:0.8rem; background:var(--sage-light); padding:2px 8px; border-radius:10px;">Products</span>
    </div>
    <div class="stat-val"><?php echo $total_products; ?></div>
    <div style="font-size:0.85rem; color:var(--text-muted); margin-top:5px;"><?php echo $total_categories; ?> Plant Categories</div>
  </div>

  <div class="stat-card">
    <div style="display:flex; justify-content:space-between; color:#d97706; font-size:1.5rem;">
      <span></span>
      <span style="font-size:0.8rem; background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:10px; font-weight:600;">Low Stock</span>
    </div>
    <div class="stat-val" style="color: #d90606;"><?php echo $low_stock_count; ?></div>
    <div style="font-size:0.85rem; color:var(--text-muted); margin-top:5px;">Items Need Restock</div>
  </div>

  <div class="stat-card">
    <div style="display:flex; justify-content:space-between; color:var(--emerald); font-size:1.5rem;">
      <span></span>
      <span style="font-size:0.8rem; background:var(--sage-light); padding:2px 8px; border-radius:10px;">Users</span>
    </div>
    <div class="stat-val"><?php echo $total_users; ?></div>
    <div style="font-size:0.85rem; color:var(--text-muted); margin-top:5px;">Registered Customers</div>
  </div>
</div>

<!-- Recent Orders Table -->
<div class="form-card" style="padding:30px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h3>Recent Customer Orders</h3>
    <a href="orders.php" style="color:var(--emerald); font-weight:600; font-size:0.9rem;">View All Orders ➔</a>
  </div>

  <div class="cart-table-wrapper" style="margin-bottom:0;">
    <table class="cart-table">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Date</th>
          <th>Payment</th>
          <th>Total</th>
          <th>Status</th>
          <th>Manage</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_orders as $order): ?>
          <tr>
            <td style="font-weight:700;">#<?php echo $order['order_id']; ?></td>
            <td>
              <strong><?php echo htmlspecialchars($order['delivery_name']); ?></strong><br>
              <span style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($order['city']); ?></span>
            </td>
            <td style="font-size:0.85rem;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
            <td style="font-size:0.85rem;"><?php echo htmlspecialchars($order['payment_method']); ?></td>
            <td style="font-weight:700; color:var(--primary-forest);">Rs. <?php echo number_format($order['total_amount']); ?></td>
            <td>
              <span style="background:var(--sage-light); color:var(--emerald); font-weight:700; padding:4px 12px; border-radius:12px; font-size:0.8rem;">
                <?php echo htmlspecialchars($order['order_status']); ?>
              </span>
            </td>
            <td>
              <a href="orders.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-emerald btn-sm">Manage</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</main>
</div>
</body>
</html>

<?php
// admin/users.php - Registered User Accounts Management
$page_title = "Manage Registered Users";
require_once __DIR__ . '/includes/admin_header.php';

// Fetch users with order counts
$sql = "SELECT u.*, COUNT(o.order_id) AS total_orders 
        FROM users u 
        LEFT JOIN orders o ON u.user_id = o.user_id 
        GROUP BY u.user_id 
        ORDER BY u.user_id DESC";
$users = $pdo->query($sql)->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
  <div>
    <h1 style="font-size:2rem;">Registered Customer Accounts</h1>
    <p style="color:var(--text-muted);">View registered customer details, contact info, and activity history.</p>
  </div>
</div>

<div class="cart-table-wrapper">
  <table class="cart-table">
    <thead>
      <tr>
        <th>User ID</th>
        <th>Customer Name</th>
        <th>Email Address</th>
        <th>Phone & Location</th>
        <th>Role</th>
        <th>Total Orders</th>
        <th>Registered Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td style="font-weight:700;">#<?php echo $u['user_id']; ?></td>

          <td>
            <strong><?php echo htmlspecialchars($u['name']); ?></strong>
          </td>

          <td>
            <?php echo htmlspecialchars($u['email']); ?>
          </td>

          <td style="font-size:0.85rem; color:var(--text-muted);">
            <?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?><br>
            <?php echo htmlspecialchars($u['city'] ?? 'N/A'); ?>
          </td>

          <td>
            <span style="background:<?php echo ($u['role'] === 'admin') ? '#dbeafe' : 'var(--sage-light)'; ?>; color:<?php echo ($u['role'] === 'admin') ? '#1e40af' : 'var(--emerald)'; ?>; font-weight:700; padding:4px 10px; border-radius:12px; font-size:0.8rem;">
              <?php echo ucfirst($u['role']); ?>
            </span>
          </td>

          <td style="font-weight:700; color:var(--primary-forest);">
            <?php echo $u['total_orders']; ?> orders
          </td>

          <td style="font-size:0.85rem; color:var(--text-muted);">
            <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
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

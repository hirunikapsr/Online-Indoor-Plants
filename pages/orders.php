<?php
// pages/orders.php - Customer Order History List
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
/** @var PDO $pdo */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_id DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

$page_title = "My Order History";
require_once __DIR__ . '/../includes/header.php';
?>

<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 40px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.4rem;">Order History</h1>
    <p style="color: rgba(255,255,255,0.85);">Track and review your previous indoor plant purchases.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    
    <div class="shop-layout" style="grid-template-columns: 280px 1fr;">
      
      <!-- Customer Nav Card -->
      <div class="filter-sidebar">
        <ul class="filter-list">
          <li>
            <a href="profile.php" class="btn btn-outline-dark btn-block btn-sm" style="text-align:left;">
               Personal Details
            </a>
          </li>
          <li>
            <a href="orders.php" class="btn btn-emerald btn-block btn-sm" style="text-align:left; margin-top:8px;">
               Order History (<?php echo count($orders); ?>)
            </a>
          </li>
          <li>
            <a href="logout.php" class="btn btn-sm btn-block" style="text-align:left; margin-top:8px; background:#fee2e2; color:#991b1b;">
               Sign Out
            </a>
          </li>
        </ul>
      </div>

      <!-- Orders List Table -->
      <div>
        <?php if (count($orders) > 0): ?>
          <div class="cart-table-wrapper">
            <table class="cart-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Date</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Details</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orders as $order): ?>
                  <tr>
                    <td style="font-weight:700; color:var(--primary-forest);">
                      #<?php echo $order['order_id']; ?>
                    </td>

                    <td style="font-size:0.9rem; color:var(--text-muted);">
                      <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                    </td>

                    <td style="font-size:0.9rem;">
                      <?php echo htmlspecialchars($order['payment_method']); ?>
                    </td>

                    <td>
                      <span style="background:var(--sage-light); color:var(--emerald); font-weight:700; padding:4px 12px; border-radius:12px; font-size:0.8rem;">
                        <?php echo htmlspecialchars($order['order_status']); ?>
                      </span>
                    </td>

                    <td style="font-weight:700; color:var(--primary-forest);">
                      Rs. <?php echo number_format($order['total_amount']); ?>
                    </td>

                    <td>
                      <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-emerald btn-sm">
                        View Details
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div style="background:var(--white); padding:50px; text-align:center; border-radius:var(--radius-md); border:1px solid var(--border-color);">
            <div style="font-size:3rem; margin-bottom:15px;"></div>
            <h3>No Orders Found</h3>
            <p style="color:var(--text-muted); margin-bottom:20px;">You haven't placed any plant orders yet.</p>
            <a href="shop.php" class="btn btn-emerald">Start Shopping Now</a>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

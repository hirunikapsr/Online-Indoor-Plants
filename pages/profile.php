<?php
// pages/profile.php - Customer Profile & Account Details
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
/** @var PDO $pdo */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $postal_code = trim($_POST['postal_code']);

    if (empty($name)) {
        $error = 'Name cannot be empty.';
    } else {
        $upd = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, city = ?, province = ?, postal_code = ? WHERE user_id = ?");
        $upd->execute([$name, $phone, $address, $city, $province, $postal_code, $user_id]);
        $_SESSION['user_name'] = $name;
        $success = 'Profile details updated successfully!';
    }
}

// Fetch current user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch recent order count
$o_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$o_stmt->execute([$user_id]);
$order_count = $o_stmt->fetchColumn();

$page_title = "My Account Profile";
require_once __DIR__ . '/../includes/header.php';
?>

<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 40px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.4rem;">Customer Account Dashboard</h1>
    <p style="color: rgba(255,255,255,0.85);">Manage your shipping preferences and view order history.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    
    <div class="shop-layout" style="grid-template-columns: 280px 1fr;">
      
      <!-- Customer Nav Card -->
      <div class="filter-sidebar">
        <div style="text-align:center; padding-bottom:20px; border-bottom:1px solid var(--border-color); margin-bottom:20px;">
          <div style="width:70px; height:70px; background:var(--sage-light); color:var(--primary-forest); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 10px;">
            👤
          </div>
          <h3 style="font-size:1.2rem;"><?php echo htmlspecialchars($user['name']); ?></h3>
          <p style="font-size:0.85rem; color:var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <ul class="filter-list">
          <li>
            <a href="profile.php" class="btn btn-emerald btn-block btn-sm" style="text-align:left;">
              👤 Personal Details
            </a>
          </li>
          <li>
            <a href="orders.php" class="btn btn-outline-dark btn-block btn-sm" style="text-align:left; margin-top:8px;">
              📦 Order History (<?php echo $order_count; ?>)
            </a>
          </li>
          <li>
            <a href="logout.php" class="btn btn-sm btn-block" style="text-align:left; margin-top:8px; background:#fee2e2; color:#991b1b;">
              🚪 Sign Out
            </a>
          </li>
        </ul>
      </div>

      <!-- Main Profile Form -->
      <div class="form-card">
        <h2 style="margin-bottom:25px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Account Details</h2>

        <?php if ($success): ?>
          <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #10b981;">
            ✓ <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #ef4444;">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label">Email Address (Read Only)</label>
              <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background:#f1f5f9;">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Default Shipping Address</label>
            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Province</label>
              <input type="text" name="province" class="form-control" value="<?php echo htmlspecialchars($user['province'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
          </div>

          <button type="submit" class="btn btn-emerald" style="padding:12px 30px; margin-top:10px;">
            Save Profile Changes
          </button>
        </form>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

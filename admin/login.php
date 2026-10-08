<?php
// admin/login.php - Admin Dedicated Login Screen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin') {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter administrator credentials.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'admin';

            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign In - Online Indoor Plants</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">

  <div class="form-card" style="width: 100%; max-width: 440px; box-shadow: var(--shadow-lg);">
    <div class="text-center" style="margin-bottom: 25px;">
      <div style="font-size: 3rem; margin-bottom: 10px;">🌿</div>
      <h2 style="font-size: 2rem;">Admin Control Panel</h2>
      <p style="color: var(--text-muted); font-size: 0.95rem;">Sign in to access store administration.</p>
    </div>

    <?php if ($error): ?>
      <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #ef4444;">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label class="form-label">Admin Email</label>
        <input type="email" name="email" class="form-control" required value="admin@indoorplants.com">
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required value="password123">
      </div>

      <div style="background:var(--sage-light); padding:12px; border-radius:var(--radius-sm); margin-bottom:20px; font-size:0.85rem;">
        <strong>Default Admin Login:</strong><br>
        Email: <code>admin@indoorplants.com</code><br>
        Password: <code>password123</code>
      </div>

      <button type="submit" class="btn btn-emerald btn-block" style="padding: 14px;">
        Sign In to Admin Panel ➔
      </button>
    </form>

    <div class="text-center" style="margin-top:20px;">
      <a href="../index.php" style="color:var(--text-muted); font-size:0.9rem;">← Back to Customer Storefront</a>
    </div>
  </div>

</body>
</html>

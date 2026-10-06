<?php
// pages/login.php - Customer Account Login
session_start();
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
/** @var PDO $pdo */
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

// After Register, Display Success Message 
if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
    $success = 'Registration successful! Please sign in with your credentials.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            $error = 'Invalid email address or password combination.';
        }
    }
}

$page_title = "Sign In";
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section" style="min-height:75vh; display:flex; align-items:center;">
  <div class="container" style="max-width:480px;">
    
    <div class="form-card">
      <div class="text-center" style="margin-bottom:30px;">
        <h1 style="font-size:2.2rem; margin-bottom:8px;">Sign In to Your Account</h1>
        <p style="color:var(--text-muted);">Welcome back! Please enter your details below.</p>
      </div>

      <?php if ($success): ?>
        <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #10b981;">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #ef4444;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required placeholder="sarah@example.com" value="sarah@example.com">
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required placeholder="••••••••" value="password123">
        </div>

        <div style="background:var(--sage-light); padding:12px; border-radius:var(--radius-sm); margin-bottom:20px; font-size:0.85rem;">
          <strong>Demo Credentials:</strong><br>
          Customer: <code>sarah@example.com</code> / <code>password123</code><br>
          Admin: <code>admin@indoorplants.com</code> / <code>password123</code>
        </div>

        <button type="submit" class="btn btn-emerald btn-block" style="padding:14px;">
          Sign In ➔
        </button>
      </form>

      <div class="text-center" style="margin-top:25px; font-size:0.95rem;">
        Don't have an account? <a href="register.php" style="color:var(--emerald); font-weight:600;">Create Account</a>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

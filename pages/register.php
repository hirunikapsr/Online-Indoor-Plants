<?php
// pages/register.php - Customer Account Registration
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
/** @var PDO $pdo */
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email address already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $ins = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, city, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
            $ins->execute([$name, $email, $hashed_password, $phone, $address, $city]);
            
            // After User insert (Not Session set, directly redirect Login Page)
            $new_user_id = $pdo->lastInsertId();

            // Session Save and  Redirect to Login Page 
            session_write_close();
            
            // PHP Header Redirect
            header("Location: login.php?msg=registered");
            
            // Not PHP Header ,JS Redirect Fallback 
            echo "<script>window.location.href = 'login.php?msg=registered';</script>";
            exit();
            
        }
    }
}

$page_title = "Register Account";
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section" style="min-height:75vh; display:flex; align-items:center;">
  <div class="container" style="max-width:550px;">
    
    <div class="form-card">
      <div class="text-center" style="margin-bottom:30px;">
        <h1 style="font-size:2.2rem; margin-bottom:8px;">Create Customer Account</h1>
        <p style="color:var(--text-muted);">Join Indoor Plants club to manage orders & save delivery details.</p>
      </div>

      <?php if ($error): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; border-left:4px solid #ef4444;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" required placeholder="e.g. Sarah Jenkins">
        </div>

        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-control" required placeholder="name@example.com">
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required placeholder="••••••••">
          </div>

          <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="confirm_password" class="form-control" required placeholder="••••••••">
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="+94 77 123 4567">
          </div>

          <div class="form-group">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" placeholder="e.g. Kandy">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Delivery Address</label>
          <input type="text" name="address" class="form-control" placeholder="Street Address">
        </div>

        <button type="submit" class="btn btn-emerald btn-block" style="padding:14px; margin-top:10px;">
          Create Account ➔
        </button>
      </form>

      <div class="text-center" style="margin-top:25px; font-size:0.95rem;">
        Already have an account? <a href="login.php" style="color:var(--emerald); font-weight:600;">Sign In Here</a>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

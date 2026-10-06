<?php
// pages/checkout.php - Order Checkout & Payment Processing
define('IS_SUBPAGE', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/payhere_config.php';
/** @var PDO $pdo */

// Fetch Cart items
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $c_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $c_stmt->execute([$user_id]);
} else {
    $session_id = session_id();
    $c_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE session_id = ?");
    $c_stmt->execute([$session_id]);
}
$cart_id = $c_stmt->fetchColumn();

if (!$cart_id) {
    header("Location: shop.php");
    exit();
}

$sql = "SELECT ci.*, p.product_name, p.main_image, 
               pv.image AS variation_image, pt.pot_type_name, pc.colour_name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        LEFT JOIN product_variations pv ON ci.variation_id = pv.variation_id
        LEFT JOIN pot_types pt ON pv.pot_type_id = pt.pot_type_id
        LEFT JOIN pot_colours pc ON pv.pot_colour_id = pc.pot_colour_id
        WHERE ci.cart_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$cart_id]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) === 0) {
    header("Location: cart.php");
    exit();
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
$shipping = ($subtotal >= 5000) ? 0.00 : 350.00;
$grand_total = $subtotal + $shipping;

// Pre-fill user profile info if logged in
$user_info = null;
if (isset($_SESSION['user_id'])) {
    $u_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $u_stmt->execute([$_SESSION['user_id']]);
    $user_info = $u_stmt->fetch();
}

// Handle Order Processing
$error = '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_name = trim($_POST['delivery_name'] ?? '');
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? ($user_info['email'] ?? 'customer@onlineindoor.lk'));
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';

    if (empty($delivery_name) || empty($delivery_address) || empty($city) || empty($phone)) {
        $error = 'Please fill in all required shipping fields.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $error]);
            exit();
        }
    } else {
        try {
            $pdo->beginTransaction();

            $order_user_id = $_SESSION['user_id'] ?? 2; // Default customer ID if guest
            $payment_status = 'Pending';
            $order_status = ($payment_method === 'Cash on Delivery') ? 'Confirmed' : 'Pending';

            // Insert into orders
            $o_stmt = $pdo->prepare("INSERT INTO orders 
                (user_id, total_amount, delivery_name, delivery_address, city, province, postal_code, phone, payment_method, payment_status, order_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $o_stmt->execute([
                $order_user_id,
                $grand_total,
                $delivery_name,
                $delivery_address,
                $city,
                $province,
                $postal_code,
                $phone,
                $payment_method,
                $payment_status,
                $order_status
            ]);
            $order_id = $pdo->lastInsertId();

            // Insert items & reduce stock
            $item_ins = $pdo->prepare("INSERT INTO order_items (order_id, product_id, variation_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stock_upd = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $var_stock_upd = $pdo->prepare("UPDATE product_variations SET stock_quantity = stock_quantity - ? WHERE variation_id = ?");

            foreach ($cart_items as $ci) {
                $item_ins->execute([
                    $order_id,
                    $ci['product_id'],
                    $ci['variation_id'],
                    $ci['quantity'],
                    $ci['price']
                ]);

                // Reduce main product stock
                $stock_upd->execute([$ci['quantity'], $ci['product_id']]);

                // Reduce variation stock if applicable
                if (!empty($ci['variation_id'])) {
                    $var_stock_upd->execute([$ci['quantity'], $ci['variation_id']]);
                }
            }

            if ($payment_method === 'Cash on Delivery') {
                // Clear Cart for COD
                $clr_stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
                $clr_stmt->execute([$cart_id]);

                $pdo->commit();

                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'redirect' => 'order-confirmation.php?id=' . $order_id]);
                    exit();
                } else {
                    header("Location: order-confirmation.php?id=" . $order_id);
                    exit();
                }
            } else {
                // Card Payment via PayHere
                $pdo->commit();

                // Prepare PayHere details
                $name_parts = explode(' ', $delivery_name, 2);
                $first_name = $name_parts[0];
                $last_name = !empty($name_parts[1]) ? $name_parts[1] : $first_name;
                $customer_email = !empty($email) ? $email : ($user_info['email'] ?? 'customer@onlineindoor.lk');

                $base_url = payhere_get_base_url();
                $return_url = $base_url . '/pages/order-confirmation.php?id=' . $order_id . '&paid=1';
                $cancel_url = $base_url . '/pages/checkout.php?cancelled=1';
                $notify_url = $base_url . '/api/payhere_notify.php';

                $amount_formatted = number_format((float)$grand_total, 2, '.', '');
                $hash = payhere_generate_hash($order_id, $amount_formatted);

                $item_names = [];
                foreach ($cart_items as $ci) {
                    $item_names[] = $ci['product_name'] . ' (x' . $ci['quantity'] . ')';
                }
                $items_str = implode(', ', $item_names);
                if (strlen($items_str) > 200) {
                    $items_str = substr($items_str, 0, 197) . '...';
                }

                $payhere_data = [
                    'sandbox' => PAYHERE_SANDBOX,
                    'merchant_id' => PAYHERE_MERCHANT_ID,
                    'return_url' => $return_url,
                    'cancel_url' => $cancel_url,
                    'notify_url' => $notify_url,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $customer_email,
                    'phone' => $phone,
                    'address' => $delivery_address,
                    'city' => $city,
                    'country' => 'Sri Lanka',
                    'order_id' => (string)$order_id,
                    'items' => $items_str,
                    'currency' => PAYHERE_CURRENCY,
                    'amount' => $amount_formatted,
                    'hash' => $hash,
                ];

                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'payhere',
                        'order_id' => $order_id,
                        'payment' => $payhere_data
                    ]);
                    exit();
                } else {
                    $payhere_auto_start = $payhere_data;
                }
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Failed to record order. Details: ' . $e->getMessage();
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $error]);
                exit();
            }
        }
    }
}

$page_title = "Checkout Order";
require_once __DIR__ . '/../includes/header.php';
?>

<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 40px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.4rem;">Checkout & Delivery</h1>
    <p style="color: rgba(255,255,255,0.85);">Complete your shipping details to receive your indoor plants.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <div id="checkoutErrorBox" style="<?php echo empty($error) && !isset($_GET['cancelled']) ? 'display:none;' : ''; ?> background:#fee2e2; color:#991b1b; padding:15px 20px; border-radius:var(--radius-sm); margin-bottom:30px; border-left:4px solid #ef4444;">
      <?php 
        if (!empty($error)) {
            echo htmlspecialchars($error);
        } else if (isset($_GET['cancelled'])) {
            echo 'Your previous payment attempt was cancelled. You can try again or choose Cash on Delivery.';
        }
      ?>
    </div>

    <form method="POST" action="checkout.php" id="checkoutForm">
      <div class="shop-layout" style="grid-template-columns: 1fr 380px;">
        
        <!-- Delivery Details Form -->
        <div class="form-card">
          <h2 style="margin-bottom:25px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">1. Shipping & Delivery Address</h2>

          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="delivery_name" class="form-control" required value="<?php echo htmlspecialchars($user_info['name'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Delivery Address *</label>
            <textarea name="delivery_address" class="form-control" rows="3" required><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">City *</label>
              <input type="text" name="city" class="form-control" required value="<?php echo htmlspecialchars($user_info['city'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label class="form-label">State / Province *</label>
              <input type="text" name="province" class="form-control" required value="<?php echo htmlspecialchars($user_info['province'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">Postal Code</label>
              <input type="text" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($user_info['postal_code'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Phone Number *</label>
              <input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>" placeholder="example@domain.com">
          </div>

          <h2 style="margin:35px 0 25px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">2. Select Payment Method</h2>

          <div class="form-group">
            <label class="filter-checkbox" style="margin-bottom:15px; font-weight:600; cursor:pointer;">
              <input type="radio" name="payment_method" value="Cash on Delivery" checked onchange="togglePaymentMethod('cod')">
               Cash on Delivery (Pay when your plants arrive)
            </label>

            <label class="filter-checkbox" style="font-weight:600; cursor:pointer;">
              <input type="radio" name="payment_method" value="Card Payment" onchange="togglePaymentMethod('payhere')">
               Credit / Debit Card (PayHere Payment Gateway)
            </label>
          </div>

          <!-- PayHere Secured Box -->
          <div id="cardFields" style="display:none; background:var(--sage-light); padding:20px; border-radius:var(--radius-sm); margin-top:20px; border:1px solid var(--sage-accent);">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
              <div style="font-size:1.8rem; line-height:1;"></div>
              <div>
                <h4 style="margin:0; font-size:1rem; color:var(--primary-forest);">Secure Online Payment via PayHere</h4>
                <p style="margin:3px 0 0; font-size:0.85rem; color:var(--text-muted);">
                  Pay safely with Visa, MasterCard, American Express, eZ Cash or Internet Banking.
                </p>
              </div>
            </div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:12px;">
              <span style="background:var(--white); padding:5px 12px; border-radius:4px; font-weight:700; font-size:0.8rem; color:#1a1f71; border:1px solid #ddd; box-shadow:0 1px 2px rgba(0,0,0,0.05);">VISA</span>
              <span style="background:var(--white); padding:5px 12px; border-radius:4px; font-weight:700; font-size:0.8rem; color:#eb001b; border:1px solid #ddd; box-shadow:0 1px 2px rgba(0,0,0,0.05);">Mastercard</span>
              <span style="background:var(--white); padding:5px 12px; border-radius:4px; font-weight:700; font-size:0.8rem; color:#016fd0; border:1px solid #ddd; box-shadow:0 1px 2px rgba(0,0,0,0.05);">AMEX</span>
              <span style="background:var(--white); padding:5px 12px; border-radius:4px; font-weight:700; font-size:0.8rem; color:#e02424; border:1px solid #ddd; box-shadow:0 1px 2px rgba(0,0,0,0.05);">eZ Cash</span>
              <span style="background:var(--white); padding:5px 12px; border-radius:4px; font-weight:700; font-size:0.8rem; color:#ff6b00; border:1px solid #ddd; box-shadow:0 1px 2px rgba(0,0,0,0.05);">mCash</span>
            </div>
            <p style="font-size:0.82rem; color:var(--text-muted); margin:12px 0 0;">
              <?php if (PAYHERE_SANDBOX): ?>
                <span style="background:#fef3c7; color:#92400e; font-weight:700; padding:2px 8px; border-radius:4px; margin-right:6px;">Sandbox Test Mode</span>
              <?php endif; ?>
              Clicking <strong>Place Order</strong> will safely open the PayHere popup payment window.
            </p>
          </div>

        </div>

        <!-- Order Summary Sidebar -->
        <div class="cart-summary-card">
          <h3 style="margin-bottom:20px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Items in Your Order</h3>

          <div style="max-height:300px; overflow-y:auto; margin-bottom:20px;">
            <?php foreach ($cart_items as $item): 
              $img = !empty($item['variation_image']) ? $item['variation_image'] : $item['main_image'];
            ?>
              <div style="display:flex; gap:12px; align-items:center; margin-bottom:15px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
                <img src="../<?php echo htmlspecialchars($img); ?>" alt="" style="width:50px; height:50px; object-fit:contain; background:var(--sage-light); border-radius:6px;">
                <div style="flex-grow:1;">
                  <h4 style="font-size:0.95rem; margin-bottom:2px;"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                  <div style="font-size:0.8rem; color:var(--emerald);">
                    <?php echo htmlspecialchars($item['pot_type_name'] ?? 'Standard'); ?> • Qty: <?php echo $item['quantity']; ?>
                  </div>
                </div>
                <div style="font-weight:600; font-size:0.95rem;">
                  Rs. <?php echo number_format($item['price'] * $item['quantity']); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="summary-row">
            <span>Subtotal</span>
            <span>Rs. <?php echo number_format($subtotal); ?></span>
          </div>

          <div class="summary-row">
            <span>Shipping</span>
            <span><?php echo ($shipping == 0) ? 'FREE' : 'Rs. 350'; ?></span>
          </div>

          <div class="summary-row total">
            <span>Total Payable</span>
            <span>Rs. <?php echo number_format($grand_total); ?></span>
          </div>

          <button type="submit" id="placeOrderBtn" class="btn btn-emerald btn-block" style="margin-top:25px; padding:16px;">
            Place Order & Confirm ➔
          </button>
        </div>

      </div>
    </form>

  </div>
</section>

<!-- Official PayHere JS SDK -->
<script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
<script>
function togglePaymentMethod(method) {
    const cardFields = document.getElementById('cardFields');
    const submitBtn = document.getElementById('placeOrderBtn');
    if (method === 'payhere') {
        cardFields.style.display = 'block';
        submitBtn.innerHTML = 'Place Order & Pay with PayHere ➔';
    } else {
        cardFields.style.display = 'none';
        submitBtn.innerHTML = 'Place Order & Confirm ➔';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('placeOrderBtn');
    const errorBox = document.getElementById('checkoutErrorBox');

    function showError(msg) {
        if (!errorBox) return;
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // PayHere Callbacks
    payhere.onCompleted = function onCompleted(orderId) {
        console.log("PayHere payment completed for order: " + orderId);
        // Call local completion endpoint to confirm order status
        fetch('../api/payhere_complete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        }).then(function(res) {
            return res.json();
        }).then(function() {
            window.location.href = 'order-confirmation.php?id=' + orderId + '&paid=1';
        }).catch(function() {
            window.location.href = 'order-confirmation.php?id=' + orderId + '&paid=1';
        });
    };

    payhere.onDismissed = function onDismissed() {
        console.log("PayHere payment dismissed");
        showError("Payment was cancelled or dismissed. You can try again or select Cash on Delivery.");
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Place Order & Pay with PayHere ➔';
    };

    payhere.onError = function onError(error) {
        console.error("PayHere error: ", error);
        showError("PayHere Error: " + error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Place Order & Pay with PayHere ➔';
    };

    <?php if (isset($payhere_auto_start)): ?>
    payhere.startPayment(<?php echo json_encode($payhere_auto_start); ?>);
    <?php endif; ?>

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const paymentMethodInput = document.querySelector('input[name="payment_method"]:checked');
            const paymentMethod = paymentMethodInput ? paymentMethodInput.value : 'Cash on Delivery';

            if (paymentMethod === 'Card Payment') {
                e.preventDefault();

                if (!checkoutForm.checkValidity()) {
                    checkoutForm.reportValidity();
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Connecting to PayHere...';
                if (errorBox) errorBox.style.display = 'none';

                const formData = new FormData(checkoutForm);
                formData.append('ajax', '1');

                fetch('checkout.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.status === 'payhere' && data.payment) {
                        payhere.startPayment(data.payment);
                    } else if (data.status === 'error') {
                        showError(data.message || 'An error occurred while preparing your order.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Place Order & Pay with PayHere ➔';
                    } else if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    showError('Network error connecting to payment gateway. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Place Order & Pay with PayHere ➔';
                });
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
// api/payhere_notify.php - PayHere Server-to-Server IPN Listener

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/payhere_config.php';
/** @var PDO $pdo */

// Log incoming notification for traceability
$log_dir = __DIR__ . '/../logs';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0777, true);
}
$log_file = $log_dir . '/payhere_' . date('Y-m') . '.log';
$raw_input = file_get_contents('php://input');
@file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "IPN RECEIVED: " . print_r($_POST, true) . PHP_EOL . "RAW: " . $raw_input . PHP_EOL . PHP_EOL, FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

$merchant_id    = $_POST['merchant_id'] ?? '';
$order_id       = $_POST['order_id'] ?? '';
$payhere_amount = $_POST['payhere_amount'] ?? '';
$payhere_currency = $_POST['payhere_currency'] ?? '';
$status_code    = $_POST['status_code'] ?? '';
$md5sig         = $_POST['md5sig'] ?? '';
$payment_id     = $_POST['payment_id'] ?? '';

if (empty($order_id) || empty($status_code) || empty($md5sig)) {
    http_response_code(400);
    echo "Invalid IPN Payload";
    exit();
}

// Verify signature
$is_valid = payhere_verify_ipn_signature($merchant_id, $order_id, $payhere_amount, $payhere_currency, $status_code, $md5sig);

if (!$is_valid) {
    @file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "SIGNATURE VERIFICATION FAILED for Order #{$order_id}" . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo "Invalid Signature";
    exit();
}

try {
    // Fetch order to verify
    $stmt = $pdo->prepare("SELECT order_id, payment_status, order_status FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo "Order Not Found";
        exit();
    }

    if ((int)$status_code === 2) {
        // Payment successful
        $upd = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', order_status = 'Confirmed' WHERE order_id = ?");
        $upd->execute([$order_id]);
        @file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "SUCCESS: Order #{$order_id} marked as Paid (PayHere Ref: {$payment_id})" . PHP_EOL, FILE_APPEND);
    } else if (in_array((int)$status_code, [-1, -2, -3])) {
        // Payment cancelled or failed
        $upd = $pdo->prepare("UPDATE orders SET payment_status = 'Failed' WHERE order_id = ? AND payment_status != 'Paid'");
        $upd->execute([$order_id]);
        @file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "FAILED: Order #{$order_id} status code {$status_code}" . PHP_EOL, FILE_APPEND);
    }

    http_response_code(200);
    echo "OK";
} catch (Exception $e) {
    @file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "DB ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo "Server Error";
}

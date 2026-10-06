<?php
// api/payhere_complete.php - Client-side Callback Handler (Ensures local & live payment confirmation)

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/payhere_config.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;

if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing order ID.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
        exit();
    }

    // Security check: Order should belong to session user or session cart
    $current_user_id = $_SESSION['user_id'] ?? null;
    if ($current_user_id && $order['user_id'] != $current_user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized order update.']);
        exit();
    }

    // Update payment status to Paid and order to Confirmed
    $upd = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', order_status = 'Confirmed' WHERE order_id = ?");
    $upd->execute([$order_id]);

    // Clear user cart if still present
    if (isset($_SESSION['user_id'])) {
        $c_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $c_stmt->execute([$_SESSION['user_id']]);
    } else {
        $session_id = session_id();
        $c_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE session_id = ?");
        $c_stmt->execute([$session_id]);
    }
    $cart_id = $c_stmt->fetchColumn();

    if ($cart_id) {
        $del = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $del->execute([$cart_id]);
    }

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

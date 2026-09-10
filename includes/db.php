<?php
// includes/db.php - Flexible PDO Connection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/** @var PDO $pdo */
$db_host = '127.0.0.1';
$db_port = 3307; 
$db_name = 'online_indoor_plants';
$db_user = 'root';
$db_pass = '';   
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}


// Function to get active cart item count for header badge
function get_cart_count($pdo) {
    if (!$pdo) return 0;
    $cart_count = 0;
    try {
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT SUM(ci.quantity) FROM cart_items ci JOIN cart c ON ci.cart_id = c.cart_id WHERE c.user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $cart_count = $stmt->fetchColumn() ?: 0;
        } else {
            $session_id = session_id();
            $stmt = $pdo->prepare("SELECT SUM(ci.quantity) FROM cart_items ci JOIN cart c ON ci.cart_id = c.cart_id WHERE c.session_id = ?");
            $stmt->execute([$session_id]);
            $cart_count = $stmt->fetchColumn() ?: 0;
        }
    } catch (Exception $e) {
        $cart_count = 0;
    }
    return (int)$cart_count;
}

// Function to get active wishlist item count for header badge
function get_wishlist_count($pdo) {
    if (!$pdo || !isset($_SESSION['user_id'])) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return (int)($stmt->fetchColumn() ?: 0);
    } catch (Exception $e) {
        return 0;
    }
}

// Function to associate guest session cart to authenticated user after Fgin/registration
function associate_session_cart_to_user($pdo, $user_id) {
    if (!$pdo || !$user_id) return;
    $session_id = session_id();

    try {
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$session_id]);
        $guest_cart_id = $stmt->fetchColumn();

        if ($guest_cart_id) {
            $u_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
            $u_stmt->execute([$user_id]);
            $user_cart_id = $u_stmt->fetchColumn();

            if ($user_cart_id) {
                $items = $pdo->prepare("SELECT * FROM cart_items WHERE cart_id = ?");
                $items->execute([$guest_cart_id]);
                $guest_items = $items->fetchAll();

                foreach ($guest_items as $gi) {
                    if ($gi['variation_id']) {
                        $chk = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variation_id = ?");
                        $chk->execute([$user_cart_id, $gi['product_id'], $gi['variation_id']]);
                    } else {
                        $chk = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variation_id IS NULL");
                        $chk->execute([$user_cart_id, $gi['product_id']]);
                    }
                    $exist = $chk->fetch();

                    if ($exist) {
                        $upd = $pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE cart_item_id = ?");
                        $upd->execute([$gi['quantity'], $exist['cart_item_id']]);
                    } else {
                        $ins = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, variation_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
                        $ins->execute([$user_cart_id, $gi['product_id'], $gi['variation_id'], $gi['quantity'], $gi['price']]);
                    }
                }

                $del = $pdo->prepare("DELETE FROM cart WHERE cart_id = ?");
                $del->execute([$guest_cart_id]);
            } else {
                $upd = $pdo->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE cart_id = ?");
                $upd->execute([$user_id, $guest_cart_id]);
            }
        }
    } catch (Exception $e) {
        // Silently continue if error
    }
}

?>

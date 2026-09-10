<?php
// api/search.php - Live AJAX Search Suggestions API Endpoint
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || strlen($query) < 1) {
    echo json_encode([]);
    exit();
}

$stmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.price, p.main_image, c.category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    WHERE (p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?) AND p.status = 'active'
    ORDER BY p.product_name ASC 
    LIMIT 6");

$param = "%{$query}%";
$stmt->execute([$param, $param, $param]);
$results = $stmt->fetchAll();

$output = [];
foreach ($results as $r) {
    $output[] = [
        'id' => (int)$r['product_id'],
        'name' => $r['product_name'],
        'category' => $r['category_name'],
        'price' => 'Rs. ' . number_format($r['price']),
        'image' => $r['main_image']
    ];
}

echo json_encode($output);
exit();
?>

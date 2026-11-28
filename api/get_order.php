<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';

$conn = create_mysqli_connection();

$order_id = intval($_GET['order_id'] ?? 0);
$lookup_value = trim($_GET['lookup'] ?? '');

if ($order_id <= 0 || $lookup_value === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Order ID and email or phone are required.',
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, customer_name, customer_email, customer_phone, delivery_address,
            momo_transaction_id, payment_proof_url, total_price, status, created_at
     FROM orders
     WHERE id = ?
       AND (customer_email = ? OR customer_phone = ?)
     LIMIT 1"
);
$stmt->bind_param('iss', $order_id, $lookup_value, $lookup_value);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Order not found. Check your details.']);
    $conn->close();
    exit;
}

$items_stmt = $conn->prepare(
    'SELECT product_name, price, quantity FROM order_items WHERE order_id = ?'
);
$items_stmt->bind_param('i', $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];

while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

$items_stmt->close();
$order['items'] = $items;

echo json_encode(['success' => true, 'order' => $order]);
$conn->close();

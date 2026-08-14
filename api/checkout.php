<?php
header('Content-Type: application/json');

// Force MySQL to report deep technical errors instead of crashing silently
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "gold_scents_db";

try {
    $conn = new mysqli($host, $username, $password, $dbname);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Read incoming transaction details
$inputData = json_decode(file_get_contents('php://input'), true);

$customer_name = trim($inputData['customer_name'] ?? '');
$customer_email = trim($inputData['customer_email'] ?? '');
$cart_items = $inputData['items'] ?? [];
$total_price = floatval($inputData['total_price'] ?? 0);

if (empty($customer_name) || empty($customer_email) || empty($cart_items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Customer name, email, and cart items are required to process checkout.']);
    exit;
}

// Open secure pipeline transaction
$conn->begin_transaction();

try {
    // 1. Insert master customer record
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, total_price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $customer_name, $customer_email, $total_price);
    $stmt->execute();
    $order_id = $conn->insert_id; 
    $stmt->close();

    // 2. Insert item row map matrix lines
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($cart_items as $item) {
        $p_id = intval($item['id']);
        $p_name = $item['name'];
        $p_price = floatval($item['price']);
        $p_qty = intval($item['quantity']);

        $itemStmt->bind_param("iisdi", $order_id, $p_id, $p_name, $p_price, $p_qty);
        $itemStmt->execute();
    }
    $itemStmt->close();

    // Securely lock entry items into memory
    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Throwable $e) {
    // Safely undo changes if anything fails midway
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'System Engine Error: ' . $e->getMessage()]);
}

$conn->close();
?>
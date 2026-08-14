<?php
session_start();

// Strict security authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized entry.']);
    exit;
}

$host = "127.0.0.1"; // Direct local loopback address
$username = "root";
$password = "";
$dbname = "gold_scents_db"; // Updated to match your exact database!

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

header('Content-Type: application/json');

// Fetch master order entries grouped by creation date
$ordersResult = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = [];

if ($ordersResult) {
    while ($row = $ordersResult->fetch_assoc()) {
        $order_id = $row['id'];
        
        // Sub-query compilation for product lines inside the order
        $itemsResult = $conn->query("SELECT product_name, price, quantity FROM order_items WHERE order_id = $order_id");
        $items = [];
        
        if ($itemsResult) {
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
        }
        
        $row['items'] = $items;
        $orders[] = $row;
    }
}

echo json_encode($orders);
$conn->close();
?>
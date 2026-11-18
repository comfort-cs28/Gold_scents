<?php
header('Content-Type: application/json');
require_once __DIR__ . '/auth_guard.php';
require_admin_session();

$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'gold_scents_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$orders_result = $conn->query('SELECT * FROM orders ORDER BY created_at DESC');
$orders = [];

if ($orders_result) {
    while ($row = $orders_result->fetch_assoc()) {
        $order_id = intval($row['id']);
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
        $row['items'] = $items;
        $orders[] = $row;
    }
}

echo json_encode($orders);
$conn->close();

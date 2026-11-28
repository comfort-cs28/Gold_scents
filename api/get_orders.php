<?php
header('Content-Type: application/json');
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../config/db.php';
require_admin_session();

$conn = create_mysqli_connection();

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

<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../config/db.php';
require_admin_session(true);

$conn = create_mysqli_connection();

$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['id']) ? intval($data['id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Order ID.']);
    exit;
}

try {
    $conn->begin_transaction();

    $stmt_items = $conn->prepare('DELETE FROM order_items WHERE order_id = ?');
    $stmt_items->bind_param('i', $order_id);
    $stmt_items->execute();
    $stmt_items->close();

    $stmt_order = $conn->prepare('DELETE FROM orders WHERE id = ?');
    $stmt_order->bind_param('i', $order_id);
    $stmt_order->execute();

    if ($stmt_order->affected_rows === 0) {
        throw new Exception("Order #{$order_id} not found.");
    }

    $stmt_order->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();

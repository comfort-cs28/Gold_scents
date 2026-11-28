<?php
header('Content-Type: application/json');

require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_admin_session(true);

$conn = create_mysqli_connection();

$input_data = json_decode(file_get_contents('php://input'), true);
$order_id = intval($input_data['id'] ?? 0);
$next_status = trim($input_data['status'] ?? '');

if ($order_id <= 0 || !OrderStatus::is_valid($next_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Valid order id and status are required.']);
    exit;
}

$stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->bind_param('si', $next_status, $order_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found or status unchanged.']);
} else {
    echo json_encode(['success' => true, 'status' => $next_status]);
}

$stmt->close();
$conn->close();

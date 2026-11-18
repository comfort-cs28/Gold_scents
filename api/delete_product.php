<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once __DIR__ . '/auth_guard.php';
require_admin_session(true);

$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'gold_scents_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['id']) ? intval($data['id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Product ID.']);
    exit;
}

try {
    $conn->begin_transaction();

    $stmt_items = $conn->prepare('DELETE FROM order_items WHERE product_id = ?');
    $stmt_items->bind_param('i', $product_id);
    $stmt_items->execute();
    $stmt_items->close();

    $stmt_product = $conn->prepare('DELETE FROM products WHERE id = ?');
    $stmt_product->bind_param('i', $product_id);
    $stmt_product->execute();

    if ($stmt_product->affected_rows === 0) {
        throw new Exception("Product ID #{$product_id} not found.");
    }

    $stmt_product->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();

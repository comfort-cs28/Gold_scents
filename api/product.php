<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../db_connect.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'No product ID provided.']);
    exit;
}

$product_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare(
        "SELECT id, products_name, notes, price, image_url, stock_quantity
         FROM products
         WHERE id = ?"
    );
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        http_response_code(200);
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Product not found.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

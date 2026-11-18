<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../db_connect.php';

try {
    $stmt = $pdo->query(
        "SELECT id, products_name, notes, price, image_url, stock_quantity
         FROM products
         ORDER BY products_name ASC"
    );
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($products ?: []);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

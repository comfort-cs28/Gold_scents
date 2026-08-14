<?php
// 1. Set headers to output JSON and allow cross-origin requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// 2. Bring in your database connection (which is up one folder)
require_once '../db_connect.php';

try {
    // 3. Query the database to get all perfumes
    $stmt = $pdo->query("SELECT id, products_name, notes, price, image_url FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Return them as JSON
    if($products) {
        http_response_code(200);
        echo json_encode($products);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'No products found.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
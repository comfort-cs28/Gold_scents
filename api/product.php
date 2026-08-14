<?php
// 1. Set headers for JSON output and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// 2. Bring in the database connection
require_once '../db_connect.php';

// 3. Check if an 'id' was passed in the URL
if (isset($_GET['id'])) {
    // Sanitize the input to ensure it's an integer
    $id = intval($_GET['id']);
    
    try {
        // 4. Prepare and execute the query safely to prevent SQL injection
        $stmt = $pdo->prepare("SELECT id, products_name, notes, price, image_url FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 5. Return the product if found, or an error if not
        if($product) {
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
    
} else {
    // 6. Handle the case where no ID was provided
    http_response_code(400); // 400 Bad Request
    echo json_encode(['message' => 'No product ID provided. Please include an ?id= parameter.']);
}
?>
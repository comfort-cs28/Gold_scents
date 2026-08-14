<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']);
    exit;
}

$host = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "gold_scents_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Order ID received.']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Delete order items first
    $stmt1 = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    if ($stmt1) {
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();
    }

    // 2. Delete the master order record
    $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
    if (!$stmt2) {
        throw new Exception("Database SQL Error: " . $conn->error);
    }

    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    
    if ($stmt2->affected_rows === 0) {
        throw new Exception("Order #{$id} could not be found or was already deleted.");
    }
    $stmt2->close();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
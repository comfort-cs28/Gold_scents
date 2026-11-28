<?php
header('Content-Type: application/json');

require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_admin_session(true);

$conn = create_mysqli_connection();

$product_name = trim($_POST['products_name'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$stock_quantity = intval($_POST['stock_quantity'] ?? AppConfig::DEFAULT_STOCK);

if ($product_name === '' || $price <= 0) {
    echo json_encode(['success' => false, 'error' => 'Name and a valid price are required.']);
    exit;
}

if ($stock_quantity < 0) {
    $stock_quantity = 0;
}

$final_image_path = '';

if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Please attach a valid product image.']);
    exit;
}

$file_tmp_path = $_FILES['image_file']['tmp_name'];
$file_name = $_FILES['image_file']['name'];
$file_parts = explode('.', $file_name);
$file_extension = strtolower(end($file_parts));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!in_array($file_extension, $allowed_extensions, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid image type.']);
    exit;
}

$new_file_name = md5(time() . $file_name) . '.' . $file_extension;
$upload_dir = '../uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$dest_path = $upload_dir . $new_file_name;

if (!move_uploaded_file($file_tmp_path, $dest_path)) {
    echo json_encode(['success' => false, 'error' => 'Could not save uploaded image.']);
    exit;
}

$final_image_path = 'uploads/' . $new_file_name;

$stmt = $conn->prepare(
    "INSERT INTO products (products_name, notes, price, image_url, stock_quantity)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param('ssdsi', $product_name, $notes, $price, $final_image_path, $stock_quantity);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database insert failed.']);
}

$stmt->close();
$conn->close();

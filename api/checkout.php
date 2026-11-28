<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';

$conn = create_mysqli_connection();

$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
$is_multipart = stripos($content_type, 'multipart/form-data') !== false;

if ($is_multipart) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $momo_transaction_id = trim($_POST['momo_transaction_id'] ?? '');
    $total_price = floatval($_POST['total_price'] ?? 0);
    $cart_items = json_decode($_POST['items'] ?? '[]', true) ?: [];
} else {
    $input_data = json_decode(file_get_contents('php://input'), true) ?: [];
    $customer_name = trim($input_data['customer_name'] ?? '');
    $customer_email = trim($input_data['customer_email'] ?? '');
    $customer_phone = trim($input_data['customer_phone'] ?? '');
    $delivery_address = trim($input_data['delivery_address'] ?? '');
    $momo_transaction_id = trim($input_data['momo_transaction_id'] ?? '');
    $total_price = floatval($input_data['total_price'] ?? 0);
    $cart_items = $input_data['items'] ?? [];
}

$order_status = OrderStatus::PENDING;
$payment_proof_url = '';

$missing_fields = $customer_name === ''
    || $customer_email === ''
    || $customer_phone === ''
    || $delivery_address === ''
    || $momo_transaction_id === ''
    || empty($cart_items);

if ($missing_fields) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Name, email, phone, address, MoMo ID, and cart items are required.',
    ]);
    exit;
}

if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
    $file_name = $_FILES['payment_proof']['name'];
    $file_parts = explode('.', $file_name);
    $file_extension = strtolower(end($file_parts));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!in_array($file_extension, $allowed_extensions, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Payment proof must be an image.']);
        exit;
    }

    $upload_dir = '../uploads/payment_proofs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $new_file_name = 'proof_' . md5(time() . $file_name) . '.' . $file_extension;
    $dest_path = $upload_dir . $new_file_name;

    if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $dest_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not save payment proof.']);
        exit;
    }

    $payment_proof_url = 'uploads/payment_proofs/' . $new_file_name;
}

$conn->begin_transaction();

try {
    foreach ($cart_items as $item) {
        $product_id = intval($item['id']);
        $requested_qty = intval($item['quantity']);

        $stock_stmt = $conn->prepare(
            'SELECT products_name, price, stock_quantity FROM products WHERE id = ? FOR UPDATE'
        );
        $stock_stmt->bind_param('i', $product_id);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        $product_row = $stock_result->fetch_assoc();
        $stock_stmt->close();

        if (!$product_row) {
            throw new Exception('A product in your cart no longer exists.');
        }

        if (intval($product_row['stock_quantity']) < $requested_qty) {
            throw new Exception(
                $product_row['products_name'] . ' only has '
                . $product_row['stock_quantity'] . ' left in stock.'
            );
        }
    }

    $order_stmt = $conn->prepare(
        "INSERT INTO orders
         (customer_name, customer_email, customer_phone, delivery_address,
          momo_transaction_id, payment_proof_url, total_price, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $order_stmt->bind_param(
        'ssssssds',
        $customer_name,
        $customer_email,
        $customer_phone,
        $delivery_address,
        $momo_transaction_id,
        $payment_proof_url,
        $total_price,
        $order_status
    );
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    $order_stmt->close();

    $item_stmt = $conn->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stock_update_stmt = $conn->prepare(
        'UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?'
    );

    foreach ($cart_items as $item) {
        $product_id = intval($item['id']);
        $product_name = $item['name'];
        $product_price = floatval($item['price']);
        $product_qty = intval($item['quantity']);

        $item_stmt->bind_param(
            'iisdi',
            $order_id,
            $product_id,
            $product_name,
            $product_price,
            $product_qty
        );
        $item_stmt->execute();

        $stock_update_stmt->bind_param('ii', $product_qty, $product_id);
        $stock_update_stmt->execute();
    }

    $item_stmt->close();
    $stock_update_stmt->close();
    $conn->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'status' => $order_status,
        'payment_proof_url' => $payment_proof_url,
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();

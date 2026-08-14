<?php
header('Content-Type: application/json');

$host = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "gold_scents_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// 1. Capture text parameters
$name = $_POST['products_name'] ?? '';
$notes = $_POST['notes'] ?? '';
$price = $_POST['price'] ?? 0;

if (empty($name) || empty($price)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields (Name or Price).']);
    exit;
}

// 2. Process Binary Image File Safe-Handling Module
$final_image_path = "";

if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image_file']['tmp_name'];
    $fileName = $_FILES['image_file']['name'];
    $fileSize = $_FILES['image_file']['size'];
    $fileType = $_FILES['image_file']['type'];
    
    // Extract file extension cleanly
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    // Safe validation whitelist
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    if (in_array($fileExtension, $allowedExtensions)) {
        // Generate completely unique filename token hash to prevent collisions
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        
        // Define directory root path target (relative to api folder, moving up one step)
        $uploadFileDir = '../uploads/';
        
        // Dynamically create the directory on the hard drive if missing
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        $dest_path = $uploadFileDir . $newFileName;
        
        // Move file out of temporary server memory space into target system folder
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Path saved inside the database needs to point perfectly relative to index.html!
            $final_image_path = 'uploads/' . $newFileName;
        } else {
            echo json_encode(['success' => false, 'error' => 'Server permissions error transferring file to storage layout destination directory.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file extension formatting type. Allowed types: JPG, JPEG, PNG, WEBP, GIF.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Please select and attach a valid product image asset file.']);
    exit;
}

// 3. Commit complete array records into MySQL
$stmt = $conn->prepare("INSERT INTO products (products_name, notes, price, image_url) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssds", $name, $notes, $price, $final_image_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database SQL registration failure: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
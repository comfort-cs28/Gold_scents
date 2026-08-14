
<?php
header('Content-Type: application/json');

$host = "localhost";
$username = "root";
$password = "";

// Connect to MySQL WITHOUT choosing a database yet
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Ask MySQL for a list of all database names it knows about
$result = $conn->query("SHOW DATABASES");
$databases = [];

while ($row = $result->fetch_array()) {
    // Wrap names in brackets [] so we can spot hidden spaces!
    $databases[] = "[" . $row[0] . "]"; 
}

echo json_encode([
    'message' => 'PHP is connected to MySQL! Here are the databases it can see:',
    'visible_databases' => $databases
]);

$conn->close();
?>
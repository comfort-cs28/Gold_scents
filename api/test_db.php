<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';

try {
    $connection = new mysqli(DbConfig::HOST, DbConfig::USER, DbConfig::PASS);
    if ($connection->connect_error) {
        echo json_encode([
            'success' => false,
            'error' => 'Connection failed: ' . $connection->connect_error,
        ]);
        exit;
    }

    $result = $connection->query('SHOW DATABASES');
    $databases = [];
    while ($row = $result->fetch_array()) {
        $databases[] = $row[0];
    }

    echo json_encode([
        'success' => true,
        'host' => DbConfig::HOST,
        'configured_database' => DbConfig::NAME,
        'visible_databases' => $databases,
    ]);
    $connection->close();
} catch (Throwable $error) {
    echo json_encode([
        'success' => false,
        'error' => $error->getMessage(),
    ]);
}

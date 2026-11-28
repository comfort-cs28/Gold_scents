<?php

require_once __DIR__ . '/db_config.php';

function create_mysqli_connection(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $connection = new mysqli(
            DbConfig::HOST,
            DbConfig::USER,
            DbConfig::PASS,
            DbConfig::NAME
        );
        $connection->set_charset(DbConfig::CHARSET);
        return $connection;
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
        exit;
    }
}

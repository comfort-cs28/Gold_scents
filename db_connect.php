<?php

require_once __DIR__ . '/config/db_config.php';

$host = DbConfig::HOST;
$dbname = DbConfig::NAME;
$username = DbConfig::USER;
$password = DbConfig::PASS;

try {
    $pdo = new PDO(
        'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=' . DbConfig::CHARSET,
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

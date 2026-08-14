<?php
$host = 'localhost';
$dbname = 'gold_scents_db';
$username = 'root'; // Default username for XAMPP/MAMP/WAMP
$password = '';     // Default password is usually empty 

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO error mode to exception so we can catch errors easily
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
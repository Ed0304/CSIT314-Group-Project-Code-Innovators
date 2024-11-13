<?php
$host = 'localhost'; // or the IP address
$dbname = 'csit314';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>

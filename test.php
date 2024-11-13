<?php
$host = '3.25.175.57:3306'; // or the IP address
$dbname = 'csit314';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=csit314;unix_socket=/var/lib/mysql/mysql.sock", $username, $password);
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>

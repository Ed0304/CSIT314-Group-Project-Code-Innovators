<?php
require "connectDatabase.php";

$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo "Connection successful!";
    $db->close($conn);
} else {
    echo "Connection failed.";
}
?>
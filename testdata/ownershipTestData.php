<?php
// Database connection
$pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');

// Base insert statements
$insertQueries = [
    "INSERT INTO ownership(seller_id, listing_id) VALUES(4, 1);",
    "INSERT INTO ownership(seller_id, listing_id) VALUES(4, 2);",
    "INSERT INTO ownership(seller_id, listing_id) VALUES(4, 3);"
];

// Start from the next listing_id and continue adding entries
$listingId = 4;
$sellerId = 4;

// Generate 97 more entries
for ($i = 0; $i < 97; $i++) {
    // Increment sellerId by 4 each time, ensuring it stays in the range 4, 8, ..., 100
    $sellerId += 4;

    // If sellerId exceeds 100, reset it back to 4
    if ($sellerId > 100) {
        $sellerId = 4;
    }

    // If listingId exceeds 100, reset it back to 1
    if ($listingId > 100) {
        $listingId = 1;
    }

    // Append the query with the current sellerId and listingId
    $insertQueries[] = "INSERT INTO ownership(seller_id, listing_id) VALUES($sellerId, $listingId);";
    
    // Increment listingId for the next iteration
    $listingId++;
}

// Execute each query
foreach ($insertQueries as $query) {
    try {
        $pdo->exec($query);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
    }
}

echo "Insertions completed!";
?>

<?php
// Database connection
$host = 'localhost';
$dbname = 'csit314';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Define constants
    $shortlistCount = 100;  // Updated to generate 100 entries
    $startDate = new DateTime('2023-01-01');
    $endDate = new DateTime('2024-01-01');

    // Fetch buyer IDs with user_id % 3 == 0
    $buyersQuery = $pdo->query("SELECT user_id FROM users WHERE user_id % 3 = 0");
    $buyerIds = $buyersQuery->fetchAll(PDO::FETCH_COLUMN);

    // Fetch available listing IDs
    $listingsQuery = $pdo->query("SELECT listing_id FROM listing");
    $listingIds = $listingsQuery->fetchAll(PDO::FETCH_COLUMN);

    if (empty($buyerIds) || empty($listingIds)) {
        throw new Exception("No valid buyer or listing IDs found.");
    }

    // Function to generate random dates
    function randomDate($startDate, $endDate) {
        $timestamp = mt_rand($startDate->getTimestamp(), $endDate->getTimestamp());
        return date("Y-m-d", $timestamp);
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare("INSERT INTO shortlist (shortlist_id, buyer_id, listing_id, shortlist_date) 
                           VALUES (:shortlist_id, :buyer_id, :listing_id, :shortlist_date)");

    // Insert 100 shortlist entries
    for ($i = 1; $i <= $shortlistCount; $i++) {
        $shortlistId = $i;
        $buyerId = $buyerIds[array_rand($buyerIds)];
        $listingId = $listingIds[array_rand($listingIds)];
        $shortlistDate = randomDate($startDate, $endDate);

        // Bind parameters and execute
        $stmt->execute([
            ':shortlist_id' => $shortlistId,
            ':buyer_id' => $buyerId,
            ':listing_id' => $listingId,
            ':shortlist_date' => $shortlistDate
        ]);
    }

    echo "100 test shortlist entries inserted successfully.";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

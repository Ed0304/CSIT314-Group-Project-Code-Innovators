<?php
// Database connection
$host = 'mariadb';
$dbname = 'csit314';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Define constants
    $reviewCount = 100;
    $maxStars = 5;
    $startDate = new DateTime('2023-01-01');
    $endDate = new DateTime('2024-01-01');
    $agentIds = [];
    $reviewerIds = [];

    // Generate agent_ids that satisfy the conditions (even but not divisible by 4)
    for ($i = 2; $i < 100; $i++) {
        if ($i % 2 == 0 && $i % 4 != 0) {
            $agentIds[] = $i;
        }
    }

    // Fetch user IDs from the users table that satisfy the conditions for reviewer_id
    $userQuery = $pdo->query("SELECT user_id FROM users WHERE user_id % 3 = 0 OR user_id % 4 = 0");
    $reviewerIds = $userQuery->fetchAll(PDO::FETCH_COLUMN);

    if (empty($reviewerIds)) {
        throw new Exception("No valid reviewer IDs found in the users table.");
    }

    // Function to generate random dates
    function randomDate($startDate, $endDate) {
        $timestamp = mt_rand($startDate->getTimestamp(), $endDate->getTimestamp());
        return date("Y-m-d", $timestamp);
    }

    // Define review descriptions based on star rating
    $reviewDescriptions = [
        1 => "Very disappointing service.",
        2 => "Could be better.",
        3 => "Average service.",
        4 => "Good experience.",
        5 => "Excellent service."
    ];

    // Prepare the SQL statement
    $stmt = $pdo->prepare("INSERT INTO review (review_id, review_details, review_stars, reviewer_id, agent_id, review_date) 
                           VALUES (:review_id, :review_details, :review_stars, :reviewer_id, :agent_id, :review_date)");

    // Insert 100 reviews
    for ($i = 1; $i <= $reviewCount; $i++) {
        $reviewId = $i;
        $stars = rand(1, $maxStars);  // Random stars between 1 and maxStars
        $reviewDetails = $reviewDescriptions[$stars]; // Get review details based on the stars
        $reviewerId = $reviewerIds[array_rand($reviewerIds)];
        $agentId = $agentIds[array_rand($agentIds)];
        $date = randomDate($startDate, $endDate);

        // Bind parameters and execute
        $stmt->execute([
            ':review_id' => $reviewId,
            ':review_details' => $reviewDetails,
            ':review_stars' => $stars,
            ':reviewer_id' => $reviewerId,
            ':agent_id' => $agentId,
            ':review_date' => $date
        ]);
    }

    echo "100 test reviews inserted successfully.";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

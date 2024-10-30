<?php
// myListings.php
require 'connectDatabase.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once 'connectDatabase.php'; // Contains the Database class

// CarListing Entity
class CarListing {
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;

    public function __construct($listing_id, $manufacturer_name, $model_name, $model_year) {
        $this->listing_id = $listing_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->model_name = $model_name;
        $this->model_year = $model_year;
    }
}

// Controller for retrieving listings
class ListingController {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function getListingsByUsername($username) {
        $query = "SELECT listing_id, manufacturer_name, model_name, model_year FROM listing
                  WHERE user_id = (SELECT user_id FROM users WHERE username = ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = new CarListing($row['listing_id'], $row['manufacturer_name'], $row['model_name'], $row['model_year']);
        }

        $stmt->close();
        return $listings;
    }
}

// Main script logic
$username = $_SESSION['username'];
$db = new Database(); // Assuming connectDatabase.php defines Database class
$controller = new ListingController($db->getConnection());
$listings = $controller->getListingsByUsername($username);
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Listings</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid black; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2><?php echo htmlspecialchars($username); ?>'s Car Listings</h2>
    <table>
        <tr>
            <th>Manufacturer</th>
            <th>Model</th>
            <th>Year</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($listings as $listing): ?>
            <tr>
                <td style="text-align:center"><?php echo htmlspecialchars($listing->manufacturer_name); ?></td>
                <td style="text-align:center"><?php echo htmlspecialchars($listing->model_name); ?></td>
                <td style="text-align:center"><?php echo htmlspecialchars($listing->model_year); ?></td>
                <td style="text-align:center">
                    <form action="listing_details.php" method="get">
                     <!-- Add hidden input to pass listing_id -->
                    <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                    <button type="submit">View more details</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br/>
    <form method="post" action="agent_dashboard.php" style="text-align:center">
        <input type="submit" value="Return" style="font-size: 24px">
    </form>
</body>
</html>

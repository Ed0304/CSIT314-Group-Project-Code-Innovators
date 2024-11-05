<?php
require "connectDatabase.php";
// Start the session
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Agent Entity
class Agent {
    public $agent_id;
    public $username;
    public $listings; // Array to hold listing details
    private $db; // Database connection

    public function __construct($dbConnection, $agent_id, $username) {
        $this->db = $dbConnection;
        $this->agent_id = $agent_id;
        $this->username = $username;
        $this->listings = []; // Initialize as an empty array
        $this->loadListings(); // Load listings upon agent creation
    }

    // Method to load listings from the database
    private function loadListings() {
        $query = "SELECT listing_id, manufacturer_name, model_name, model_year FROM listing WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $this->agent_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Directly add listing details as an associative array
            $this->listings[] = [
                'listing_id' => $row['listing_id'],
                'manufacturer_name' => $row['manufacturer_name'],
                'model_name' => $row['model_name'],
                'model_year' => $row['model_year'],
            ];
        }

        $stmt->close();
    }
}

// Controller for managing the agent
class ListingController {
    private $agent;

    public function __construct($dbConnection, $username) {
        // Fetch agent information
        $agentQuery = "SELECT user_id FROM users WHERE username = ?";
        $stmt = $dbConnection->prepare($agentQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($agent_id);
        $stmt->fetch();
        $stmt->close();

        // Create the agent object
        $this->agent = new Agent($dbConnection, $agent_id, $username);
    }

    public function getAgent() {
        return $this->agent;
    }
}

// Boundary class for displaying listings
class CarListingBoundary {
    public function displayListings(ListingController $controller) {
        $agent = $controller->getAgent();
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
            <h2><?php echo htmlspecialchars($agent->username); ?>'s Car Listings</h2>
            <br/><br/>
            <form method="get" action="">
                <input type="text" style="font-size:24px" name="search" placeholder="Search listings...">
                <button type="submit" style="font-size:24px">Search</button>
            </form>
            <br/><br/>
            <form method="post" action="agent_create_listings.php">
                <button type="submit" id="create" name="create" style="font-size:24px">Create new listings</button>
            </form>
            <br/><br/>
            <table>
                <tr>
                    <th>Manufacturer</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($agent->listings as $listing): ?>
                    <tr>
                        <td style="text-align:center"><?php echo htmlspecialchars($listing['manufacturer_name']); ?></td>
                        <td style="text-align:center"><?php echo htmlspecialchars($listing['model_name']); ?></td>
                        <td style="text-align:center"><?php echo htmlspecialchars($listing['model_year']); ?></td>
                        <td style="text-align:center">
                            <form action="listing_details.php" method="get" style="display:inline;">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                <button type="submit">View more details</button>
                            </form>
                            <form action="update_listing_details.php" method="get" style="display:inline;">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                <button type="submit">Update Listing</button>
                            </form>
                            <form action="agent_delete_listing.php" method="get" style="display:inline;">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                <button type="submit">Delete Listing</button>
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
        <?php
    }
}

// Main script logic
$db = new Database(); // Assuming connectDatabase.php defines Database class
$controller = new ListingController($db->getConnection(), $_SESSION['username']);

// Create a boundary instance and display the listings
$boundary = new CarListingBoundary();
$boundary->displayListings($controller);
?>

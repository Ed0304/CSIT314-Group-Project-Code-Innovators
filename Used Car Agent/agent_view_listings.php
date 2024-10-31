<?php
require "connectDatabase.php";
// Start the session
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['create'])) {
    header("Location: agent_create_listings.php");
    exit();
}

if (isset($_POST['view'])) {
    $username = urlencode($this->view->listing_id); // Encode the username
    header("Location: listing_details.php?listing_id=" . $listing_id);
    exit();
}


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
    private $username;
    private $listings;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->username = $_SESSION['username'];
        $this->listings = $this->getListingsByUsername($this->username);
    }

    private function getListingsByUsername($username) {
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

    public function getUsername() {
        return $this->username;
    }

    public function getListings() {
        return $this->listings;
    }
}

// Boundary class for displaying listings
class CarListingBoundary {
    public function displayListings(ListingController $controller) {
        $username = $controller->getUsername();
        $listings = $controller->getListings();
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
            <br/><br/>
            <br/>
            <form>
                <input type="text" style="font-size:24px" name="search" placeholder="Search listings...">
                <button type="submit" style="font-size:24px">Search</button>
            </form>
            <br/><br/>
            <form method="post" action="agent_create_listings.php">
                <button type="submit" id="create" name="create" style="font-size:24px">Create new listings</button>
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->username); ?>" />
            </form>
            <br/><br/>
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
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit">View more details</button>
                            </form>
                            <form action="update_listing_details.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit">Update Listing</button>
                            </form>
                            <form action="agent_delete_listing.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
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
$controller = new ListingController($db->getConnection());

// Create a boundary instance and display the listings
$boundary = new CarListingBoundary();
$boundary->displayListings($controller);
?>

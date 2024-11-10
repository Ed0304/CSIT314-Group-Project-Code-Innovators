<?php
require "../connectDatabase.php";
// Start the session
session_start();

// CarListing Entity
class CarListing
{
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    private $db;

    // Constructor
    public function __construct($dbConnection, $listing_id = null, $manufacturer_name = null, $model_name = null, $model_year = null)
    {
        $this->db = $dbConnection;
        $this->listing_id = $listing_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->model_name = $model_name;
        $this->model_year = $model_year;
    }

    // Method to get all listings for a user
    public function getListingsByUsername($username)
    {
        $query = "SELECT listing_id, manufacturer_name, model_name, model_year FROM listing
                  WHERE user_id = (SELECT user_id FROM users WHERE username = ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = new CarListing($this->db, $row['listing_id'], $row['manufacturer_name'], $row['model_name'], $row['model_year']);
        }
        $stmt->close();
        return $listings;
    }

    // Method to search listings based on a filter (manufacturer, model, year)
    public function searchCarListing($username, $role, $search)
    {
        $query = "SELECT listing_id, manufacturer_name, model_name, model_year FROM listing
                  WHERE user_id = (SELECT user_id FROM users WHERE username = ?)
                  AND $role LIKE ?";
        $search = "%$search%";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $username, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = new CarListing($this->db, $row['listing_id'], $row['manufacturer_name'], $row['model_name'], $row['model_year']);
        }
        $stmt->close();
        return $listings;
    }
}


// Controller for retrieving listings
class SearchCarListingController
{
    private $db;
    private $username;
    public $listings;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
        $this->username = $_SESSION['username'];
        $carListing = new CarListing($this->db);  // Instantiate the CarListing class
        $this->listings = $carListing->getListingsByUsername($this->username); // Use CarListing to fetch listings
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getListings()
    {
        return $this->listings;
    }

    public function searchCarListing($role, $search)
    {
        $carListing = new CarListing($this->db);  // Instantiate the CarListing class
        return $carListing->searchCarListing($this->username, $role, $search); // Use CarListing to search listings
    }
}

// Boundary class for displaying listings
// Boundary class for displaying listings and handling form logic
class SearchCarListingPage
{
    public function handleFormSubmission()
    {
        // Handle search button submission
        if (isset($_POST['searchButton'])) {
            $role = strtolower($_POST['vehicle']);
            $search = $_POST['search'];
            return $this->searchCarListing($role, $search);
        }

        // Check if the user is logged in
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        // Handle create listing button submission
        if (isset($_POST['create'])) {
            header("Location: agent_create_listings.php");
            exit();
        }

        // Handle view listing button submission
        if (isset($_POST['view'])) {
            $username = urlencode($this->view->listing_id); // Encode the listing ID
            header("Location: listing_details.php?listing_id=" . $listing_id);
            exit();
        }

        return null;
    }

    // Method for performing the search
    private function searchCarListing($role, $search)
    {
        global $controller;
        $controller->listings = $controller->searchCarListing($role, $search);
    }

    public function displayListings(SearchCarListingController $controller)
    {
        $username = $controller->getUsername();
        $listings = $controller->getListings();
        ?>
        <!DOCTYPE HTML>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <title>My Listings</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                th,
                td {
                    padding: 10px;
                    border: 1px solid black;
                }

                th {
                    background-color: #f2f2f2;
                }
            </style>
        </head>

        <body>
            <h2><?php echo htmlspecialchars($username); ?>'s Car Listings</h2>
            <br /><br />
            <br />

            <!-- Form for filtering users based on manufacturer, model, year -->
            <form method="POST" action="agent_view_listings.php">
                <label for="vehicle" class="select-label" style="font-size: 24px">Filter based on:</label>
                <select id="vehicle" name="vehicle" class="select-label" style="font-size: 24px">
                    <option value="manufacturer_name" class="select-label" style="font-size: 24px">Manufacturer</option>
                    <option value="model_name" class="select-label" style="font-size: 24px">Model</option>
                    <option value="model_year" class="select-label" style="font-size: 24px">Year</option>
                </select>
                <input type="text" id="search" name="search" placeholder="Enter Text Here" style="font-size: 22px" />
                <button type="submit" name="searchButton" id="searchButton" style="font-size: 24px">Search</button>
                <br /><br />
            </form>
            <!-- Form ends here-->

            <form method="post" action="agent_create_listings.php">
                <button type="submit" id="create" name="create" style="font-size:24px">Create new listings</button>
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->username); ?>" />
            </form>
            <br /><br />
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
            <br />
            <form method="post" action="agent_dashboard.php" style="text-align:center">
                <input type="submit" value="Return" style="font-size: 24px">
            </form>
        </body>

        </html>
        <?php
    }
}

//main script logic
$db = new Database(); // Assuming connectDatabase.php defines Database class
$controller = new SearchCarListingController($db->getConnection());

$page = new SearchCarListingPage();
$page->handleFormSubmission(); // Handle form submissions (search, create, etc.)

$boundary = new SearchCarListingPage();
$boundary->displayListings($controller);
?>
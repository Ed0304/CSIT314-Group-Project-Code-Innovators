<?php
require "../connectDatabase.php";
session_start();

// CarListing Entity (Handles database interactions)
class CarListing
{
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    private $db;

    // Constructor (handles DB connection inside the entity)
    public function __construct()
    {
        $this->db = (new Database())->getConnection();  // DB connection directly from the Database class
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
            $listing = new CarListing();
            $listing->listing_id = $row['listing_id'];
            $listing->manufacturer_name = $row['manufacturer_name'];
            $listing->model_name = $row['model_name'];
            $listing->model_year = $row['model_year'];
            $listings[] = $listing;
        }
        $stmt->close();
        return $listings;
    }

    // Method to search listings based on a filter
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
            $listing = new CarListing();
            $listing->listing_id = $row['listing_id'];
            $listing->manufacturer_name = $row['manufacturer_name'];
            $listing->model_name = $row['model_name'];
            $listing->model_year = $row['model_year'];
            $listings[] = $listing;
        }
        $stmt->close();
        return $listings;
    }
}

// Controller (Handles logic between Boundary and Entity)
class SearchCarListingController
{
    private $carListing;
    private $username;

    public function __construct()
    {
        $this->carListing = new CarListing();  // No need to pass DB connection
        $this->username = $_SESSION['username'];
    }

    public function getListings()
    {
        return $this->carListing->getListingsByUsername($this->username);
    }

    public function searchListings($role, $search)
    {
        return $this->carListing->searchCarListing($this->username, $role, $search);
    }
}

// Boundary class (Handles UI and form submission)
class SearchCarListingPage
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function handleFormSubmission()
    {
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        if (isset($_POST['searchButton'])) {
            $role = strtolower($_POST['vehicle']);
            $search = $_POST['search'];
            return $this->controller->searchListings($role, $search);
        }

        if (isset($_POST['create'])) {
            header("Location: agent_create_listings.php");
            exit();
        }

        if (isset($_POST['view'])) {
            $listing_id = $_POST['listing_id'];
            header("Location: listing_details.php?listing_id=" . urlencode($listing_id));
            exit();
        }

        return $this->controller->getListings();
    }

    public function displayListings($listings)
    {
        $username = $_SESSION['username'];
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
            <form method="POST" action="agent_view_listings.php">
                <label for="vehicle">Filter based on:</label>
                <select id="vehicle" name="vehicle">
                    <option value="manufacturer_name">Manufacturer</option>
                    <option value="model_name">Model</option>
                    <option value="model_year">Year</option>
                </select>
                <input type="text" name="search" placeholder="Enter Text Here" />
                <button type="submit" name="searchButton">Search</button>
            </form>
            <form method="post" action="agent_create_listings.php">
                <button type="submit" name="create">Create new listings</button>
            </form>
            <table>
                <tr>
                    <th>Manufacturer</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($listing->manufacturer_name); ?></td>
                        <td><?php echo htmlspecialchars($listing->model_name); ?></td>
                        <td><?php echo htmlspecialchars($listing->model_year); ?></td>
                        <td>
                            <form action="listing_details.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit">View</button>
                            </form>
                            <form action="update_listing_details.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit">Update</button>
                            </form>
                            <form action="agent_delete_listing.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </body>
        </html>
        <?php
    }
}

// Main Script
$controller = new SearchCarListingController();  // No need to pass DB connection here
$page = new SearchCarListingPage($controller);

$listings = $page->handleFormSubmission();
$page->displayListings($listings);
?>

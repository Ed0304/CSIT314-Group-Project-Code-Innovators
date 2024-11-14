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
            /* Basic Reset */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }
            
            /* Body Styling */
            body {
                background-color: #f8f9fa;
                color: #343a40;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 20px;
            }
            
            /* Page Header */
            h2 {
                margin-bottom: 20px;
                font-size: 1.8em;
                color: #007bff;
            }
            
            /* Form Styling */
            form {
                margin-bottom: 15px;
                display: inline-block;
            }

            /* Filter Form */
            .filter-form label {
                margin-right: 10px;
                font-weight: bold;
            }
            
            /* Search and Filter Input */
            select, input[type="text"] {
                padding: 8px;
                margin-right: 10px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                font-size: 1em;
            }

            /* Buttons */
            button {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                font-size: 1em;
                color: #ffffff;
                cursor: pointer;
                transition: background-color 0.3s ease;
                margin: 0 5px;
            }

            /* Specific Button Colors */
            button[name="searchButton"] {
                background-color: #007bff;
            }

            button[name="searchButton"]:hover {
                background-color: #0056b3;
            }

            button[name="create"] {
                background-color: #28a745;
            }

            button[name="create"]:hover {
                background-color: #218838;
            }

            /* Table Styling */
            table {
                width: 100%;
                border-collapse: collapse;
                background-color: white;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                margin-top: 20px;
            }

            th, td {
                padding: 12px;
                text-align: center;
                border: 1px solid #dee2e6;
            }

            th {
                background-color: #6c757d;
                color: #ffffff;
                font-weight: bold;
            }

            tr:nth-child(even) {
                background-color: #f1f1f1;
            }

            /* Action Buttons in Table */
            .action-buttons form {
                display: inline;
            }

            .action-buttons button {
                background-color: #007bff;
                color: #ffffff;
                margin: 0 3px;
                padding: 6px 12px;
            }

            .action-buttons button:hover {
                background-color: #0056b3;
            }

            .action-buttons .delete-button {
                background-color: #dc3545;
            }

            .action-buttons .delete-button:hover {
                background-color: #c82333;
            }

            .action-buttons .update-button {
                background-color: #28a745;
            }

            .action-buttons .update-button:hover {
                background-color: #218838;
            }

            /* Centering Content */
            .content {
                max-width: 1000px;
                width: 100%;
            }

            /* Return to Dashboard Button */
            .dashboard-button {
                display: block;
                margin: 20px auto;
                padding: 10px 20px;
                background-color: #6c757d;
                color: #ffffff;
                text-align: center;
                border-radius: 5px;
                font-size: 1.1em;
                width: 25%;
                text-decoration: none;
            }

            .dashboard-button:hover {
                background-color: #5a6268;
            }
        </style>
    </head>
    <body>
        <div class="content">
            <h2><?php echo htmlspecialchars($username); ?>'s Car Listings</h2>
            
            <!-- Filter Form -->
            <form method="POST" action="agent_view_listings.php" class="filter-form">
                <label for="vehicle">Filter based on:</label>
                <select id="vehicle" name="vehicle">
                    <option value="manufacturer_name">Manufacturer</option>
                    <option value="model_name">Model</option>
                    <option value="model_year">Year</option>
                </select>
                <input type="text" name="search" placeholder="Enter Text Here" />
                <button type="submit" name="searchButton">Search</button>
            </form>

            <!-- Create Listing Button -->
            <form method="post" action="agent_create_listings.php">
                <button type="submit" name="create">Create new listings</button>
            </form>

            <!-- Listings Table -->
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
                        <td class="action-buttons">
                            <form action="listing_details.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit">View</button>
                            </form>
                            <form action="update_listing_details.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit" class="update-button">Update</button>
                            </form>
                            <form action="agent_delete_listing.php" method="get">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <!-- Return to Dashboard Button -->
            <a href="agent_dashboard.php" class="dashboard-button">Return to Dashboard</a>
        </div>
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

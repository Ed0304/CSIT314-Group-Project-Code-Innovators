<?php
session_start();
require_once "../connectDatabase.php";
$user_id = $_SESSION['user_id'];

// Entity: Represents a listing
class CarListing
{
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_color;
    public $listing_price;
    public $listing_description;
    public $user_id;
    public $first_name;
    public $last_name;
    public $searchUsedCar;

    public function __construct($data, $searchUsedCar)
    {
        $this->listing_id = $data['listing_id'];
        $this->manufacturer_name = $data['manufacturer_name'];
        $this->model_name = $data['model_name'];
        $this->model_year = $data['model_year'];
        $this->listing_color = $data['listing_color'];
        $this->listing_price = $data['listing_price'];
        $this->listing_description = $data['listing_description'];
        $this->user_id = $data['user_id'];
        $this->first_name = $data['first_name'];
        $this->last_name = $data['last_name'];
        $this->searchUsedCar = $searchUsedCar;
    }
}

// Controller: Manages the interaction with the database
class SearchUsedCarController
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getBuyerID()
    {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    public function getAllListings()
    {
        $stmt = $this->conn->prepare("
            SELECT 
                l.listing_id, 
                l.manufacturer_name, 
                l.model_name, 
                l.model_year, 
                l.listing_color, 
                l.listing_price, 
                l.listing_description, 
                p.user_id, 
                p.first_name, 
                p.last_name 
            FROM 
                listing l 
            JOIN 
                profile p ON l.user_id = p.user_id
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = new CarListing($row, false); // Pass false for searchUsedCar
        }
        return $listings;
    }
    public function searchUsedCar($criteria, $search, $searchUsedCar)
    {
        $query = "
        SELECT 
            l.listing_id, 
            l.manufacturer_name, 
            l.model_name, 
            l.model_year, 
            l.listing_color, 
            l.listing_price, 
            l.listing_description, 
            p.user_id, 
            p.first_name, 
            p.last_name 
        FROM 
            listing l 
        JOIN 
            profile p ON l.user_id = p.user_id
    ";
        if ($criteria && $search) {
            $query .= " WHERE l.$criteria LIKE ? ORDER BY l.$criteria ASC";
            $stmt = $this->conn->prepare($query);
            $search = "%$search%";
            $stmt->bind_param("s", $search);
        } else if ($criteria) {
            $query .= " ORDER BY l.$criteria ASC";
            $stmt = $this->conn->prepare($query);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = new CarListing($row, $searchUsedCar); // Pass searchUsedCar boolean
        }
        return $listings;
    }
}

// Boundary: Manages the display of data
class SearchUsedCarPage
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function render()
    {
        $criteria = isset($_POST['criteria']) ? $_POST['criteria'] : null;
        $search = isset($_POST['search']) ? $_POST['search'] : null;
        $searchUsedCar = isset($_POST['searchButton']);

        if ($searchUsedCar) {
            $listings = $this->controller->searchUsedCar($criteria, $search, $searchUsedCar);
        } else {
            $listings = $this->controller->getAllListings();
        }
        $buyerID = $this->controller->getBuyerID();
        ?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Buyer Dashboard</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f9fa;
                }

                header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 20px;
                    background-color: #343a40;
                    color: #ffffff;
                }

                header h1 {
                    margin: 0;
                    font-size: 1.5em;
                }

                header a {
                    text-decoration: none;
                    color: #ffffff;
                    background-color: #007bff;
                    padding: 8px 16px;
                    border-radius: 4px;
                    font-size: 0.9em;
                }

                header a:hover {
                    background-color: #0056b3;
                }

                h2 {
                    text-align: center;
                    color: #343a40;
                    margin-top: 20px;
                }

                table {
                    width: 90%;
                    margin: 20px auto;
                    border-collapse: collapse;
                    background-color: white;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                table,
                th,
                td {
                    border: 1px solid #dee2e6;
                }

                th,
                td {
                    padding: 12px;
                    text-align: center;
                    color: #343a40;
                }

                th {
                    background-color: #6c757d;
                    color: #ffffff;
                    font-weight: bold;
                }

                tr:nth-child(even) {
                    background-color: #f1f1f1;
                }

                .shortlist-button {
                    background-color: #007bff;
                    color: white;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                }

                .shortlist-button:hover {
                    background-color: #0056b3;
                }

                .agent-button {
                    background-color: green;
                    color: white;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                }

                .agent-button:hover {
                    background-color: darkgreen;
                }

                .listing-button {
                    background-color: green;
                    color: white;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                }

                .listing-button:hover {
                    background-color: darkgreen;
                }

                .search-button {
                    background-color: #007bff;
                    color: white;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                }

                .search-button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>

        <body>
            <header>
                <h1>Welcome to the Buyer Dashboard</h1>
                <!-- New "Update Account Details" button -->
                <a href="buyer_update_profile.php?user_id=<?php echo $buyerID; ?>">Update Account Details</a>
                <a href="loanCalculator.php">Calculate Loan</a>
                <a href="buyer_view_shortlist.php?user_id=<?php echo $buyerID; ?>">View Shortlist</a>
                <a href="../logout.php">Logout</a>
            </header>
            <h2>Available Listings</h2>

            <!-- Form for filtering users based on manufacturer, model, year -->
            <form method="POST" action="buyer_dashboard.php">
                <label for="vehicle" class="select-label" style="font-size: 18px">Search based on:</label>
                <select id="vehicle" name="criteria" class="select-label" style="font-size: 18px">
                    <option value="manufacturer_name" class="select-label" style="font-size: 18px">Manufacturer</option>
                    <option value="model_name" class="select-label" style="font-size: 18px">Model</option>
                    <option value="model_year" class="select-label" style="font-size: 18px">Year</option>
                    <option value="listing_color" class="select-label" style="font-size: 18px">Color</option>
                    <option value="listing_price" class="select-label" style="font-size: 18px">Price</option>
                </select>
                <input type="text" id="search" name="search" placeholder="Enter Text Here" style="font-size: 18px" />
                <button class="search-button" type="submit" name="searchButton" id="searchButton"
                    style="font-size: 18px">Search</button>
                <br /><br />
            </form>
            <!-- Form ends here-->

            <table>
                <tr>
                    <th>Manufacturer</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Color</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Agent</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($listing->manufacturer_name); ?></td>
                        <td><?php echo htmlspecialchars($listing->model_name); ?></td>
                        <td><?php echo htmlspecialchars($listing->model_year); ?></td>
                        <td><?php echo htmlspecialchars($listing->listing_color); ?></td>
                        <td><?php echo htmlspecialchars($listing->listing_price); ?></td>
                        <td><?php echo htmlspecialchars($listing->listing_description); ?></td>
                        <td><?php echo htmlspecialchars($listing->first_name . " " . $listing->last_name); ?></td>
                        <td>
                            <form action="buyerListingDetails.php" method="post">
                                <input type="hidden" name="listing_id" value="<?php echo $listing->listing_id; ?>">
                                <input type="hidden" name="referrer" value="dashboard">
                                <button class="listing-button" id="listing-button" type="submit">View Listing Details</button>
                            </form>

                            <a href="buyer_view_agent_details.php?user_id=<?php echo $listing->user_id; ?>&referrer=dashboard">
                                <button class="agent-button" id="agent-button" type="button">View Agent Details</button>
                            </a>

                            <a href="buyer_add_shortlist.php?listing_id=<?php echo $listing->listing_id; ?>">
                                <button class="shortlist-button" id="shortlist-button" type="button">Add this listing to
                                    shortlist</button>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </body>

        </html>
        <?php
    }
}

// Main script logic
$database = new Database();
$conn = $database->getConnection();
$searchUsedCarController = new SearchUsedCarController($conn);
$searchUsedCarPage = new SearchUsedCarPage($searchUsedCarController);
$searchUsedCarPage->render();
$database->closeConnection();
?>
<?php
session_start();
require_once "connectDatabase.php";
$user_id = $_SESSION['user_id'];

// Entity: Represents a listing
class Listing {
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

    public function __construct($data) {
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
    }
}

// Controller: Manages the interaction with the database
class UserController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to get the buyer user ID from the session
    public function getBuyerID() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    public function getAllListings() {
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
            $listings[] = new Listing($row); // Create Listing objects from the results
        }
        return $listings;
    }
}

// Boundary: Manages the display of data
class UserBoundary {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function render() {
        $listings = $this->controller->getAllListings();
        $buyerID = $this->controller->getBuyerID(); // Get the buyer ID
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
                    background-color: #dc3545;
                    padding: 8px 16px;
                    border-radius: 4px;
                    font-size: 0.9em;
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
                    background-color: #ffffff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                table, th, td {
                    border: 1px solid #dee2e6;
                }
                th, td {
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
            </style>
        </head>
        <body>
            <header>
                <h1>Welcome to the Buyer Dashboard</h1>
                <a href="loanCalculator.php">Calculate Loan</a>        
                <a href="buyer_view_shortlist.php?user_id=<?php echo $buyerID; ?>">View Shortlist</a>
                <a href="logout.php">Logout</a>
            </header>
            <h2>Available Listings</h2>
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
                                <button type="submit">View Listing Details</button>
                            </form>
                            <a href="buyer_view_agent_details.php?user_id=<?php echo $listing->user_id; ?>">
                                <button type="button">View Agent Details</button>
                            </a>
                            <a href="buyer_add_shortlist.php?listing_id=<?php echo $listing->listing_id; ?>">
                                <button type="button">Add this listing to shortlist</button>
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

$userController = new UserController($conn);
$userBoundary = new UserBoundary($userController);
$userBoundary->render();

$database->closeConnection();
?>
<?php
session_start();
require_once "../connectDatabase.php";
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Entity: Represents a shortlisted car
class Shortlist {
    public $shortlist_id;
    public $listing_id;
    public $user_id; // Buyer ID
    public $date_added;
    public $manufacturer_name; // Added to store additional information
    public $model_name;
    public $model_year;
    public $listing_color;
    public $listing_price;
    public $listing_description;

    public function __construct($data) {
        $this->shortlist_id = $data['shortlist_id'] ?? null;
        $this->listing_id = $data['listing_id'] ?? null;
        $this->user_id = $data['user_id'] ?? null; // Buyer ID
        $this->date_added = $data['date_added'] ?? null; // Date added
        $this->manufacturer_name = $data['manufacturer_name'] ?? null; // Manufacturer name
        $this->model_name = $data['model_name'] ?? null; // Model name
        $this->model_year = $data['model_year'] ?? null; // Model year
        $this->listing_color = $data['listing_color'] ?? null; // Color
        $this->listing_price = $data['listing_price'] ?? null; // Price
        $this->listing_description = $data['listing_description'] ?? null; // Description
    }
}

// Controller: Manages the interaction with the database for shortlists
class ViewShortlistsController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to get the buyer user ID from the session
    public function getBuyerID() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    // Method to get all shortlists for the buyer
    public function getShortlists() {
        $buyerID = $this->getBuyerID();
        if ($buyerID === null) {
            return []; // Return an empty array if buyer ID is not set
        }
        
        $stmt = $this->conn->prepare("
            SELECT 
                s.shortlist_id, 
                s.listing_id, 
                s.buyer_id AS user_id,
                s.shortlist_date AS date_added, 
                l.manufacturer_name, 
                l.model_name, 
                l.model_year, 
                l.listing_color, 
                l.listing_price, 
                l.listing_description 
            FROM 
                shortlist s 
            JOIN 
                listing l ON s.listing_id = l.listing_id 
            WHERE 
                s.buyer_id = ?
        ");
        $stmt->bind_param("i", $buyerID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $shortlists = [];
        while ($row = $result->fetch_assoc()) {
            $shortlists[] = new Shortlist($row); // Create Shortlist objects from the results
        }
        return $shortlists;
    }
}

// Boundary: Manages the display of shortlisted cars
class ViewShortlistsBoundary {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function render() {
        $shortlists = $this->controller->getShortlists();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Your Shortlisted Cars</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f9fa;
                }
                header {
                    padding: 20px;
                    background-color: #343a40;
                    color: #ffffff;
                }
                header h1 {
                    margin: 0;
                    font-size: 1.5em;
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
                .return-button {
                    display: block;
                    width: 200px;
                    margin: 20px auto;
                    padding: 10px;
                    background-color: #007bff;
                    color: white;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                }
                .return-button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <header>
                <h1>Your Shortlisted Cars</h1>
            </header>
            <h2>Shortlisted Cars</h2>
            <table>
                <tr>
                    <th>Manufacturer</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Color</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Date Added</th>
                </tr>
                <?php foreach ($shortlists as $shortlist): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shortlist->manufacturer_name); ?></td>
                        <td><?php echo htmlspecialchars($shortlist->model_name); ?></td>
                        <td><?php echo htmlspecialchars($shortlist->model_year); ?></td>
                        <td><?php echo htmlspecialchars($shortlist->listing_color); ?></td>
                        <td><?php echo htmlspecialchars($shortlist->listing_price); ?></td>
                        <td><?php echo htmlspecialchars($shortlist->listing_description); ?></td>
                        <td><?php echo htmlspecialchars($shortlist->date_added); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <a href="buyer_dashboard.php" class="return-button">Return to Main Page</a>
        </body>
        </html>
        <?php
    }
}

// Main script logic
$database = new Database();
$conn = $database->getConnection();

$viewShortlistsController = new ViewShortlistsController($conn);
$viewShortlistsBoundary = new ViewShortlistsBoundary($viewShortlistsController);
$viewShortlistsBoundary->render();

$database->closeConnection();
?>

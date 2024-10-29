<?php
// listingDetails.php

require_once 'connectDatabase.php'; // Contains the Database class
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['listing_id'])) {
    echo "Listing ID not provided!";
    exit();
}

// ENTITY LAYER: Represents the CarListing entity
class CarListing {
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_image; // Base64-encoded image data
    public $listing_color;
    public $listing_price;
    public $listing_description;

    public function __construct($listing_id, $manufacturer_name, $model_name, $model_year, $listing_image, $listing_color, $listing_price, $listing_description) {
        $this->listing_id = $listing_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->model_name = $model_name;
        $this->model_year = $model_year;
        $this->listing_image = $listing_image; // Store the image data
        $this->listing_color = $listing_color;
        $this->listing_price = $listing_price;
        $this->listing_description = $listing_description;
    }
}
// CONTROL LAYER: Responsible for data retrieval
class ListingController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getListingDetails($listing_id) {
        $stmt = $this->db->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null; // No listing found
        }

        $row = $result->fetch_assoc();

        // Convert the BLOB image data to Base64
        $imageData = base64_encode($row['listing_image']); // Assuming 'listing_image' is the BLOB column

        return new CarListing(
            $row['listing_id'],
            $row['manufacturer_name'],
            $row['model_name'],
            $row['model_year'],
            $imageData, // Use the Base64 encoded image data
            $row['listing_color'],
            $row['listing_price'],
            $row['listing_description']
        );
    }
}


// BOUNDARY LAYER: Generates HTML for displaying the listing
class ListingView {
    private $listing;

    public function __construct($listing) {
        $this->listing = $listing;
    }

    public function render() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Listing Details</title>
            <style>
                .details-container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .details-container img { 
                    max-width: 100%; /* Makes the image responsive */
                    height: auto; /* Maintains the aspect ratio */
                    border: 2px solid #ccc; /* Adds a border around the image */
                    border-radius: 5px; /* Rounds the corners of the border */
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adds a shadow effect */
                }
                .details-container h2 { text-align: center; }
                .details-container table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .details-container th, .details-container td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                .details-container th { background-color: #f2f2f2; }
                .return-button { text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="details-container">
                <h2>Car Listing Details</h2>
                
                <table>
                    <tr>
                        <th>Image</th>
                        <td>
                            <?php if (!empty($this->listing->listing_image)): ?>
                                <img src="data:image/jpeg;base64,<?php echo htmlspecialchars($this->listing->$listing_image); ?>" alt="Car Picture" />
                            <?php else: ?>
                                <p>No image available.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th>Manufacturer</th><td><?php echo htmlspecialchars($this->listing->manufacturer_name); ?></td></tr>
                    <tr><th>Model</th><td><?php echo htmlspecialchars($this->listing->model_name); ?></td></tr>
                    <tr><th>Year</th><td><?php echo htmlspecialchars($this->listing->model_year); ?></td></tr>
                    <tr><th>Color</th><td><?php echo htmlspecialchars($this->listing->listing_color); ?></td></tr>
                    <tr><th>Price</th><td><?php echo "$" . number_format($this->listing->listing_price, 2); ?></td></tr>
                    <tr><th>Description</th><td><?php echo htmlspecialchars($this->listing->listing_description); ?></td></tr>
                </table>
    
                <div class="return-button">
                    <a href="agent_view_listings.php">
                        <button type="button">Return to My Listings</button>
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
}

// MAIN LOGIC: Initializes classes and renders listing
$listing_id = $_GET['listing_id'];
$db = new Database(); // Ensure your Database class properly implements the connection
$controller = new ListingController($db->getConnection());
$listing = $controller->getListingDetails($listing_id);

if ($listing !== null) {
    $view = new ListingView($listing);
    $view->render();
} else {
    echo "Listing not found.";
}
?>

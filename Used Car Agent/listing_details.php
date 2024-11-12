<?php
// listingDetails.php

require_once '../connectDatabase.php'; // Contains the Database class
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['listing_id'])) {
    echo "Listing ID not provided!";
    exit();
}

// ENTITY LAYER: Manages CarListing data and database interactions
class CarListing {
    private $db;
    private $listing_id;
    private $manufacturer_name;
    private $model_name;
    private $model_year;
    private $listing_image;
    private $listing_color;
    private $listing_price;
    private $listing_description;

    public function __construct($listing_id = null, $manufacturer_name = null, $model_name = null, $model_year = null, $listing_image = null, $listing_color = null, $listing_price = null, $listing_description = null) {
        $this->db = new Database();
        $this->listing_id = $listing_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->model_name = $model_name;
        $this->model_year = $model_year;
        $this->listing_image = $listing_image;
        $this->listing_color = $listing_color;
        $this->listing_price = $listing_price;
        $this->listing_description = $listing_description;
    }

    // Getter methods for each property
    public function getListingId() { return $this->listing_id; }
    public function getManufacturerName() { return $this->manufacturer_name; }
    public function getModelName() { return $this->model_name; }
    public function getModelYear() { return $this->model_year; }
    public function getListingImage() { return !empty($this->listing_image) ? base64_encode($this->listing_image) : null; }
    public function getListingColor() { return $this->listing_color; }
    public function getListingPrice() { return $this->listing_price; }
    public function getListingDescription() { return $this->listing_description; }

    // Fetch listing details by listing_id from the database and return a CarListing object
    public function viewCarListing($listing_id) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        $carListing = new CarListing(
            $row['listing_id'],
            $row['manufacturer_name'],
            $row['model_name'],
            $row['model_year'],
            $row['listing_image'],
            $row['listing_color'],
            $row['listing_price'],
            $row['listing_description']
        );

        return $carListing;
    }
}


// CONTROL LAYER: Manages application logic and retrieves data from the Entity
class ViewCarListingController {
    private $carListing;

    public function __construct() {
        $this->carListing = new CarListing();
    }

    public function viewCarListing($listing_id) {
        return $this->carListing->viewCarListing($listing_id);
    }
}

// BOUNDARY LAYER: Manages user interface, handles input/output and displays the listing
class ViewCarListingPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function ViewCarListing() {
        $listing_id = $_GET['listing_id'];
        $listing = $this->controller->viewCarListing($listing_id);

        if ($listing === null) {
            echo "Listing not found.";
            return;
        }

        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Listing Details</title>
            <style>
                .details-container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .details-container img { max-width: 100%; height: auto; border: 2px solid #ccc; border-radius: 5px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
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
                            <?php if (!empty($listing->getListingImage())): ?>
                                <img src="data:image/jpeg;base64,<?php echo $listing->getListingImage(); ?>" alt="Car Picture" />
                            <?php else: ?>
                                <p>No image available.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th>Manufacturer</th><td><?php echo htmlspecialchars($listing->getManufacturerName()); ?></td></tr>
                    <tr><th>Model</th><td><?php echo htmlspecialchars($listing->getModelName()); ?></td></tr>
                    <tr><th>Year</th><td><?php echo htmlspecialchars($listing->getModelYear()); ?></td></tr>
                    <tr><th>Color</th><td><?php echo htmlspecialchars($listing->getListingColor()); ?></td></tr>
                    <tr><th>Price</th><td><?php echo "$" . number_format($listing->getListingPrice(), 2); ?></td></tr>
                    <tr><th>Description</th><td><?php echo htmlspecialchars($listing->getListingDescription()); ?></td></tr>
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


// MAIN LOGIC: Initializes the controller and boundary to render the listing page
$controller = new ViewCarListingController();
$view = new ViewCarListingPage($controller);
$view->ViewCarListing();
?>

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
class CarListingEntity {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Fetch listing details by listing_id from the database
    public function fetchListingDetails($listing_id) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();

        return [
            'listing_id' => $row['listing_id'],
            'manufacturer_name' => $row['manufacturer_name'],
            'model_name' => $row['model_name'],
            'model_year' => $row['model_year'],
            'listing_image' => $this->prepareImageData($row['listing_image']),
            'listing_color' => $row['listing_color'],
            'listing_price' => $row['listing_price'],
            'listing_description' => $row['listing_description'],
            'mime_type' => $this->detectMimeType($row['listing_image'])
        ];
    }

    private function prepareImageData($image) {
        return !empty($image) ? base64_encode($image) : null;
    }

    private function detectMimeType($imageData) {
        return 'image/jpeg'; // Static MIME type for simplicity; adjust as needed.
    }
}

// CONTROL LAYER: Manages application logic and retrieves data from the Entity
class CarListingController {
    private $carListingEntity;

    public function __construct() {
        $this->carListingEntity = new CarListingEntity();
    }

    public function getListingDetails($listing_id) {
        return $this->carListingEntity->fetchListingDetails($listing_id);
    }
}

// BOUNDARY LAYER: Manages user interface, handles input/output and displays the listing
class CarListingPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function render() {
        $listing_id = $_GET['listing_id'];
        $listing = $this->controller->getListingDetails($listing_id);

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
                            <?php if (!empty($listing['listing_image'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo $listing['listing_image']; ?>" alt="Car Picture" />
                            <?php else: ?>
                                <p>No image available.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th>Manufacturer</th><td><?php echo htmlspecialchars($listing['manufacturer_name']); ?></td></tr>
                    <tr><th>Model</th><td><?php echo htmlspecialchars($listing['model_name']); ?></td></tr>
                    <tr><th>Year</th><td><?php echo htmlspecialchars($listing['model_year']); ?></td></tr>
                    <tr><th>Color</th><td><?php echo htmlspecialchars($listing['listing_color']); ?></td></tr>
                    <tr><th>Price</th><td><?php echo "$" . number_format($listing['listing_price'], 2); ?></td></tr>
                    <tr><th>Description</th><td><?php echo htmlspecialchars($listing['listing_description']); ?></td></tr>
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
$controller = new CarListingController();
$view = new CarListingPage($controller);
$view->render();
?>

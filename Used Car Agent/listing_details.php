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

// ENTITY LAYER: Represents the CarListing entity and now also fetches data from the database
class CarListing {
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_image; // Image data (could be path or Base64)
    public $listing_color;
    public $listing_price;
    public $listing_description;
    public $mime_type; // Property for MIME type
    private $db;

    public function __construct($db) {
        $this->db = $db; // Store database connection
    }

    // Fetch listing details by listing_id from the database
    public function fetchListingDetails($listing_id) {
        $stmt = $this->db->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null; // No listing found
        }
        
        $row = $result->fetch_assoc();
        
        // Initialize variables for image data and type
        $imageData = null;
        $mimeType = null;

        // Determine if listing_image is a BLOB or a file path
        if (!empty($row['listing_image'])) {
            if ($this->is_blob($row['listing_image'])) {
                // BLOB image data handling
                $mimeType = 'image/jpeg'; // Adjust based on your application's logic
                $imageData = base64_encode($row['listing_image']); // Encode BLOB data to Base64
            } else {
                // File path handling
                $imageData = htmlspecialchars($row['listing_image']); // Use file path directly
                $mimeType = $this->getMimeType($imageData); // Get the MIME type from the file path
            }
        }

        // Initialize and return the CarListing object
        $this->listing_id = $row['listing_id'];
        $this->manufacturer_name = $row['manufacturer_name'];
        $this->model_name = $row['model_name'];
        $this->model_year = $row['model_year'];
        $this->listing_image = $imageData;
        $this->listing_color = $row['listing_color'];
        $this->listing_price = $row['listing_price'];
        $this->listing_description = $row['listing_description'];
        $this->mime_type = $mimeType;

        return $this;
    }

    private function is_blob($image) {
        // Implement logic to check if $image is BLOB data
        return is_string($image) && strlen($image) > 0; // Basic example; modify as needed for your case
    }

    private function getMimeType($filePath) {
        // Check if the file exists and then get the MIME type
        if (file_exists($filePath)) {
            $mimeType = mime_content_type($filePath); // Try to get MIME type
            if ($mimeType) {
                return $mimeType; // Return found MIME type
            }
        }

        // Fallback: Set default MIME type based on file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return 'application/octet-stream'; // Default for unknown types
        }
    }
}

// CONTROL LAYER: Responsible for interacting with the CarListing entity
class CarListingController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getListingDetails($listing_id) {
        $carListing = new CarListing($this->db); // Instantiate CarListing with DB connection
        return $carListing->fetchListingDetails($listing_id); // Fetch listing details
    }
}


// BOUNDARY LAYER: Generates HTML for displaying the listing
class CarListingPage {
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
                                <img src="<?php echo (strpos($this->listing->listing_image, 'data:') === 0) ? $this->listing->listing_image : 'data:' . $this->listing->mime_type . ';base64,' . $this->listing->listing_image; ?>" alt="Car Picture" />
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
$controller = new CarListingController($db->getConnection());
$listing = $controller->getListingDetails($listing_id);

if ($listing !== null) {
    $view = new CarListingPage($listing);
    $view->render();
} else {
    echo "Listing not found.";
}
?>

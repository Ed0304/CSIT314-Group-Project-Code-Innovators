<?php
// Start session and include the database connection
session_start();
require_once "../connectDatabase.php";

// Redirect if listing_id is not provided
if (!isset($_POST['listing_id'])) {
    header("Location: buyer_dashboard.php");
    exit();
}

$listing_id = $_POST['listing_id'];

// Entity: CarListing
class CarListing {
    public $manufacturerName;
    public $modelName;
    public $modelYear;
    public $listingColor;
    public $listingPrice;
    public $listingDescription;
    public $agentFirstName;
    public $agentLastName;
    public $listingImage;

    public function __construct($data) {
        $this->manufacturerName = $data['manufacturer_name'];
        $this->modelName = $data['model_name'];
        $this->modelYear = $data['model_year'];
        $this->listingColor = $data['listing_color'];
        $this->listingPrice = $data['listing_price'];
        $this->listingDescription = $data['listing_description'];
        $this->agentFirstName = $data['first_name'];
        $this->agentLastName = $data['last_name'];
        $this->listingImage = $data['listing_image'];
    }
}

// Control: ListingDetailsController
class ListingDetailsController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getListingDetails($listing_id) {
        // Increment view count before retrieving the details
        $this->incrementViews($listing_id);

        // Prepare and execute the SQL statement to get listing details
        $stmt = $this->conn->prepare("
            SELECT 
                l.manufacturer_name, 
                l.model_name, 
                l.model_year, 
                l.listing_color, 
                l.listing_price, 
                l.listing_description, 
                p.first_name, 
                p.last_name,
                l.listing_image 
            FROM 
                listing l 
            JOIN 
                profile p ON l.user_id = p.user_id 
            WHERE 
                l.listing_id = ?
        ");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data ? new CarListing($data) : null;
    }

    private function incrementViews($listing_id) {
        // Update the views count by 1 for the specified listing_id
        $stmt = $this->conn->prepare("UPDATE listing SET views = views + 1 WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
    }
}

// Boundary: ListingDetailsView
class ListingDetailsView {
    public function render($listing) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Listing Details</title>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
                .container { max-width: 800px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
                h1 { color: #0066cc; }
                .details { margin-top: 20px; }
                .details p { margin: 10px 0; font-size: 1.1em; }
                .details p strong { color: #555; }
                .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; transition: background-color 0.3s ease; }
                .back-link:hover { background-color: #004a99; }
                .listing-image { margin-top: 20px; max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Listing Details</h1>
                <?php if ($listing): ?>
                    <div class="details">
                        <?php if ($listing->listingImage): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($listing->listingImage); ?>" class="listing-image" alt="Listing Image">
                        <?php endif; ?>
                        <p><strong>Manufacturer:</strong> <?php echo htmlspecialchars($listing->manufacturerName); ?></p>
                        <p><strong>Model:</strong> <?php echo htmlspecialchars($listing->modelName); ?></p>
                        <p><strong>Year:</strong> <?php echo htmlspecialchars($listing->modelYear); ?></p>
                        <p><strong>Color:</strong> <?php echo htmlspecialchars($listing->listingColor); ?></p>
                        <p><strong>Price:</strong> <?php echo htmlspecialchars($listing->listingPrice); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($listing->listingDescription); ?></p>
                        <p><strong>Agent:</strong> <?php echo htmlspecialchars($listing->agentFirstName . " " . $listing->agentLastName); ?></p>
                        <a href="buyer_dashboard.php" class="back-link">Back to Dashboard</a>
                    </div>
                <?php else: ?>
                    <p>Listing not found.</p>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }
}

// Initialize database connection, controller, and retrieve listing details
$database = new Database();
$conn = $database->getConnection();
$controller = new ListingDetailsController($conn);
$listing = $controller->getListingDetails($listing_id);
$database->closeConnection();

// Render the view
$view = new ListingDetailsView();
$view->render($listing);
?>

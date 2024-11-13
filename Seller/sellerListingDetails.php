<?php
session_start();
require_once "../connectDatabase.php";

// CarListing Entity: Handles all operations related to a single listing
class CarListing {
    private $database;

    public $manufacturerName;
    public $modelName;
    public $modelYear;
    public $color;
    public $price;
    public $description;
    public $agentFirstName;
    public $agentLastName;
    public $image;
    public $views;

    public function __construct($database) {
        $this->database = $database;
    }

    public function ViewUsedCarListing($listing_id) {
        $conn = $this->database->getConnection();

        $stmt = $conn->prepare("
            SELECT 
                l.manufacturer_name, 
                l.model_name, 
                l.model_year, 
                l.listing_color, 
                l.listing_price, 
                l.listing_description, 
                l.views,
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
        $data = $stmt->get_result()->fetch_assoc();

        if ($data) {
            $this->manufacturerName = $data['manufacturer_name'];
            $this->modelName = $data['model_name'];
            $this->modelYear = $data['model_year'];
            $this->color = $data['listing_color'];
            $this->price = $data['listing_price'];
            $this->description = $data['listing_description'];
            $this->agentFirstName = $data['first_name'];
            $this->agentLastName = $data['last_name'];
            $this->image = $data['listing_image'];
            $this->views = $data['views'];
        }
        $stmt->close();

        return $this; // Return the CarListing object
    }

}

// ViewUsedCarListingController: Coordinates actions for a Listing
class ViewUsedCarListingController {
    private $listing;

    public function __construct($listing) {
        $this->listing = $listing;
    }

    public function ViewUsedCarListing($listing_id) {
        return $this->listing->ViewUsedCarListing($listing_id); // Get the populated Listing object
    }
}

// ViewUsedCarListingPage: Renders the listing details
class ViewUsedCarListingPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function handleRequest() {
        if (!isset($_POST['listing_id'])) {
            header("Location: seller_dashboard.php");
            exit();
        }
        return $_POST['listing_id'];
    }

    public function ViewUsedCarListingUI() {
        // Handle request and get listing id
        $listing_id = $_POST['listing_id'] ?? $_GET['listing_id'] ?? null; //retrieves listing_id from previous screen
        $listingDetails = $this->controller->ViewUsedCarListing($listing_id);

        // Render the listing details view
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Listing Details</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                    color: #333;
                }

                .container {
                    max-width: 400px;
                    margin: 50px auto;
                    padding: 20px;
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }

                h1 {
                    color: #0066cc;
                    text-align: center;
                }

                .details {
                    margin-top: 20px;
                }

                .details p {
                    margin: 10px 0;
                    font-size: 1.1em;
                }

                .details p strong {
                    color: #555;
                }

                .back-link {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 15px;
                    background-color: #0066cc;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 4px;
                    transition: background-color 0.3s ease;
                }

                .back-link:hover {
                    background-color: #004a99;
                }

                .listing-image {
                    margin-top: 20px;
                    width: 400px;
                    /* Set fixed width */
                    height: 300px;
                    /* Set fixed height */
                    object-fit: cover;
                    /* Maintain aspect ratio */
                    border-radius: 4px;
                    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
                }
            </style>
        </head>

        <body>
            <div class="container">
                <h1>Listing Details</h1>
                <h2><?php echo $listingDetails->views; ?> people has viewed this listing</h2>
                <?php if ($listingDetails): ?>
                    <div class="details">
                        <?php if ($listingDetails->image): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($listingDetails->image); ?>" class="listing-image" alt="Listing Image">
                        <?php endif; ?>
                        <p><strong>Manufacturer:</strong> <?php echo htmlspecialchars($listingDetails->manufacturerName); ?></p>
                        <p><strong>Model:</strong> <?php echo htmlspecialchars($listingDetails->modelName); ?></p>
                        <p><strong>Year:</strong> <?php echo htmlspecialchars($listingDetails->modelYear); ?></p>
                        <p><strong>Color:</strong> <?php echo htmlspecialchars($listingDetails->color); ?></p>
                        <p><strong>Price:</strong> <?php echo htmlspecialchars($listingDetails->price); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($listingDetails->description); ?></p>
                        <p><strong>Agent:</strong> <?php echo htmlspecialchars($listingDetails->agentFirstName . " " . $listingDetails->agentLastName); ?></p>
                        <?php
                        $referrer = $_GET['referrer'] ?? $_POST['referrer'] ?? 'dashboard';
                        ?>
                        <a href="<?php echo 'seller_view_listings.php'; ?>" class="back-link">Back to <?php echo ucfirst($referrer); ?></a>
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

// Initialize and use the classes
$database = new Database();
$listing = new CarListing($database);
$controller = new ViewUsedCarListingController($listing);
$view = new ViewUsedCarListingPage($controller);

// Render the listing details
$view->ViewUsedCarListingUI();

$database->closeConnection();
?>

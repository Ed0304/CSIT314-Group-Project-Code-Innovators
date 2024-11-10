<?php
// Start session and include the database connection
session_start();
require_once "../connectDatabase.php";

if (!isset($_POST['listing_id'])) {
    header("Location: buyerDashboard.php");
    exit();
}

$listing_id = $_POST['listing_id'];

// Entity: CarListing
// Entity: CarListing
class CarListing
{
    private $conn;

    public $manufacturerName;
    public $modelName;
    public $modelYear;
    public $listingColor;
    public $listingPrice;
    public $listingDescription;
    public $agentFirstName;
    public $agentLastName;
    public $listingImage;

    public function __construct($conn, $listing_id)
    {
        $this->conn = $conn;
        $this->loadListing($listing_id);
    }

    // Fetch listing details from the database
    private function loadListing($listing_id)
    {
        // Increment the views when the listing is accessed
        $this->incrementViews($listing_id);

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

        if ($data) {
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

    // Increment view count for the listing
    private function incrementViews($listing_id)
    {
        $query = "UPDATE listing SET views = views + 1 WHERE listing_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $stmt->close();
    }
}


// Control: ListingDetailsController
class ListingDetailsController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Fetch the listing details by calling the CarListing class
    public function getListing($listing_id)
    {
        $carListing = new CarListing($this->conn, $listing_id);
        return $carListing;
    }
}

// Boundary: ListingDetailsView
class ListingDetailsView
{
    public function handleRequest()
    {
        if (!isset($_POST['listing_id'])) {
            header("Location: buyerDashboard.php");
            exit();
        }

        return $_POST['listing_id'];
    }

    public function render($listing)
    {
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
                <?php if ($listing): ?>
                    <div class="details">
                        <?php if ($listing->listingImage): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($listing->listingImage); ?>" class="listing-image"
                                alt="Listing Image">
                        <?php endif; ?>
                        <p><strong>Manufacturer:</strong> <?php echo htmlspecialchars($listing->manufacturerName); ?></p>
                        <p><strong>Model:</strong> <?php echo htmlspecialchars($listing->modelName); ?></p>
                        <p><strong>Year:</strong> <?php echo htmlspecialchars($listing->modelYear); ?></p>
                        <p><strong>Color:</strong> <?php echo htmlspecialchars($listing->listingColor); ?></p>
                        <p><strong>Price:</strong> <?php echo htmlspecialchars($listing->listingPrice); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($listing->listingDescription); ?></p>
                        <p><strong>Agent:</strong>
                            <?php echo htmlspecialchars($listing->agentFirstName . " " . $listing->agentLastName); ?></p>
                        <br />
                        <br />
                        <?php
                        $referrer = isset($_GET['referrer']) ? $_GET['referrer'] : (isset($_POST['referrer']) ? $_POST['referrer'] : 'dashboard');
                        ?>
                        <a href="<?php echo $referrer === 'shortlist' ? 'buyer_view_shortlist.php' : 'buyer_dashboard.php'; ?>"
                            class="back-link">Back to <?php echo ucfirst($referrer); ?></a>
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

// Initialize the database connection and retrieve listing details
$database = new Database();
$conn = $database->getConnection();

// Create the view and handle the request
$view = new ListingDetailsView();
$listing_id = $view->handleRequest();  // This now handles the redirection if necessary

// Create the controller and fetch the listing details
$controller = new ListingDetailsController($conn);
$listing = $controller->getListing($listing_id);

// Close the database connection
$database->closeConnection();

// Render the view
$view->render($listing);
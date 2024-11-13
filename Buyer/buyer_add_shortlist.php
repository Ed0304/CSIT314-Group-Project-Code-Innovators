<?php
session_start();
require_once "../connectDatabase.php";
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Entity: Represents a listing and manages listing-related queries
class Listing
{
    private $conn;

    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_color;
    public $listing_price;
    public $listing_description;

    public function __construct($conn, $data = null)
    {
        $this->conn = $conn;
        if ($data) {
            $this->listing_id = $data['listing_id'] ?? null;
            $this->manufacturer_name = $data['manufacturer_name'] ?? null;
            $this->model_name = $data['model_name'] ?? null;
            $this->model_year = $data['model_year'] ?? null;
            $this->listing_color = $data['listing_color'] ?? null;
            $this->listing_price = $data['listing_price'] ?? null;
            $this->listing_description = $data['listing_description'] ?? null;
        }
    }

    public function fetchById($listingId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listingId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return new Listing($this->conn, $row);
        }
        return null;
    }

    public function isDuplicateShortlist($listingId, $buyerId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM shortlist WHERE listing_id = ? AND buyer_id = ?");
        $stmt->bind_param("ii", $listingId, $buyerId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return $count > 0;
    }

    public function addtoShortlist($listingId, $buyerId)
    {
        $stmt = $this->conn->prepare("INSERT INTO shortlist (listing_id, buyer_id, shortlist_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $listingId, $buyerId);
        return $stmt->execute();
    }
}

// Controller: Manages the interaction for adding to shortlist
class AddToShortlistController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getListingDetails($listingId)
    {
        $listing = new Listing($this->conn);
        return $listing->fetchById($listingId);
    }

    public function addtoShortlist($listingId, $buyerId)
    {
        $listing = new Listing($this->conn);
        if ($listing->isDuplicateShortlist($listingId, $buyerId)) {
            return false; // Prevent addition if it's a duplicate
        }
        return $listing->addtoShortlist($listingId, $buyerId);
    }

    public function getBuyerID()
    {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
}

// Boundary: Manages the display and handling of the confirmation screen
// Boundary: Manages the display and handling of the confirmation screen
class AddToShortlistPage
{
    private $controller;
    private $added = false; // Flag for successful addition
    private $duplicate = false; // Flag for duplicate detection
    private $listing = null; // Holds listing details

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function handleRequest()
    {
        $listingId = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
        $buyerId = $this->controller->getBuyerID();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'])) {
            if ($this->controller->addtoShortlist($listingId, $buyerId)) {
                $this->added = true; // Set flag to true if added successfully
            } else {
                $this->duplicate = true; // Set flag if a duplicate is detected
            }
        }

        // Fetch listing details and store in an instance variable
        $this->listing = $this->controller->getListingDetails($listingId);

        // Render the UI
        $this->AddToShortlistUI();
    }

    public function AddToShortlistUI()
    {
        if ($this->listing === null) {
            echo "Listing not found.";
            return;
        }
        ?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Confirm Add to Shortlist</title>
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

                .confirmation-container {
                    width: 90%;
                    max-width: 600px;
                    margin: 50px auto;
                    padding: 20px;
                    background-color: #ffffff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    margin: 10px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }

                .button:hover {
                    background-color: #0056b3;
                }

                .message {
                    color: green;
                    font-weight: bold;
                }

                .error {
                    color: red;
                    font-weight: bold;
                }
            </style>
        </head>

        <body>
            <header>
                <h1>Confirm Add to Shortlist</h1>
            </header>
            <div class="confirmation-container">
                <?php if ($this->added): ?>
                    <p class="message">Listing added to your shortlist successfully!</p>
                <?php elseif ($this->duplicate): ?>
                    <p class="error">This listing is already in your shortlist.</p>
                <?php endif; ?>
                <h2>Are you sure you want to add this listing to your shortlist?</h2>
                <p><strong>Manufacturer:</strong> <?php echo htmlspecialchars($this->listing->manufacturer_name); ?></p>
                <p><strong>Model:</strong> <?php echo htmlspecialchars($this->listing->model_name); ?></p>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($this->listing->model_year); ?></p>
                <p><strong>Color:</strong> <?php echo htmlspecialchars($this->listing->listing_color); ?></p>
                <p><strong>Price:</strong> <?php echo htmlspecialchars($this->listing->listing_price); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($this->listing->listing_description); ?></p>

                <form method="post">
                    <input type="hidden" name="listing_id" value="<?php echo $this->listing->listing_id; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $this->controller->getBuyerID(); ?>">
                    <button type="submit" class="button">Yes, Add to Shortlist</button>
                </form>
                <a href="buyer_dashboard.php" class="button">Cancel</a>
            </div>
        </body>

        </html>
        <?php
    }
}


// Main script logic
$database = new Database();
$conn = $database->getConnection();

$controller = new AddToShortlistController($conn);
$boundary = new AddToShortlistPage($controller);

// Process the request
$boundary->handleRequest();

$database->closeConnection();
?>

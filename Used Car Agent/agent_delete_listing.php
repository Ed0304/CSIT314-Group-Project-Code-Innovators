<?php
// listingDetails.php

require_once '../connectDatabase.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ENTITY LAYER: Defines the CarListing entity
class CarListing {
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_image;
    public $listing_color;
    public $listing_price;
    public $listing_description;

    public function __construct($data) {
        $this->listing_id = $data['listing_id'];
        $this->manufacturer_name = $data['manufacturer_name'];
        $this->model_name = $data['model_name'];
        $this->model_year = $data['model_year'];
        $this->listing_image = $data['listing_image'];
        $this->listing_color = $data['listing_color'];
        $this->listing_price = $data['listing_price'];
        $this->listing_description = $data['listing_description'];
        // Removed mime_type
    }
}

// CONTROL LAYER: Manages business logic
class DeleteCarListingController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Retrieves listing details by ID
    public function getListingDetails($listing_id) {
        $stmt = $this->db->prepare("SELECT listing_id, manufacturer_name, model_name, model_year, listing_image, listing_color, listing_price, listing_description FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $data = $result->fetch_assoc();
        $data['listing_image'] = $this->formatImage($data['listing_image']);

        return new CarListing($data);
    }

    // Deletes listing by ID
    public function deleteListing($listing_id) {
        $stmt = $this->db->prepare("DELETE FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        return $stmt->execute();
    }

    // Determines if image is a blob and formats it as base64 if necessary
    private function formatImage($image) {
        return !empty($image) ? 'data:image/jpeg;base64,' . base64_encode($image) : null;
    }
}

// BOUNDARY LAYER: Handles rendering
class DeleteCarListingPage {
    private $controller;
    private $listing;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    // Renders the confirmation page for deletion
    public function renderConfirmation($listing_id) {
        $this->listing = $this->controller->getListingDetails($listing_id);

        if (!$this->listing) {
            echo "Listing not found.";
            return;
        }

        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Delete Listing</title>
            <style>
                .details-container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .details-container img { max-width: 100%; height: auto; border: 2px solid #ccc; border-radius: 5px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
                .details-container h2 { text-align: center; }
                .details-container table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .details-container th, .details-container td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                .details-container th { background-color: #f2f2f2; }
                .button-container { text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="details-container">
                <h2>Are you sure you want to delete this listing?</h2>                
                <table>
                    <tr>
                        <th>Image</th>
                        <td>
                            <?php if ($this->listing->listing_image): ?>
                                <img src="<?php echo $this->listing->listing_image; ?>" alt="Car Picture" />
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

                <div class="button-container">
                    <form action="" method="post">
                        <input type="hidden" name="listing_id" value="<?php echo $this->listing->listing_id; ?>" />
                        <button type="submit" name="confirm_delete">Delete</button>
                        <a href="agent_view_listings.php"><button type="button">Cancel</button></a>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}

// MAIN LOGIC: Setup and render view or process deletion
$db = new Database();
$controller = new DeleteCarListingController($db->getConnection());
$view = new DeleteCarListingPage($controller);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $listing_id = $_POST['listing_id'];

    if ($controller->deleteListing($listing_id)) {
        header("Location: agent_view_listings.php?message=Listing deleted successfully");
        exit();
    } else {
        echo "Failed to delete listing.";
    }
} else {
    $listing_id = $_GET['listing_id'] ?? null;
    if ($listing_id) {
        $view->renderConfirmation($listing_id);
    } else {
        echo "Listing ID not provided!";
    }
}
?>

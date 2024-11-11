<?php
require "../connectDatabase.php";

// ENTITY LAYER
class CarListing {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findById($listing_id) {
        $stmt = $this->db->prepare("SELECT listing_id, manufacturer_name, model_name, model_year, listing_image, listing_color, listing_price, listing_description FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }
        $data = $result->fetch_assoc();
        $data['listing_image'] = $this->formatImage($data['listing_image']);
        return $data;
    }

    public function deleteById($listing_id) {
        // Begin transaction
        $this->db->begin_transaction();

        try {
            // Delete from ownership table
            $stmt = $this->db->prepare("DELETE FROM ownership WHERE listing_id = ?");
            $stmt->bind_param("i", $listing_id);
            $stmt->execute();

            //Delete from all shortlist
            $stmt = $this->db->prepare("DELETE FROM shortlist WHERE listing_id = ?");
            $stmt->bind_param("i", $listing_id);
            $stmt->execute();

            // Delete from listing table
            $stmt = $this->db->prepare("DELETE FROM listing WHERE listing_id = ?");
            $stmt->bind_param("i", $listing_id);
            $stmt->execute();

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on failure
            $this->db->rollback();
            return false;
        }
    }

    private function formatImage($image) {
        return !empty($image) ? 'data:image/jpeg;base64,' . base64_encode($image) : null;
    }
}

// CONTROLLER LAYER
class DeleteCarListingController {
    private $carListing;

    public function __construct($carListing) {
        $this->carListing = $carListing;
    }

    public function getListingDetails() {
        $listing_id = $_GET['listing_id'] ?? null;
        if ($listing_id) {
            return $this->carListing->findById($listing_id);
        }
        return null;
    }

    public function deleteCarListing($listing_id) {
        return $this->carListing->deleteById($listing_id);
    }
}

// BOUNDARY LAYER
class DeleteCarListingPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            $this->processDeleteRequest($_POST['listing_id']);
        } else {
            $listing = $this->controller->getListingDetails();
            if ($listing) {
                $this->DeleteCarListingUI($listing);
            } else {
                echo "Listing not found or ID not provided!";
            }
        }
    }

    private function processDeleteRequest($listing_id) {
        if ($this->controller->deleteCarListing($listing_id)) {
            header("Location: agent_view_listings.php?message=Listing deleted successfully");
            exit();
        } else {
            echo "Failed to delete listing.";
        }
    }

    public function DeleteCarListingUI($listing) {
        // Render HTML with listing details
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Delete Listing</title>
            <!-- Styles omitted for brevity -->
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
                            <?php if ($listing['listing_image']): ?>
                                <img src="<?php echo $listing['listing_image']; ?>" alt="Car Picture" />
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

                <div class="button-container">
                    <form action="" method="post">
                        <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>" />
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

// MAIN EXECUTION LOGIC
$db = new Database();
$carListingEntity = new CarListing($db->getConnection());
$controller = new DeleteCarListingController($carListingEntity);
$view = new DeleteCarListingPage($controller);
$view->handleRequest();


?>
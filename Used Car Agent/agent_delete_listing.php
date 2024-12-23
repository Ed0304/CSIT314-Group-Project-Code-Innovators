<?php
require "../connectDatabase.php";
// BOUNDARY LAYER
class DeleteCarListingPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function handleRequest() {
        // Check if listing_id is provided via GET or session
        $listing_id = $_GET['listing_id'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            // Ensure the POST value is correctly passed
            $listing_id = $_POST['listing_id'] ?? null;
            $this->processDeleteRequest($listing_id);
        } elseif ($listing_id) {
            $listing = $this->controller->getListingDetails($listing_id);
            if ($listing) {
                $this->DeleteCarListingUI($listing);
            } else {
                echo "Listing not found!";
            }
        } else {
            echo "No listing ID provided!";
        }
    }

    private function processDeleteRequest($listing_id) {
        if ($listing_id && $this->controller->deleteCarListing($listing_id)) {
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
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f9fa;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }

                .details-container {
                    background-color: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                    max-width: 600px;
                    padding: 20px;
                    width: 100%;
                }

                .details-container h2 {
                    text-align: center;
                    color: #dc3545;
                    margin-bottom: 20px;
                }

                .details-container img {
                    max-width: 100%;
                    height: auto;
                    border: 2px solid #ccc;
                    border-radius: 5px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .details-container table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    color: #343a40;
                }

                .details-container th,
                .details-container td {
                    padding: 10px;
                    border: 1px solid #ddd;
                    text-align: left;
                }

                .details-container th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    color: #495057;
                }

                .button-container {
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                    margin-top: 20px;
                }

                .button-container button,
                .button-container a button {
                    background-color: #dc3545;
                    color: #fff;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 1em;
                }

                .button-container a button {
                    background-color: #6c757d;
                }

                .button-container button:hover {
                    background-color: #c82333;
                }

                .button-container a button:hover {
                    background-color: #5a6268;
                }
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
                    </form>
                    <a href="agent_view_listings.php">
                        <button type="button">Cancel</button>
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}

// CONTROLLER LAYER
class DeleteCarListingController {
    private $carListing;

    public function __construct($carListing) {
        $this->carListing = $carListing;
    }

    public function getListingDetails($listing_id) {
        return $this->carListing->findById($listing_id);
    }

    public function deleteCarListing($listing_id) {
        return $this->carListing->deleteCarListing($listing_id);
    }
}

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

    public function deleteCarListing($listing_id) {
        $this->db->begin_transaction();

        $stmt = $this->db->prepare("DELETE FROM ownership WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();

        $stmt = $this->db->prepare("DELETE FROM shortlist WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();

        $stmt = $this->db->prepare("DELETE FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();

        $this->db->commit();
        return true;
    }

    private function formatImage($image) {
        return !empty($image) ? 'data:image/jpeg;base64,' . base64_encode($image) : null;
    }
}

// MAIN EXECUTION LOGIC
$db = new Database();
$carListingEntity = new CarListing($db->getConnection());
$controller = new DeleteCarListingController($carListingEntity);
$view = new DeleteCarListingPage($controller);
$view->handleRequest();
?>
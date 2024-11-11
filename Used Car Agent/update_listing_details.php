<?php
require '../connectDatabase.php';

### ENTITY LAYER: Represents the Listing Entity with database interaction
class CarListingEntity {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Retrieve listing details from the database
    public function getListingDetails($listing_id) {
        $stmt = $this->conn->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update listing in the database
    public function updateListing($data) {
        if ($data['listing_image'] !== null) {
            // Update with image
            $stmt = $this->conn->prepare(
                "UPDATE listing 
                 SET manufacturer_name = ?, model_name = ?, model_year = ?, listing_image = ?, listing_color = ?, listing_price = ?, listing_description = ? 
                 WHERE listing_id = ?"
            );
            $stmt->bind_param(
                "ssissisi",
                $data['manufacturer_name'],
                $data['model_name'],
                $data['model_year'],
                $data['listing_image'],
                $data['listing_color'],
                $data['listing_price'],
                $data['listing_description'],
                $data['listing_id']
            );
        } else {
            // Update without image
            $stmt = $this->conn->prepare(
                "UPDATE listing 
                 SET manufacturer_name = ?, model_name = ?, model_year = ?, listing_color = ?, listing_price = ?, listing_description = ? 
                 WHERE listing_id = ?"
            );
            $stmt->bind_param(
                "ssisisi",
                $data['manufacturer_name'],
                $data['model_name'],
                $data['model_year'],
                $data['listing_color'],
                $data['listing_price'],
                $data['listing_description'],
                $data['listing_id']
            );
        }
        return $stmt->execute();
    }
}

### CONTROLLER LAYER: Manages data passing and CRUD skeleton functions
class CarListingController {
    private $entity;

    public function __construct($entity) {
        $this->entity = $entity;
    }

    // Handle the data from Boundary, update if POST request, otherwise retrieve details
    public function handleRequest($listing_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['listing_image'] = null;

            // Handle image file upload if provided
            if (isset($_FILES['listing_image']) && $_FILES['listing_image']['error'] === UPLOAD_ERR_OK) {
                $data['listing_image'] = file_get_contents($_FILES['listing_image']['tmp_name']);
            }

            // Pass data to Entity for updating listing
            $this->entity->updateListing($data);
            header("Location: agent_view_listings.php");
            exit();
        }

        // Get the listing details from the Entity
        return $this->entity->getListingDetails($listing_id);
    }
}

### BOUNDARY LAYER: Renders the page, validation, and display logic
class UpdateCarListingBoundary {
    private $listing;

    public function __construct($listing) {
        $this->listing = $listing;
    }

    // Display function
    public function renderForm() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                /* General styling for the form */
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
                h1 { text-align: center; color: #555; }
                .form-container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
                label { font-size: 18px; }
                input, textarea, button { width: 100%; padding: 12px; font-size: 16px; margin-top: 5px; }
                button { background-color: #4CAF50; color: #fff; cursor: pointer; border: none; font-size: 18px; margin-top: 10px; }
                button[type="button"] { background-color: #ccc; }
                button:hover { background-color: #45a049; }
            </style>
        </head>
        <body>
        <h1>Update Listing</h1>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="listing_id" value="<?= htmlspecialchars($this->listing['listing_id']); ?>" />
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($this->listing['user_id']); ?>" />

                <label for="manufacturer_name">Manufacturer Name:</label>
                <input type="text" id="manufacturer_name" name="manufacturer_name" value="<?= htmlspecialchars($this->listing['manufacturer_name']); ?>" required />

                <label for="model_name">Model Name:</label>
                <input type="text" id="model_name" name="model_name" value="<?= htmlspecialchars($this->listing['model_name']); ?>" required />

                <label for="model_year">Model Year:</label>
                <input type="number" id="model_year" name="model_year" value="<?= htmlspecialchars($this->listing['model_year']); ?>" required />

                <label for="listing_color">Color:</label>
                <input type="text" id="listing_color" name="listing_color" value="<?= htmlspecialchars($this->listing['listing_color']); ?>" required />

                <label for="listing_price">Price:</label>
                <input type="number" step="0.01" id="listing_price" name="listing_price" value="<?= htmlspecialchars($this->listing['listing_price']); ?>" />

                <label for="listing_description">Description:</label>
                <textarea id="listing_description" name="listing_description" required><?= htmlspecialchars($this->listing['listing_description']); ?></textarea>

                <label for="listing_image">Listing Image:</label>
                <input type="file" id="listing_image" name="listing_image" />

                <button type="submit">Update Listing</button>
                <a href="agent_view_listings.php">
                    <button type="button">Return to Listings</button>
                </a>
            </form>
        </div>
        </body>
        </html>
        <?php
    }
}

// MAIN SCRIPT: Initializes and orchestrates BCE layers
$listing_id = $_GET['listing_id'];
$entity = new CarListingEntity($conn);
$controller = new CarListingController($entity);
$listingData = $controller->handleRequest($listing_id);
$boundary = new UpdateCarListingBoundary($listingData);
$boundary->renderForm();
?>

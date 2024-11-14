<?php
require '../connectDatabase.php';

### ENTITY LAYER: Represents the Listing Entity with database interaction
class CarListing {
    private $conn;
    private $listing_id;
    private $manufacturer_name;
    private $model_name;
    private $model_year;
    private $listing_image;
    private $listing_color;
    private $listing_price;
    private $listing_description;

    public function __construct($conn, $listing_id = null, $manufacturer_name = null, $model_name = null, $model_year = null, $listing_image = null, $listing_color = null, $listing_price = null, $listing_description = null) {
        $this->conn = $conn;
        $this->listing_id = $listing_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->model_name = $model_name;
        $this->model_year = $model_year;
        $this->listing_image = $listing_image;
        $this->listing_color = $listing_color;
        $this->listing_price = $listing_price;
        $this->listing_description = $listing_description;
    }
     // Getter methods
    public function getListingId() { return $this->listing_id; }
    public function getManufacturerName() { return $this->manufacturer_name; }
    public function getModelName() { return $this->model_name; }
    public function getModelYear() { return $this->model_year; }
    public function getListingImage() { return $this->listing_image; }
    public function getListingColor() { return $this->listing_color; }
    public function getListingPrice() { return $this->listing_price; }
    public function getListingDescription() { return $this->listing_description; }


    // Retrieve listing details from the database
    public function getListingDetails($listing_id) {
        $stmt = $this->conn->prepare("SELECT * FROM listing WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update listing in the database
    public function updateCarListing(CarListing $carListing) {
        if ($carListing->getListingImage() !== null) {
            $stmt = $this->conn->prepare(
                "UPDATE listing 
                 SET manufacturer_name = ?, model_name = ?, model_year = ?, listing_image = ?, listing_color = ?, listing_price = ?, listing_description = ? 
                 WHERE listing_id = ?"
            );
            $stmt->bind_param(
                "ssissisi",
                $carListing->getManufacturerName(),
                $carListing->getModelName(),
                $carListing->getModelYear(),
                $carListing->getListingImage(),
                $carListing->getListingColor(),
                $carListing->getListingPrice(),
                $carListing->getListingDescription(),
                $carListing->getListingId()
            );
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE listing 
                 SET manufacturer_name = ?, model_name = ?, model_year = ?, listing_color = ?, listing_price = ?, listing_description = ? 
                 WHERE listing_id = ?"
            );
            $stmt->bind_param(
                "ssisisi",
                $carListing->getManufacturerName(),
                $carListing->getModelName(),
                $carListing->getModelYear(),
                $carListing->getListingColor(),
                $carListing->getListingPrice(),
                $carListing->getListingDescription(),
                $carListing->getListingId()
            );
        }
        return $stmt->execute();
    }
}

### CONTROLLER LAYER: Manages data passing and CRUD skeleton functions
class UpdateCarListingController {
    private $entity;

    public function __construct($entity) {
        $this->entity = $entity;
    }

    public function getListingDetails($listing_id) {
        return $this->entity->getListingDetails($listing_id);
    }

    public function updateCarListing(CarListing $carListing) {
        return $this->entity->updateCarListing($carListing);
    }
}

### BOUNDARY LAYER: Renders the page, validation, and display logic
class UpdateCarListingPage {
    private $controller;
    private $listing;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function UpdateCarListingUI($listing_id) {
        $this->listing = $this->controller->getListingDetails($listing_id); // Set $this->listing with fetched data
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

    public function handleRequest($listing_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Create CarListing object with POST data
            global $conn;
            $carListing = new CarListing(
                $conn,
                $_POST['listing_id'],
                $_POST['manufacturer_name'],
                $_POST['model_name'],
                $_POST['model_year'],
                isset($_FILES['listing_image']) && $_FILES['listing_image']['error'] === UPLOAD_ERR_OK ? file_get_contents($_FILES['listing_image']['tmp_name']): null,  // Check if a file is uploaded, otherwise set to null
                $_POST['listing_color'],
                $_POST['listing_price'],
                $_POST['listing_description']
            );

            // Pass CarListing object to the controller's updateCarListing function
            $this->controller->updateCarListing($carListing);

            // Redirect after successful update
            header("Location: agent_view_listings.php");
            exit();
        } else {
            // Display form for a GET request
            $this->UpdateCarListingUI($listing_id);
        }
    }
}

// MAIN SCRIPT: Initializes and orchestrates BCE layers
$listing_id = $_GET['listing_id'];
$entity = new CarListing($conn);
$controller = new UpdateCarListingController($entity);
$boundary = new UpdateCarListingPage($controller);

$boundary->handleRequest($listing_id);
?>

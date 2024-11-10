<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

class CarListingEntity {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=localhost;dbname=your_database", "username", "password");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function createListing($formData, $username, $listing_image) {
        $stmt = $this->db->prepare("INSERT INTO car_listings (manufacturer_name, model_name, model_year, listing_image, listing_price, listing_description, listing_color, username) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $formData['manufacturer_name']);
        $stmt->bindParam(2, $formData['model_name']);
        $stmt->bindParam(3, $formData['model_year']);
        $stmt->bindParam(4, $listing_image, PDO::PARAM_LOB);
        $stmt->bindParam(5, $formData['listing_price']);
        $stmt->bindParam(6, $formData['listing_description']);
        $stmt->bindParam(7, $formData['listing_color']);
        $stmt->bindParam(8, $username);

        if ($stmt->execute()) {
            return ["message" => "Listing created successfully!"];
        } else {
            return ["message" => "Failed to create listing."];
        }
    }
}

class CarListingController {
    private $entity;

    public function __construct($entity) {
        $this->entity = $entity;
    }

    public function handleCarListingCreation($formData, $username, $listing_image) {
        return $this->entity->createListing($formData, $username, $listing_image);
    }
}

class CreateCarListingPage {
    private $message;
    private $controller;

    public function __construct($controller, $message = "") {
        $this->controller = $controller;
        $this->message = $message;
    }

    public function processRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->handleFormSubmission($_POST);
        }
    }

    public function handleFormSubmission($formData) {
        if (isset($_FILES['listing_image']) && $_FILES['listing_image']['error'] == UPLOAD_ERR_OK) {
            $listing_image = file_get_contents($_FILES['listing_image']['tmp_name']);
            $username = $_SESSION['username'];
    
            $response = $this->controller->handleCarListingCreation($formData, $username, $listing_image);
            
            $this->message = is_array($response) && isset($response['message']) 
                ? $response['message']
                : "Unexpected error occurred.";
        } else {
            $this->message = "A valid image file is required.";
        }
    }

    public function render() {
        ?>
        <html>
        <head>
            <title>Car Listing Creation Page</title>
            <style>
                .form-body { text-align: center; }
                .select-label { font-size: 24px; }
                .invisible-table {
                    border-collapse: collapse;
                    width: 0%;
                    margin: auto;
                }
                .invisible-table td { border: none; padding: 10px; }
            </style>
        </head>
        <body>
            <div style="background-color: green" class="header">
                <h1 style="text-align:center">Car Listing Creation</h1>
                <h2 style="text-align:center">Please fill in the following details</h2>
                <h3 style="text-align:center">All fields are mandatory</h3>
            </div>

            <?php if (!empty($this->message)): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

            <form class="form-body" method="POST" action="" enctype="multipart/form-data">
                <table class="invisible-table">
                    <tr><td><label style="font-size: 24px">Manufacturer Name:</label></td><td><input type="text" name="manufacturer_name" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Model Name:</label></td><td><input type="text" name="model_name" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Model Year:</label></td><td><input type="number" name="model_year" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Listing Image:</label></td><td><input type="file" name="listing_image" accept="image/*" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Listing Price:</label></td><td><input type="number" name="listing_price" step="0.01" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Listing Description:</label></td><td><textarea name="listing_description" style="font-size: 24px" required></textarea></td></tr>
                    <tr><td><label style="font-size: 24px">Listing Color:</label></td><td><input type="text" name="listing_color" style="font-size: 24px" required/></td></tr>
                </table>
                <br/>
                <button type="submit" style="font-size: 24px">Create New Car Listing</button>
            </form>
            <br/>
            <hr/>
            <form action="agent_view_listings.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Return to listings list.</button>
            </form>
        </body>
        </html>
        <?php
    }
}

// Instantiate and process
$carListingEntity = new CarListingEntity();
$carListingController = new CarListingController($carListingEntity);
$createCarListingPage = new CreateCarListingPage($carListingController);

// Process the form submission within the boundary class
$createCarListingPage->processRequest();
$createCarListingPage->render();
?>

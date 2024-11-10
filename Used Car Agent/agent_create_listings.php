<?php
session_start();
require_once '../connectDatabase.php';

//Entity layer
class CarListing {
    public function getUserId($conn, $username) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
        return $user_id;
    }

    public function createCarListing($conn, $manufacturer_name, $model_name, $model_year, $user_id, $listing_image, $listing_price, $listing_description, $listing_color) {
        $stmt = $conn->prepare("INSERT INTO listing (manufacturer_name, model_name, model_year, user_id, listing_image, listing_price, listing_description, listing_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssiissss", $manufacturer_name, $model_name, $model_year, $user_id, $listing_image, $listing_price, $listing_description, $listing_color);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
        return true;
    }
}


//Controller layer
class CreateCarListingController {
    private $carListingModel;

    public function __construct($carListingModel) {
        $this->carListingModel = $carListingModel;
    }

    public function handleCarListingCreation($formData, $username, $conn, $listing_image) {
        $manufacturer_name = $formData['manufacturer_name'] ?? null;
        $model_name = $formData['model_name'] ?? null;
        $model_year = $formData['model_year'] ?? null;
        $listing_price = $formData['listing_price'] ?? null;
        $listing_description = $formData['listing_description'] ?? null;
        $listing_color = $formData['listing_color'] ?? null;
    
        // Retrieve user ID based on username
        $user_id = $this->carListingModel->getUserId($conn, $username);
        if (!$user_id) {
            return ["success" => false, "message" => "User not found or not a Used Car Agent."];
        }
    
        // Attempt to create car listing
        $result = $this->carListingModel->createCarListing(
            $conn, $manufacturer_name, $model_name, $model_year, $user_id, 
            $listing_image, $listing_price, $listing_description, $listing_color
        );
    
        return $result 
            ? ["success" => true, "message" => "New car listing created successfully."]
            : ["success" => false, "message" => "Failed to create car listing."];
    }
    

    public function createCarListing($formData, $user_id, $conn, $listing_image) {
        // Extract data from form
        $manufacturer_name = $formData['manufacturer_name'];
        $model_name = $formData['model_name'];
        $model_year = $formData['model_year'];
        $listing_price = $formData['listing_price'];
        $listing_description = $formData['listing_description'];
        $listing_color = $formData['listing_color'];

        // Create car listing in the database
        return $this->carListingModel->createCarListing(
            $conn, $manufacturer_name, $model_name, $model_year, $user_id, 
            $listing_image, $listing_price, $listing_description, $listing_color
        );
    }
}


// BOUNDARY LAYER: Manages the user interface (display form and messages)
class CreateCarListingPage {
    private $message;
    private $controller;

    // Constructor to initialize the message and controller
    public function __construct($controller, $message = "") {
        $this->controller = $controller;
        $this->message = $message;
    }

    // Handle the form submission and validation
    public function handleFormSubmission($formData, $conn) {
        if (isset($_FILES['listing_image']) && $_FILES['listing_image']['error'] == UPLOAD_ERR_OK) {
            $listing_image = file_get_contents($_FILES['listing_image']['tmp_name']);
            $username = $_SESSION['username'];
    
            // Call the controller to handle car listing creation
            $response = $this->controller->handleCarListingCreation($formData, $username, $conn, $listing_image);
            
            // Check if $response is an array
            if (is_array($response) && isset($response['message'])) {
                $this->message = $response['message'];
            } else {
                $this->message = "Unexpected error occurred.";
            }
        } else {
            $this->message = "A valid image file is required.";
        }
    }
    

    // Render the car listing creation form
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

            <!-- Display success or error messages only after form submission -->
            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($this->message)): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

            <!-- Form for car listing creation -->
            <form class="form-body" method="POST" action="" enctype="multipart/form-data">
                <table class="invisible-table">
                    <tr>
                        <td><label style="font-size: 24px">Manufacturer Name:</label></td>
                        <td><input type="text" name="manufacturer_name" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Model Name:</label></td>
                        <td><input type="text" name="model_name" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Model Year:</label></td>
                        <td><input type="number" name="model_year" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Listing Image:</label></td>
                        <td><input type="file" name="listing_image" accept="image/*" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Listing Price:</label></td>
                        <td><input type="number" name="listing_price" step="0.01" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Listing Description:</label></td>
                        <td><textarea name="listing_description" style="font-size: 24px" required></textarea></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Listing Color:</label></td>
                        <td><input type="text" name="listing_color" style="font-size: 24px" required/></td>
                    </tr>
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



// MAIN LOGIC
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Initialize the controller and page
$carListingModel = new CarListing();
$controller = new CreateCarListingController($carListingModel);
$page = new CreateCarListingPage($controller);

// Check if the form was submitted and handle the submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $page->handleFormSubmission($_POST, $conn);
}

// Render the page
$page->render();

$database->closeConnection();
?>

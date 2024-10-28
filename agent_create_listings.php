<?php
session_start(); // Start the session

require_once 'connectDatabase.php'; // Include your Database class

// ENTITY LAYER: Handles data-related tasks (database interactions)
class CarListing {
    // Fetch the user ID based on username
    public function getUserId($conn, $username) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
        return $user_id;
    }

    // Insert a new car listing into the listings table
    public function createCarListing($conn, $manufacturer_name, $model_name, $model_year, $user_id, $listing_image, $listing_price, $listing_description, $listing_color) {
        $stmt = $conn->prepare("INSERT INTO listing (manufacturer_name, model_name, model_year, user_id, listing_image, listing_price, listing_description, listing_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Check if the statement was prepared successfully
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters ('s' for strings, 'i' for integers, 'd' for double, and 'b' for blob)
        $stmt->bind_param("ssiiisss", $manufacturer_name, $model_name, $model_year, $user_id, $listing_image, $listing_price, $listing_description, $listing_color);

        // Execute the query
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        return true; // Return true for successful execution
    }
}

// CONTROL LAYER: Handles the logic and mediates between boundary and entity layers
class CarListingController {
    private $carListingModel;

    // Constructor to initialize the CarListing entity model
    public function __construct($carListingModel) {
        $this->carListingModel = $carListingModel;
    }

    public function handleCarListingCreation($formData, $username, $conn) {
        $manufacturer_name = $formData['manufacturer_name'];
        $model_name = $formData['model_name'];
        $model_year = $formData['model_year'];
        $listing_price = $formData['listing_price'];
        $listing_description = $formData['listing_description'];
        $listing_color = $formData['listing_color'];

        // Use $_FILES for file uploads
        if (isset($_FILES['listing_image']) && $_FILES['listing_image']['error'] == UPLOAD_ERR_OK) {
            $listing_image = file_get_contents($_FILES['listing_image']['tmp_name']); // Read the uploaded image as binary
        } else {
            return "Error: Failed to upload image. Please try again.";
        }

        // Get the user ID based on the username
        $user_id = $this->carListingModel->getUserId($conn, $username);

        // Check if the user ID exists
        if (!$user_id) {
            return "Error: User not found or is not a Used Car Agent.";
        }

        // Insert the new car listing into the listings table
        $result = $this->carListingModel->createCarListing($conn, $manufacturer_name, $model_name, $model_year, $user_id, $listing_image, $listing_price, $listing_description, $listing_color);

        return $result ? "New car listing created successfully." : "Error: Failed to create car listing.";
    }
}

// BOUNDARY LAYER: Manages the user interface (display form and messages)
class CarListingView {
    private $message;

    // Constructor to initialize any message to display
    public function __construct($message = "") {
        $this->message = $message;
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

            <!-- Display success or error messages -->
            <?php if ($this->message): ?>
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
                        <td><input type="file" name="listing_image" accept="image/*" required/></td>
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
            <form action="agent_dashboard.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Return to main dashboard</button>
            </form>
        </body>
        </html>
        <?php
    }
}

// MAIN LOGIC: Connects the BCE components

// Entity layer: Initialize CarListing model
$carListingModel = new CarListing();

// Control layer: Initialize CarListingController with the entity model
$controller = new CarListingController($carListingModel);

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Retrieve the username from the session
$username = $_SESSION['username'];

// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $controller->handleCarListingCreation($_POST, $username, $conn);
}

// Boundary layer: Initialize CarListingView with any message and render the form
$view = new CarListingView($message);
$view->render();

// Close the database connection
$database->closeConnection();
?>

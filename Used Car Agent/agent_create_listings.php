<?php
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

/** ENTITY CLASS */
class CarListing {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=mariadb;dbname=csit314", "root", "");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function createCarListing($formData, $username, $listing_image) {
        // Retrieve the user_id based on the username
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user['user_id'];
        } else {
            throw new Exception("User not found.");
        }

        // Insert the car listing
        $stmt = $this->db->prepare("INSERT INTO listing (manufacturer_name, model_name, model_year, listing_image, listing_price, listing_description, listing_color, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $formData['manufacturer_name']);
        $stmt->bindParam(2, $formData['model_name']);
        $stmt->bindParam(3, $formData['model_year']);
        $stmt->bindParam(4, $listing_image, PDO::PARAM_LOB);
        $stmt->bindParam(5, $formData['listing_price']);
        $stmt->bindParam(6, $formData['listing_description']);
        $stmt->bindParam(7, $formData['listing_color']);
        $stmt->bindParam(8, $user_id);

        return $stmt->execute(); //returns a boolean
    }
}

/** CONTROLLER CLASS */
class CreateCarListingController {
    private $entity;

    public function __construct($entity) {
        $this->entity = $entity;
    }

    public function handleCarListingCreation($formData, $username, $listing_image) {
        return $this->entity->createCarListing($formData, $username, $listing_image);
    }
}

/** BOUNDARY CLASS */
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

            try {
                $isCreated = $this->controller->handleCarListingCreation($formData, $username, $listing_image);

                // Determine message
                if ($isCreated) {
                    $this->message = "Listing created successfully!";
                } else {
                    $this->message = "Failed to create listing.";
                }
            } catch (Exception $e) {
                $this->message = "Error: " . $e->getMessage();
            }
        } else {
            $this->message = "A valid image file is required.";
        }
    }

    public function CreateCarListingUI() {
        ?>
        <html>
        <head>
            <title>Car Listing Creation Page</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f6f9;
                    color: #333;
                }

                .header {
                    text-align: center;
                    margin-top: 50px;
                    color: #fff;
                    font-size: 1.8em;
                }

                .headDiv {
                    background-color: #28a745;
                    padding: 20px;
                    border-bottom: 2px solid #333;
                }

                .formBody {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 20px;
                }

                button, input[type="submit"] {
                    background-color: #007bff;
                    color: white;
                    border: none;
                    padding: 15px 30px;
                    margin: 10px 0;
                    border-radius: 5px;
                    font-size: 1em;
                    cursor: pointer;
                    width: 35%;
                    transition: background-color 0.3s;
                }

                button:hover, input[type="submit"]:hover {
                    background-color: #0056b3;
                }

                .mainInterface {
                    text-align: center;
                    background-color: #fff;
                    border: 1px solid #ddd;
                    padding: 20px;
                    margin-top: 50px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                    width: 50%;
                    margin-left: auto;
                    margin-right: auto;
                }

                h1,h2,h3 {
                    color: #333;
                    text-align: center;
                    margin-bottom: 20px;
                }

                .invisible-table {
                    border-collapse: collapse;
                    width: 100%;
                    margin-bottom: 20px;
                }

                .invisible-table td {
                    border: none;
                    padding: 10px;
                    font-size: 18px;
                }

                .invisible-table input[type="text"],
                .invisible-table input[type="number"],
                .invisible-table textarea {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ccc;
                    font-size: 18px;
                    border-radius: 5px;
                }

                @media (max-width: 768px) {
                    .mainInterface {
                        width: 80%;
                    }

                    .formBody button {
                        width: 90%;
                    }
                }
            </style>
        </head>
        <body>

        <div class="headDiv">
            <h1>Car Listing Creation</h1>
            <h2>Please fill in the following details</h2>
            <h3>All fields are mandatory</h3>
        </div>

        <?php if (!empty($this->message)): ?>
            <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
        <?php endif; ?>

        <div class="mainInterface">
            <form class="formBody" method="POST" action="" enctype="multipart/form-data">
                <table class="invisible-table">
                    <tr><td><label>Manufacturer Name:</label></td><td><input type="text" name="manufacturer_name" required/></td></tr>
                    <tr><td><label>Model Name:</label></td><td><input type="text" name="model_name" required/></td></tr>
                    <tr><td><label>Model Year:</label></td><td><input type="number" name="model_year" required/></td></tr>
                    <tr><td><label>Listing Image:</label></td><td><input type="file" name="listing_image" accept="image/*" required/></td></tr>
                    <tr><td><label>Listing Price:</label></td><td><input type="number" name="listing_price" step="0.01" required/></td></tr>
                    <tr><td><label>Listing Description:</label></td><td><textarea name="listing_description" required></textarea></td></tr>
                    <tr><td><label>Listing Color:</label></td><td><input type="text" name="listing_color" required/></td></tr>
                </table>
                <button type="submit">Create New Car Listing</button>
            </form>
            
        </div>
            <form action="agent_view_listings.php" class="formBody">
                <button type="submit">Return to listings list.</button>
            </form>
        </body>
        </html>
        <?php
    }
}

/** MAIN LOGIC */
$carListingEntity = new CarListing();
$carListingController = new CreateCarListingController($carListingEntity);
$createCarListingPage = new CreateCarListingPage($carListingController);

// Process the form submission and display the UI
$createCarListingPage->processRequest();
$createCarListingPage->CreateCarListingUI();
?>
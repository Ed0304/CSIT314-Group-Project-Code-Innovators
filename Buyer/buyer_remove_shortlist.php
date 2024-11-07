<?php
require_once "../connectDatabase.php"; // Ensure this contains the Database connection details

// Entity Layer: Shortlist - Manages database connection and queries
class Shortlist {
    private $conn;

    public function __construct() {
        // Establish a new database connection
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function __destruct() {
        // Close the database connection when the object is destroyed
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Method to remove shortlist entries by listing_id
    public function removeByListingId($listing_id) {
        $stmt = $this->conn->prepare("DELETE FROM shortlist WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Controller Layer: removeShortlistController - Manages business logic and calls the entity layer
class removeShortlistController {
    private $shortlistEntity;

    public function __construct() {
        // Instantiate the Shortlist entity within the controller
        $this->shortlistEntity = new Shortlist();
    }

    // Method to process the delete request
    public function removeShortlist($listing_id) {
        $this->shortlistEntity->removeByListingId($listing_id);
    }
}

// Boundary Layer: removeShortlistPage - Handles display and user interaction
class removeShortlistPage {
    private $controller;

    public function __construct() {
        // Instantiate the controller within the boundary layer
        $this->controller = new removeShortlistController();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'])) {
            $listing_id = $_POST['listing_id'];
            $this->controller->removeShortlist($listing_id);
            $this->render();
        } else {
            echo "No listing ID provided.";
        }
    }

    // Render method to display the page
    public function render() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Shortlist Removal</title>
            <style>
                /* General reset for consistency */
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                    font-family: Arial, sans-serif;
                }

                /* Body styling */
                body {
                    background-color: #f4f6f9;
                    color: #333;
                    font-size: 16px;
                    line-height: 1.6;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }

                /* Main container */
                .container {
                    background-color: #fff;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                }

                /* Heading styles */
                h1 {
                    font-size: 1.8em;
                    color: #333;
                    margin-bottom: 20px;
                }

                /* Message style */
                .message {
                    font-size: 1.2em;
                    color: #4CAF50; /* Success green color */
                    margin-bottom: 30px;
                }

                /* Button styling */
                .button {
                    display: inline-block;
                    padding: 12px 20px;
                    background-color: #007bff;
                    color: #fff;
                    font-size: 1em;
                    font-weight: bold;
                    text-decoration: none;
                    border-radius: 5px;
                    transition: background-color 0.3s ease;
                }

                .button:hover {
                    background-color: #0056b3;
                }

                /* Link styling for "Return to Dashboard" link */
                .return-link {
                    display: inline-block;
                    margin-top: 20px;
                    color: #007bff;
                    font-size: 0.9em;
                    text-decoration: none;
                }

                .return-link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Shortlist Item Removed</h1>
                <p class="message">The shortlisted item has been successfully removed.</p>
                <a href="buyer_dashboard.php" class="button">Return to Dashboard</a>
            </div>
        </body>
        </html>
        <?php
    }
}

// Main script: Instantiate the boundary layer and handle the request
$page = new removeShortlistPage();
$page->handleRequest();
?>

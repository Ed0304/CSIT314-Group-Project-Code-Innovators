<?php
session_start();
require_once "../connectDatabase.php";  // Include the file that contains your database connection

class ViewNumberOfShortlistsPage {
    private $sellerCountShortlistController;

    public function __construct($sellerCountShortlistController) {
        $this->sellerCountShortlistController = $sellerCountShortlistController;
    }

    public function ViewNumberOfShortlistsUI($listing_id) {
        $listing = $this->sellerCountShortlistController->ViewNumberOfShortlists($listing_id);

        if ($listing) {
            $formatted_price = "$" . number_format($listing['listing_price'], 2);

            echo "<div style='text-align: center; padding: 20px; font-family: Arial, sans-serif;'>";
            echo "<h2>{$listing['manufacturer_name']} {$listing['model_name']} ({$listing['model_year']})</h2>";
            echo "<img src='data:image/jpeg;base64," . base64_encode($listing['listing_image']) . "' alt='{$listing['manufacturer_name']}' style='max-width: 300px; border-radius: 10px;'>";
            echo "<p><strong>Color:</strong> {$listing['listing_color']}</p>";
            echo "<p><strong>Price:</strong> {$formatted_price}</p>";
            echo "<p><strong>Description:</strong> {$listing['listing_description']}</p>";
            echo "<p><strong>Agent Name:</strong> {$listing['agent_name']}</p>";  // Display agent's full name
            echo "<p><strong>Shortlisted Count:</strong> {$listing['shortlist_count']}</p>";
            echo "<a href='seller_view_listings.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; border-radius: 5px; text-decoration: none;'>Return to listing</a>";
            echo "</div>";
        } else {
            echo "<p style='text-align: center;'>Listing not found.</p>";
        }
    }

    public function handleRequest() {
        if (isset($_GET['listing_id'])) {
            $listing_id = $_GET['listing_id'];
            $this->ViewNumberOfShortlistsUI($listing_id);
        } else {
            echo "<p style='text-align: center;'>No listing ID provided.</p>";
        }
    }
}

class ViewNumberOfShortlistsController {
    private $CarListing;

    public function __construct($CarListing) {
        $this->CarListing = $CarListing;
    }

    public function ViewNumberOfShortlists($listing_id) {
        return $this->CarListing->ViewNumberOfShortlists($listing_id);
    }
}

class CarListing {
    private $pdo;

    public function __construct() {
        // Database connection setup within Entity
        $this->pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function ViewNumberOfShortlists($listing_id) {
        $query = "SELECT l.listing_id, l.manufacturer_name, l.model_name, l.model_year, 
                         CONCAT(p.first_name, ' ', p.last_name) AS agent_full_name,
                         l.listing_image, l.listing_color, l.listing_price, 
                         l.listing_description, COUNT(s.shortlist_id) AS shortlist_count
                  FROM listing l
                  LEFT JOIN users u ON l.user_id = u.user_id
                  LEFT JOIN profile p ON u.user_id = p.user_id  -- Join with profile table
                  LEFT JOIN shortlist s ON l.listing_id = s.listing_id
                  WHERE l.listing_id = :listing_id
                  GROUP BY l.listing_id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':listing_id', $listing_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return [
                'listing_id' => $row['listing_id'],
                'manufacturer_name' => $row['manufacturer_name'],
                'model_name' => $row['model_name'],
                'model_year' => $row['model_year'],
                'listing_image' => $row['listing_image'],
                'listing_color' => $row['listing_color'],
                'listing_price' => $row['listing_price'],
                'listing_description' => $row['listing_description'],
                'shortlist_count' => $row['shortlist_count'],
                'agent_name' => $row['agent_full_name']  // Use the full name of the agent
            ];
        } else {
            return null;
        }
    }
}

// Initialize classes
$CarListing = new CarListing();
$sellerCountShortlistController = new ViewNumberOfShortlistsController($CarListing);
$sellerCountShortlistPage = new ViewNumberOfShortlistsPage($sellerCountShortlistController);

// Handle the request
$sellerCountShortlistPage->handleRequest();
?>

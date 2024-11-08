<?php
session_start();
require_once "../connectDatabase.php";  // Include the file that contains your database connection

// Entity Class
class Listing {
    public $listing_id;
    public $manufacturer_name;
    public $model_name;
    public $model_year;
    public $listing_image;
    public $listing_color;
    public $listing_price;
    public $listing_description;
    public $views;
    public $shortlists;

    public function __construct($listing_id, $manufacturer_name, $model_name, $model_year, $listing_image, $listing_color, $listing_price, $listing_description, $views, $shortlists) {
        $this->listing_id = $listing_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->model_name = $model_name;
        $this->model_year = $model_year;
        $this->listing_image = $listing_image;
        $this->listing_color = $listing_color;
        $this->listing_price = $listing_price;
        $this->listing_description = $listing_description;
        $this->views = $views;
        $this->shortlists = $shortlists;
    }
}

// Controller Class
class ViewListingController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserIdByUsername($username) {
        $query = "SELECT user_id FROM users WHERE username = :username";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['user_id'];
        } else {
            return null;
        }
    }

    public function getListingsBySeller($user_id) {
        $query = "SELECT listing.*, ownership.seller_id 
                  FROM listing
                  JOIN ownership ON listing.listing_id = ownership.listing_id
                  WHERE ownership.seller_id = :seller_id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':seller_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $listings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $listing = new Listing(
                $row['listing_id'] ?? null,
                $row['manufacturer_name'] ?? null,
                $row['model_name'] ?? null,
                $row['model_year'] ?? null,
                $row['listing_image'] ?? null,
                $row['listing_color'] ?? null,
                $row['listing_price'] ?? null,
                $row['listing_description'] ?? null,
                $row['views'] ?? 0,
                $row['shortlists'] ?? 0
            );
            $listings[] = $listing;
        }

        return $listings;
    }
}

// Boundary Class
class ViewListingBoundary {
    private $viewListingController;

    public function __construct($viewListingController) {
        $this->viewListingController = $viewListingController;
    }

    public function displayListings($username) {
        echo "<style>
                /* CSS styles here */
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                .listing-container { display: flex; flex-wrap: wrap; justify-content: space-around; padding: 20px; }
                .listing-item { background-color: #fff; border: 1px solid #ddd; border-radius: 5px; width: 30%; margin: 10px; padding: 15px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center; }
                .listing-item img { width: 100%; height: auto; max-width: 200px; margin-bottom: 10px; border-radius: 5px; }
                .listing-item h3 { font-size: 18px; color: #333; }
                .listing-item p { color: #666; font-size: 14px; }
                .btn-view, .btn-shortlist, .btn-return { margin-top: 10px; padding: 8px 16px; background-color: #007bff; color: white; border-radius: 5px; text-decoration: none; }
                .btn-view:hover, .btn-shortlist:hover, .btn-return:hover { background-color: #0056b3; }
              </style>";
    
        $user_id = $this->viewListingController->getUserIdByUsername($username);
    
        if ($user_id) {
            $listings = $this->viewListingController->getListingsBySeller($user_id);
    
            echo "<div class='listing-container'>";
            if (count($listings) > 0) {
                foreach ($listings as $listing) {
                    $formatted_price = "$" . number_format($listing->listing_price, 2);
    
                    echo "<div class='listing-item'>";
                    echo "<h3>{$listing->manufacturer_name} {$listing->model_name} ({$listing->model_year})</h3>";
                    echo "<img src='data:image/jpeg;base64," . base64_encode($listing->listing_image) . "' alt='{$listing->manufacturer_name}'>";
                    echo "<p>Price: {$formatted_price}</p>";
                    echo "<p>Description: {$listing->listing_description}</p>";
                    echo "<a href='listing-views.php?listing_id={$listing->listing_id}' class='btn-view'>View Details</a>";
                    // Update the "See Shortlists" button to point to seller_count_shortlist.php
                    echo "<a href='seller_count_shortlist.php?listing_id={$listing->listing_id}' class='btn-shortlist'>See Shortlists</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>No listings found for this seller.</p>";
            }
            echo "</div>";
            echo "<a href='seller_dashboard.php' class='btn-return'>Return to Previous Page</a>";
        } else {
            echo "<p>No user found with that username.</p>";
        }
    }
}

// Main script to display listings when the user is logged in
$pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $viewListingController = new ViewListingController($pdo);
    $viewListingBoundary = new ViewListingBoundary($viewListingController);
    $viewListingBoundary->displayListings($username);
} else {
    echo "<p>Please log in to view your listings.</p>";
}
?>

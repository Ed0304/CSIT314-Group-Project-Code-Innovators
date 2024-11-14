<?php
session_start();

// Entity Class
class CarListing {
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
    private $pdo;

    public function __construct($listing_id = null, $manufacturer_name = null, $model_name = null, $model_year = null, $listing_image = null, $listing_color = null, $listing_price = null, $listing_description = null, $views = 0, $shortlists = 0) {
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

        // Database connection inside the Entity class
        $this->pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function sellerViewCarListings($username) {
        // Fetch user ID using username directly here
        $queryUserId = "SELECT user_id FROM users WHERE username = :username";
        $stmtUserId = $this->pdo->prepare($queryUserId);
        $stmtUserId->bindParam(':username', $username, PDO::PARAM_STR);
        $stmtUserId->execute();
        $user_id = $stmtUserId->fetchColumn();

        // Use user_id to fetch listings for the user
        $query = "SELECT listing.*, ownership.seller_id 
                  FROM listing
                  JOIN ownership ON listing.listing_id = ownership.listing_id
                  WHERE ownership.seller_id = :seller_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':seller_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $carlistings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $carlisting = new CarListing(
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
            $carlistings[] = $carlisting;
        }

        return $carlistings;
    }
}

// Controller Class
class SellerViewCarListingsController {
    private $carlisting;

    public function __construct($carlisting) {
        $this->carlisting = $carlisting;
    }

    public function sellerViewCarListings($username) {
        // Fetch listings for the user directly by username
        return $this->carlisting->sellerViewCarListings($username);
    }
}

// Boundary Class
class SellerViewCarListingsPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function sellerViewListingsUI() {
        if (!isset($_SESSION['username'])) {
            echo "<p>Please log in to view your listings.</p>";
            return;
        }

        $username = $_SESSION['username'];
        $carlistings = $this->controller->sellerViewCarListings($username);

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

        if ($carlistings) {
            echo "<div class='listing-container'>";
            foreach ($carlistings as $carlisting) {
                $formatted_price = "$" . number_format($carlisting->listing_price, 2);

                echo "<div class='listing-item'>";
                echo "<h3>{$carlisting->manufacturer_name} {$carlisting->model_name} ({$carlisting->model_year})</h3>";
                echo "<img src='data:image/jpeg;base64," . base64_encode($carlisting->listing_image) . "' alt='{$carlisting->manufacturer_name}'>";
                echo "<p>Price: {$formatted_price}</p>";
                echo "<p>Description: {$carlisting->listing_description}</p>";
                echo "<br/>";
                echo "<a href='sellerListingDetails.php?listing_id={$carlisting->listing_id}' class='btn-view'>View Details</a>";
                echo "<br/>";
                echo "<br/>";
                echo "<a href='seller_count_shortlist.php?listing_id={$carlisting->listing_id}' class='btn-shortlist'>See Shortlists</a>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>No listings found for this seller.</p>";
        }
        echo "<a href='seller_dashboard.php' class='btn-return'>Return to Previous Page</a>";
    }
}

// Main script to display listings
$carlisting = new CarListing();  // Entity class initialized without actual data
$listingController = new SellerViewCarListingsController($carlisting);  // Controller instantiation
$viewListingBoundary = new SellerViewCarListingsPage($listingController);  // Boundary receives controller
$viewListingBoundary->sellerViewListingsUI();
?>

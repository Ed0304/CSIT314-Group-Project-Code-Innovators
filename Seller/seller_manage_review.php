<?php
require "../connectDatabase.php";
session_start();

class ViewReviewPage {
    private $reviewController;

    public function __construct() {
        $this->reviewController = new ViewReviewController();
    }

    public function displayAgentsList() {
        $agents = $this->reviewController->getAllAgents();

        // Inline styling for simplicity
        echo "<style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h2 { text-align: center; color: #333; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
                th { background-color: #007bff; color: #fff; }
                img { width: 50px; height: 50px; border-radius: 50%; }
                .agent-card { padding: 15px; border: 1px solid #ddd; margin-bottom: 20px; }
                .car-list { margin-top: 10px; }
                .car-item { margin-left: 20px; font-style: italic; }
                .buttons { margin-top: 10px; }
                .buttons button { padding: 10px 15px; margin-right: 10px; background-color: #007bff; color: white; border: none; cursor: pointer; }
                .buttons button:hover { background-color: #0056b3; }
                .return-button { display: block; margin-top: 20px; text-align: center; }
                .return-button button { padding: 10px 20px; background-color: #6c757d; color: white; border: none; cursor: pointer; }
                .return-button button:hover { background-color: #5a6268; }
              </style>";

        echo "<h2>List of Agents and Their Cars</h2>";

        foreach ($agents as $agent) {
            $gender = ($agent['gender'] === 'M') ? 'Male' : (($agent['gender'] === 'F') ? 'Female' : 'Other');
            echo "<div class='agent-card'>";
            echo "<h3>{$agent['first_name']} {$agent['last_name']} (Agent ID: {$agent['user_id']})</h3>";
            echo "<p><strong>Username:</strong> {$agent['username']}</p>";
            echo "<p><strong>Email:</strong> {$agent['email']}</p>";
            echo "<p><strong>Phone:</strong> {$agent['phone_num']}</p>";
            echo "<p><strong>About:</strong> {$agent['about']}</p>";
            echo "<p><strong>Gender:</strong> $gender</p>";

            // Display cars for each agent
            echo "<div class='car-list'><strong>Cars Selling:</strong>";
            $cars = $this->reviewController->getCarsByAgent($agent['user_id']);
            if ($cars) {
                foreach ($cars as $car) {
                    echo "<div class='car-item'>- {$car['manufacturer_name']} {$car['model_name']} ({$car['model_year']}) - $" . number_format($car['listing_price'], 2) . "</div>";
                }
            } else {
                echo "<p class='car-item'>No cars listed.</p>";
            }
            echo "</div>";

            // Add buttons for View and Create Ratings/Reviews
            echo "<div class='buttons'>";
            echo "<form method='get' action='seller_view_reviews.php'>";
            echo "<input type='hidden' name='agent_id' value='{$agent['user_id']}'>";
            echo "<button type='submit'>View Ratings and Reviews</button>";
            echo "</form>";

            echo "<form method='get' action='seller_give_review.php'>";
            echo "<input type='hidden' name='agent_id' value='{$agent['user_id']}'>";
            echo "<button type='submit'>Create Ratings and Reviews</button>";
            echo "</form>";
            echo "</div>";

            echo "</div>";
        }

        // Return button
        echo "<div class='return-button'>";
        echo "<form method='get' action='seller_dashboard.php'>"; // Replace 'previous_page.php' with the actual return page
        echo "<button type='submit'>Return</button>";
        echo "</form>";
        echo "</div>";
    }
}

class ViewReviewController {
    private $Review;

    public function __construct() {
        $this->Review = new Review();
    }

    public function getAllAgents() {
        return $this->Review->getAllAgents();
    }

    public function getCarsByAgent($agent_id) {
        return $this->Review->getCarsForAgent($agent_id);
    }
}

class Review {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAllAgents() {
        $query = "SELECT u.user_id, u.username, u.email, u.phone_num, 
                         p.first_name, p.last_name, p.about, p.gender, p.profile_image 
                  FROM users u
                  INNER JOIN profile p ON u.user_id = p.user_id
                  WHERE u.role_id = :agent_role_id";
        
        $stmt = $this->pdo->prepare($query);
        $agentRoleId = 2; // Assuming role_id = 2 represents agents; adjust if different
        $stmt->bindParam(':agent_role_id', $agentRoleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCarsForAgent($agent_id) {
        $query = "SELECT manufacturer_name, model_name, model_year, listing_price 
                  FROM listing 
                  WHERE user_id = :user_id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $agent_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialize Boundary
$reviewBoundary = new ViewReviewPage();

// Display all agents and their cars
$reviewBoundary->displayAgentsList();

?>

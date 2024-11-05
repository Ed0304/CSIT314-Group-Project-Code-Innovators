<?php
session_start();

require 'connectDatabase.php';

// Entity Class: Review
class Review {
    private $mysqli;
    private $details;
    private $stars;
    private $date;
    private $reviewerUsername;
    private $review_id; // Added review_id property
    private $agentFirstName; // Added property for agent's first name
    private $agentLastName;  // Added property for agent's last name

    public function __construct($mysqli, $details = null, $stars = null, $date = null, $reviewerUsername = null, $review_id = null, $agentFirstName = null, $agentLastName = null) {
        $this->mysqli = $mysqli;
        $this->details = $details;
        $this->stars = $stars;
        $this->date = $date;
        $this->reviewerUsername = $reviewerUsername;
        $this->review_id = $review_id; // Store the review_id
        $this->agentFirstName = $agentFirstName; // Store agent's first name
        $this->agentLastName = $agentLastName; // Store agent's last name
    }

    public function getDetails() {
        return $this->details;
    }

    public function getStars() {
        return $this->stars;
    }

    public function getDate() {
        return $this->date;
    }

    public function getReviewerUsername() {
        return $this->reviewerUsername;
    }

    public function getReviewId() { // Method to retrieve review_id
        return $this->review_id;
    }

    public function getAgentFirstName() { // Method to retrieve agent's first name
        return $this->agentFirstName;
    }

    public function getAgentLastName() { // Method to retrieve agent's last name
        return $this->agentLastName;
    }

    // Method to retrieve user ID by username
    public function getUserIdByUsername($username) {
        $stmt = $this->mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['user_id'];
        }
        return null;
    }

    // Method to retrieve reviews for a specific agent
    public function getAgentRatingsAndReviews($agent_id) {
        $query = "SELECT r.review_id, r.review_details, r.review_stars, r.review_date, 
                         u.username, p.first_name, p.last_name
                  FROM review r
                  JOIN users u ON r.reviewer_id = u.user_id
                  JOIN profile p ON r.agent_id = p.user_id
                  WHERE r.agent_id = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = new Review(
                $this->mysqli, 
                $row['review_details'], 
                $row['review_stars'], 
                $row['review_date'], 
                $row['username'], 
                $row['review_id'], 
                $row['first_name'], // Pass the agent's first name
                $row['last_name']   // Pass the agent's last name
            );
        }
        return $reviews;
    }
}

// Control Class: RatingsReviewsController
class RatingsReviewsController {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function handleRequest() {
        $reviews = [];
        $agent_id = null; // Initialize agent_id
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
            $username = trim($_GET['username']);
            if (!empty($username)) {
                $reviewEntity = new Review($this->mysqli);
                $userId = $reviewEntity->getUserIdByUsername($username);
                if ($userId) {
                    $reviews = $reviewEntity->getAgentRatingsAndReviews($userId);
                    $agent_id = $userId; // Set agent_id based on the userId found
                }
            }
        }
        return [$reviews, $agent_id]; // Return both reviews and agent_id
    }
}

// Boundary Class: RatingsReviewsView
class RatingsReviewsView {
    public function render($reviews = [], $message = "", $agentFirstName = "", $agentLastName = "", $agent_id = null) {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Agent Ratings & Reviews</title>
            <style>
                #reviews-table {
                    border-collapse: collapse;
                    width: 100%;
                }
                #reviews-table, 
                #reviews-table th, 
                #reviews-table td {
                    border: 1px solid black;
                }
                #reviews-table th, 
                #reviews-table td {
                    padding: 10px;
                    font-size: 20px;
                    text-align: center;
                }
                .star {
                    width: 25px;
                    height: 25px;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    font-size: 24px;
                    color: white;
                    background-color: #007BFF; /* Bootstrap primary color */
                    border: none;
                    border-radius: 5px;
                    text-decoration: none; /* Remove underline */
                    text-align: center;
                    transition: background-color 0.3s;
                }
                .button:hover {
                    background-color: #0056b3; /* Darker shade on hover */
                }
            </style>
        </head>
        <body>
            <h1 style="text-align:center">Ratings for <?php echo htmlspecialchars($agentFirstName . ' ' . $agentLastName); ?></h1> <!-- Display agent's name in the heading -->
            <div style="text-align:center">
                <a href="buyer_give_reviews.php?agent_id=<?php echo htmlspecialchars($agent_id); ?>" class="button">Create a review</a>
            </div>
            <?php
            if (!empty($reviews)) {
                echo "<table id='reviews-table'>";
                echo "<tr><th>Ratings</th><th>Rated By:</th><th>Action</th></tr>";
                foreach ($reviews as $review) {
                    echo "<tr>";
                    echo "<td>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review->getStars()) {
                            echo "<img src='star.png' alt='Filled Star' class='star'>";
                        } else {
                            echo "<img src='empty-star.png' alt='Empty Star' class='star'>";
                        }
                    }
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($review->getReviewerUsername()) . "</td>";
                    // Include the review_id in the link to the review details
                    echo "<td><a href='buyer_view_review_details.php?review_id=" . htmlspecialchars($review->getReviewId()) . "' class='button'>See Review Details</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No ratings or reviews found for this agent.</p>";
            }
            ?>
            <br/>
            <br/>
            <div style="text-align:center">
                <a href="buyer_view_agent_details.php?user_id=<?php echo htmlspecialchars($agent_id); ?>" class="button">Return</a>
            </div>
        </body>
        </html>
        <?php
    }
}

// Entry Point
$database = new Database();
$mysqli = $database->getConnection();

$ratingsReviewsController = new RatingsReviewsController($mysqli);
list($reviews, $agent_id) = $ratingsReviewsController->handleRequest(); // Get reviews and agent_id

// Get agent's name from the first review (if available) to display in the heading
$agentFirstName = !empty($reviews) ? $reviews[0]->getAgentFirstName() : '';
$agentLastName = !empty($reviews) ? $reviews[0]->getAgentLastName() : '';

$view = new RatingsReviewsView();
$view->render($reviews, "", $agentFirstName, $agentLastName, $agent_id); // Pass agent names to render

$database->closeConnection();
?>

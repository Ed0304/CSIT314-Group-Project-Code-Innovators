<?php
session_start();

require '../connectDatabase.php';

// Entity Class: Review
class Review {
    private $mysqli;
    private $details;
    private $stars;
    private $date;
    private $reviewerUsername;

    public function __construct($mysqli, $details = null, $stars = null, $date = null, $reviewerUsername = null) {
        $this->mysqli = $mysqli;
        $this->details = $details;
        $this->stars = $stars;
        $this->date = $date;
        $this->reviewerUsername = $reviewerUsername;
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
        $query = "SELECT r.review_details, r.review_stars, r.review_date, u.username
                  FROM review r
                  JOIN users u ON r.reviewer_id = u.user_id
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
            $reviews[] = new Review($this->mysqli, $row['review_details'], $row['review_stars'], $row['review_date'], $row['username']);
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
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
            $username = trim($_GET['username']);
            if (!empty($username)) {
                $reviewEntity = new Review($this->mysqli);
                $userId = $reviewEntity->getUserIdByUsername($username);
                if ($userId) {
                    $reviews = $reviewEntity->getAgentRatingsAndReviews($userId);
                }
            }
        }
        return $reviews;
    }
}

// Boundary Class: RatingsReviewsView
class RatingsReviewsView {
    public function render($reviews = [], $message = "") {
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
                .button-font {
                    font-size: 18px;
                }
            </style>
        </head>
        <body>
            <h1 style="text-align:center">Agent Ratings & Reviews</h1>
            <?php
            if (!empty($reviews)) {
                echo "<table id='reviews-table'>";
                echo "<tr><th>Stars</th><th>Review</th><th>Date</th><th>Rated By:</th></tr>";
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
                    echo "<td>" . htmlspecialchars($review->getDetails()) . "</td>";
                    echo "<td>" . htmlspecialchars($review->getDate()) . "</td>";
                    echo "<td>" . htmlspecialchars($review->getReviewerUsername()) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No ratings or reviews found for this agent.</p>";
            }
            ?>
            <form method="post" action="agent_dashboard.php" style="text-align:center">
                <br/>
                <input type="submit" value="Return" style="font-size: 24px">
            </form>
        </body>
        </html>
        <?php
    }
}

// Entry Point
$database = new Database();
$mysqli = $database->getConnection();

$ratingsReviewsController = new RatingsReviewsController($mysqli);
$reviews = $ratingsReviewsController->handleRequest();

$view = new RatingsReviewsView();
$view->render($reviews);

$database->closeConnection();
?>

<?php
session_start();

require '../connectDatabase.php';

// Entity Class: Review
class Reviews {
    private $mysqli;

    private $stars;
    private $date;
    private $reviewerUsername;
    private $review_id;

    public function __construct($mysqli, $stars = null, $date = null, $reviewerUsername = null, $review_id = null) {
        $this->mysqli = $mysqli;
        $this->stars = $stars;
        $this->date = $date;
        $this->reviewerUsername = $reviewerUsername;
        $this->review_id = $review_id;
    }

    public function getReviewId() {
        return $this->review_id;
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
        $query = "SELECT r.review_id, r.review_stars, r.review_date, u.username
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
            $reviews[] = new Reviews($this->mysqli, $row['review_stars'], $row['review_date'], $row['username'], $row['review_id']);
        }
        return $reviews;
    }
}

// Control Class: ViewAllReviewsController
class ViewAllReviewsController {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function handleRequest() {
        $reviews = [];
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
            $username = trim($_GET['username']);
            if (!empty($username)) {
                $reviewEntity = new Reviews($this->mysqli);
                $userId = $reviewEntity->getUserIdByUsername($username);
                if ($userId) {
                    $reviews = $reviewEntity->getAgentRatingsAndReviews($userId);
                }
            }
        }
        return $reviews;
    }
}

// Boundary Class: ViewAllReviewsPage
class ViewAllReviewsPage {
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
                echo "<tr><th>Rating</th><th>Date</th><th>Rated By:</th><th>Actions</th></tr>";
                foreach ($reviews as $review) {
                    echo "<tr>";
                    echo "<td>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review->getStars()) {
                            echo "<img src='../star.png' alt='Filled Star' class='star'>";
                        } else {
                            echo "<img src='../empty-star.png' alt='Empty Star' class='star'>";
                        }
                    }
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($review->getDate()) . "</td>";
                    echo "<td>" . htmlspecialchars($review->getReviewerUsername()) . "</td>";
                    
                    // Add button to pass review_id to another PHP file
                    echo "<td>";
                    echo "<form action='agent_view_single_review.php' method='get' style='display:inline;'>";
                    echo "<input type='hidden' name='review_id' value='" . $review->getReviewId() . "'>";
                    echo "<input type='submit' value='View Review' style='font-size: 18px'>";
                    echo "</form>";
                    echo "</td>";
                    
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

$ratingsReviewsController = new ViewAllReviewsController($mysqli);
$reviews = $ratingsReviewsController->handleRequest();

$view = new ViewAllReviewsPage();
$view->render($reviews);

$database->closeConnection();
?>

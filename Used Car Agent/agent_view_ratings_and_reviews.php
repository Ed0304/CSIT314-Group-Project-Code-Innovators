<?php
session_start();

require '../connectDatabase.php';

// Entity Class: Review
class Review {
    public $mysqli;
    public $stars;
    public $date;
    public $reviewerUsername;
    public $review_id;

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
            $reviews[] = new Review($this->mysqli, $row['review_stars'], $row['review_date'], $row['username'], $row['review_id']);
        }
        return $reviews;
    }
}

// Control Class: ViewAllReviewsController
class ViewAllReviewsController {
    public $reviewEntity;

    public function __construct($reviewEntity) {
        $this->reviewEntity = $reviewEntity;
    }

    public function getAgentReviewsByUserId($userId) {
        return $this->reviewEntity->getAgentRatingsAndReviews($userId);
    }
}

// Boundary Class: ViewAllReviewsPage
class ViewAllReviewsPage {
    public function handleRequest($controller) {
        $reviews = [];
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
            $username = trim($_GET['username']);
            if (!empty($username)) {
                $userId = $controller->reviewEntity->getUserIdByUsername($username);
                if ($userId) {
                    $reviews = $controller->getAgentReviewsByUserId($userId);
                }
            }
        }
        return $reviews;
    }

    public function ViewAllReviewsUI($controller, $message = "") {
        $reviews = $this->handleRequest($controller);
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Agent Ratings & Reviews</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }

                h1 {
                    text-align: center;
                    color: #343a40;
                    margin-top: 30px;
                }

                #reviews-table {
                    width: 80%;
                    margin: 20px auto;
                    border-collapse: collapse;
                    background-color: #ffffff;
                    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                }

                #reviews-table th, #reviews-table td {
                    padding: 12px;
                    text-align: center;
                    font-size: 16px;
                    color: #343a40;
                    border: 1px solid #dee2e6;
                }

                #reviews-table th {
                    background-color: #6c757d;
                    color: white;
                    font-weight: bold;
                }

                #reviews-table td {
                    background-color: #f9f9f9;
                }

                #reviews-table tr:nth-child(even) {
                    background-color: #f1f1f1;
                }

                .star {
                    width: 20px;
                    height: 20px;
                    margin: 0 2px;
                }

                button, input[type="submit"] {
                    background-color: #007bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    padding: 8px 16px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: background-color 0.3s ease;
                }

                button:hover, input[type="submit"]:hover {
                    background-color: #0056b3;
                }

                form {
                    text-align: center;
                }

                form input[type="submit"] {
                    font-size: 18px;
                    margin-top: 20px;
                }

                form input[type="submit"]:hover {
                    background-color: #0056b3;
                }

                p {
                    text-align: center;
                    color: #6c757d;
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

$reviewEntity = new Review($mysqli);
$ratingsReviewsController = new ViewAllReviewsController($reviewEntity);
$view = new ViewAllReviewsPage();
$view->ViewAllReviewsUI($ratingsReviewsController);

$database->closeConnection();
?>

<?php
session_start();

require 'connectDatabase.php';

// Entity Class: User
class User {
    private $userId;
    private $username;

    public function __construct($userId, $username) {
        $this->userId = $userId;
        $this->username = $username;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getUsername() {
        return $this->username;
    }
}

// Control Class: RatingsReviewsController
class RatingsReviewsController {
    private $mysqli;
    private $view;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->view = new RatingsReviewsView(); // Instantiate the view
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
            $username = trim($_GET['username']);
            if (empty($username)) {
                return $this->view->render([], "Username cannot be empty.");
            }

            $user = $this->getUserByUsername($username);
            if ($user) {
                $reviews = $this->getAgentRatingsAndReviews($user->getUserId());
                return $this->view->render($reviews);
            } else {
                return $this->view->render([], "Agent not found for the given username.");
            }
        } else {
            return $this->view->render([], "No username provided.");
        }
    }

    private function getUserByUsername($username) {
        $stmt = $this->mysqli->prepare("SELECT user_id, username FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new User($row['user_id'], $row['username']);
        }
        return null;
    }

    private function getAgentRatingsAndReviews($agent_id) {
        $query = "SELECT r.review_details, r.review_stars, r.review_date, u.username
                  FROM review r
                  JOIN users u ON r.reviewer_id = u.user_id
                  WHERE r.agent_id = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
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
                    width: 25px; /* Adjust size as needed */
                    height: 25px; /* Adjust size as needed */
                }
                .button-font {
                    font-size: 18px;
                }
            </style>
        </head>
        <body>
            <h1 style="text-align:center">Agent Ratings & Reviews</h1>
            <?php
            if ($message) {
                echo "<p style='color: red; text-align:center;'>$message</p>";
            }
            if (!empty($reviews)) {
                echo "<table id='reviews-table'>";
                echo "<tr><th>Stars</th><th>Review</th><th>Date</th><th>Rated By:</th></tr>";
                foreach ($reviews as $review) {
                    echo "<tr>";
                    echo "<td>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review['review_stars']) {
                            echo "<img src='star.png' alt='Star' class='star'>";
                        } else {
                            echo "<img src='empty-star.png' alt='Empty Star' class='star'>";
                        }
                    }
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($review['review_details']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['review_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['username']) . "</td>";
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
$ratingsReviewsController->handleRequest();

$database->closeConnection();
?>

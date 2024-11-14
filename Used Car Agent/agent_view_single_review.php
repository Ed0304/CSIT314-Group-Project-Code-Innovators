<?php
session_start();
require '../connectDatabase.php';

// Entity Class: Review
class Review {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    // Method to retrieve a single review by review_id
    public function fetchById($review_id) {
        $query = "SELECT r.review_details, r.review_stars, r.review_date, u.username
                  FROM review r
                  JOIN users u ON r.reviewer_id = u.user_id
                  WHERE r.review_id = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) return null;

        $stmt->bind_param('i', $review_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return [
                'details' => $row['review_details'],
                'stars' => $row['review_stars'],
                'date' => $row['review_date'],
                'reviewerUsername' => $row['username']
            ];
        }
        return null;
    }
}

// Control Class: ViewOneReviewController
class ViewOneReviewController {
    private $reviewEntity;

    public function __construct($reviewEntity) {
        $this->reviewEntity = $reviewEntity;
    }

    public function getReviewData($review_id) {
        return $this->reviewEntity->fetchById($review_id);
    }
}

// Boundary Class: ViewOneReviewPage
class ViewOneReviewPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function handleRequest() {
        $reviewData = null;
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['review_id'])) {
            $review_id = (int)$_GET['review_id'];
            $reviewData = $this->controller->getReviewData($review_id);
        }
        $this->render($reviewData);
    }

    private function render($reviewData) {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Agent Review</title>
            <style>
               body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f9fa;
                    margin: 0;
                    padding: 0;
                }

                h1 {
                    text-align: center;
                    color: #343a40;
                    margin-top: 30px;
                }

                #review-table {
                    width: 80%;
                    margin: 20px auto;
                    border-collapse: collapse;
                    background-color: #ffffff;
                    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                }

                #review-table th, #review-table td {
                    padding: 12px;
                    text-align: center;
                    font-size: 18px;
                    color: #343a40;
                    border: 1px solid #dee2e6;
                }

                #review-table th {
                    background-color: #6c757d;
                    color: white;
                    font-weight: bold;
                }

                #review-table td {
                    background-color: #f9f9f9;
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
                    padding: 10px 20px;
                    cursor: pointer;
                    font-size: 18px;
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
            <h1 style="text-align:center">Agent Review</h1>
            <?php if ($reviewData): ?>
                <table id="review-table">
                    <tr><th>Rating</th><th>Review Description</th><th>Date</th><th>Rated By:</th></tr>
                    <tr>
                        <td>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <img src="../<?= $i <= $reviewData['stars'] ? 'star.png' : 'empty-star.png' ?>" class="star">
                            <?php endfor; ?>
                        </td>
                        <td><?= htmlspecialchars($reviewData['details']) ?></td>
                        <td><?= htmlspecialchars($reviewData['date']) ?></td>
                        <td><?= htmlspecialchars($reviewData['reviewerUsername']) ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <p>No review found for this ID.</p>
            <?php endif; ?>

            <form method="get" action="agent_view_ratings_and_reviews.php" style="text-align:center">
                <input type="hidden" name="username" value="<?= htmlspecialchars($_SESSION['username']); ?>">
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
$reviewController = new ViewOneReviewController($reviewEntity);

$view = new ViewOneReviewPage($reviewController);
$view->handleRequest();

$database->closeConnection();

?>

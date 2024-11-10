<?php
session_start();

require '../connectDatabase.php';

// Entity Class: Review
class Reviews {
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

    // Method to retrieve a single review by review_id
    public function getSingleReviewById($review_id) {
        $query = "SELECT r.review_details, r.review_stars, r.review_date, u.username
                  FROM review r
                  JOIN users u ON r.reviewer_id = u.user_id
                  WHERE r.review_id = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $review_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return new Reviews($this->mysqli, $row['review_details'], $row['review_stars'], $row['review_date'], $row['username']);
        }
        return null;
    }
}

// Control Class: ViewSpecificReviewController
class ViewSpecificReviewController {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function handleRequest() {
        $review = null;
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['review_id'])) {
            $review_id = (int)$_GET['review_id'];
            $reviewEntity = new Reviews($this->mysqli);
            $review = $reviewEntity->getSingleReviewById($review_id);
        }
        return $review;
    }
}

// Boundary Class: ViewSpecificReviewPage
class ViewSpecificReviewPage {
    public function render($review = null, $message = "") {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Agent Review</title>
            <style>
                #review-table {
                    border-collapse: collapse;
                    width: 100%;
                }
                #review-table, 
                #review-table th, 
                #review-table td {
                    border: 1px solid black;
                }
                #review-table th, 
                #review-table td {
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
            <h1 style="text-align:center">Agent Review</h1>
            <?php
            if ($review) {
                echo "<table id='review-table'>";
                echo "<tr><th>Rating</th><th>Review Description</th><th>Date</th><th>Rated By:</th></tr>";
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
                echo "<td>" . htmlspecialchars($review->getDetails()) . "</td>";
                echo "<td>" . htmlspecialchars($review->getDate()) . "</td>";
                echo "<td>" . htmlspecialchars($review->getReviewerUsername()) . "</td>";
                echo "</tr>";
                echo "</table>";
            } else {
                echo "<p>No review found for this ID.</p>";
            }
            ?>
            <form method="get" action="agent_view_ratings_and_reviews.php" style="text-align:center">
                <br/>
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
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

$ratingsReviewsController = new ViewSpecificReviewController($mysqli);
$review = $ratingsReviewsController->handleRequest();

$view = new ViewSpecificReviewPage();
$view->render($review);

$database->closeConnection();
?>

<?php
session_start();
// Entity Layer
class Review
{
    private $mysqli;
    private $details;
    private $stars;
    private $seller_name;
    private $date;

    public function __construct($details = null, $stars = null, $seller_name = null, $date = null)
    {
        $this->details = $details;
        $this->stars = $stars;
        $this->seller_name = $seller_name;
        $this->date = $date;

        // Initialize the database connection here
        $this->mysqli = new mysqli("mariadb", "root", "", "csit314");
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function __destruct()
    {
        // Close the connection when the object is destroyed
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }

    // Getter methods
    public function getDetails()
    {
        return $this->details;
    }

    public function getStars()
    {
        return $this->stars;
    }

    public function getSellerName()
    {
        return $this->seller_name;
    }

    public function getDate()
    {
        return $this->date;
    }

    // CRUD - Read: Fetch reviews by agent
    public function viewReview($agent_id)
    {
        $stmt = $this->mysqli->prepare("
            SELECT r.review_details, r.review_stars, CONCAT(s.first_name, ' ', s.last_name) AS seller_name, r.review_date
            FROM review r
            JOIN users u_seller ON r.reviewer_id = u_seller.user_id
            JOIN profile s ON u_seller.user_id = s.user_id
            WHERE r.agent_id = ?
            ORDER BY r.review_date DESC
        ");
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = new Review($row['review_details'], $row['review_stars'], $row['seller_name'], $row['review_date']);
        }

        $stmt->close();
        return $reviews;
    }

    // CRUD - Read: Fetch agent name by agent ID
    public function fetchAgentName($agent_id)
    {
        $stmt = $this->mysqli->prepare("
            SELECT CONCAT(p.first_name, ' ', p.last_name) AS agent_name
            FROM users u
            JOIN profile p ON u.user_id = p.user_id
            WHERE u.user_id = ? AND u.role_id = 2
        ");
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $agent = $result->fetch_assoc();
        $stmt->close();

        return $agent ? $agent['agent_name'] : null;
    }
}

// Controller Layer
class ViewReviewController
{
    private $reviewEntity;

    public function __construct($reviewEntity)
    {
        $this->reviewEntity = $reviewEntity;
    }

    // Fetch reviews by agent ID, called by Boundary
    public function viewReview($agent_id)
    {
        return $this->reviewEntity->viewReview($agent_id);
    }

    // Fetch agent name by agent ID, called by Boundary
    public function getAgentName($agent_id)
    {
        return $this->reviewEntity->fetchAgentName($agent_id);
    }
}

// Boundary Layer (UI and Interaction)
class ViewReviewPage
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    // ViewReviewUI function to display reviews and agent name
    public function ViewReviewUI()
    {
        if (!$this->isUserLoggedIn()) {
            echo "<p>Please log in to view agent reviews.</p>";
            return;
        }

        $agent_id = $this->getAgentId();
        if (!$this->validateAgentId($agent_id)) {
            echo "<p>Invalid agent ID.</p>";
            return;
        }

        $agent_name = $this->controller->getAgentName($agent_id);
        if (!$agent_name) {
            echo "<p>Agent not found or invalid ID.</p>";
            return;
        }

        $reviews = $this->controller->viewReview($agent_id);
        $this->displayReviews($agent_name, $reviews);
    }

    // Display function to ViewReviewUI reviews
    private function displayReviews($agent_name, $reviews)
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Reviews for <?php echo htmlspecialchars($agent_name); ?></title>
            <style>
                 body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f9;
                    color: #333;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #fff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                    border-radius: 8px;
                }
                h1 {
                    color: #4a90e2;
                    text-align: center;
                }
                .review {
                    border-bottom: 1px solid #ddd;
                    padding: 15px 0;
                }
                .stars {
                    color: #ffd700;
                    font-size: 1.2em;
                }
                .date {
                    font-size: 0.9em;
                    color: #777;
                }
                .seller {
                    font-weight: bold;
                    margin-top: 5px;
                }
                .details {
                    margin-top: 10px;
                    white-space: pre-line;
                }
                .no-reviews {
                    text-align: center;
                    color: #999;
                    font-style: italic;
                }
                /* Add styles for the return button */
                .return-button {
                    display: block;
                    width: 200px;
                    margin: 30px auto;
                    padding: 10px;
                    background-color: #6c757d;
                    color: white;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 1.2em;
                }
                .return-button:hover {
                    background-color: #5a6268;
                }
            </style>
        </head>
        <body>
        <div class="container">
            <h1>Reviews for <?php echo htmlspecialchars($agent_name); ?></h1>
            <?php if (empty($reviews)): ?>
                <p>No reviews found for this agent.</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <div class="stars"><?php echo str_repeat("★", $review->getStars()) . str_repeat("☆", 5 - $review->getStars()); ?></div>
                        <div class="date"><?php echo htmlspecialchars($review->getDate()); ?></div>
                        <div class="seller">Reviewed by: <?php echo htmlspecialchars($review->getSellerName()); ?></div>
                        <div class="details"><?php echo nl2br(htmlspecialchars($review->getDetails())); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- Return Button -->
            <a href="seller_manage_review.php?username=<?php echo urlencode($_SESSION['username']); ?>" class="return-button">Return</a>
        </div>
        </body>
        </html>
        <?php
    }

    // Validation function for login status
    private function isUserLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Validation function for agent ID
    private function getAgentId()
    {
        return isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : null;
    }

    // Verification function for agent ID validity
    private function validateAgentId($agent_id)
    {
        return !is_null($agent_id) && $agent_id > 0;
    }
}


// Usage Example
$reviewEntity = new Review();
$controller = new ViewReviewController($reviewEntity);
$boundary = new ViewReviewPage($controller);
$boundary->ViewReviewUI();
?>

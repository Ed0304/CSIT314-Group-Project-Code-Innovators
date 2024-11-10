<?php
session_start();
require_once "../connectDatabase.php";

// Entity Layer
class Review
{
    private $details;
    private $stars;
    private $seller_name;
    private $date;

    public function __construct($details, $stars, $seller_name, $date)
    {
        $this->details = $details;
        $this->stars = $stars;
        $this->seller_name = $seller_name;
        $this->date = $date;
    }

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
}

// Controller Layer
class ReviewController
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function getReviewsByAgent($agent_id)
    {
        // Fetch all reviews for the specified agent
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

    public function getAgentName($agent_id)
    {
        // Fetch the agent's name for display
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

// Boundary Layer
class ReviewBoundary
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function render($agent_id)
    {
        // Get agent name
        $agent_name = $this->controller->getAgentName($agent_id);
        if (!$agent_name) {
            echo "<p>Agent not found or invalid ID.</p>";
            return;
        }

        // Get reviews for the specific agent
        $reviews = $this->controller->getReviewsByAgent($agent_id);

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Reviews for <?php echo htmlspecialchars($agent_name); ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                    display: flex;
                    justify-content: center;
                    padding: 20px;
                }
                .container {
                    max-width: 800px;
                    width: 100%;
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    text-align: center;
                    color: #333;
                }
                .review {
                    border-bottom: 1px solid #ddd;
                    padding: 10px 0;
                }
                .review:last-child {
                    border-bottom: none;
                }
                .review .stars {
                    color: #ffbb33;
                    font-size: 16px;
                }
                .review .date {
                    color: #888;
                    font-size: 14px;
                    margin-bottom: 5px;
                }
                .review .seller {
                    font-weight: bold;
                    font-size: 14px;
                }
                .review .details {
                    font-size: 16px;
                    line-height: 1.6;
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
        </div>
        </body>
        </html>
        <?php
    }
}

// Usage Example
$mysqli = new mysqli("localhost", "root", "", "csit314");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    echo "<p>Please log in to view agent reviews.</p>";
    exit();
}

$agent_id = isset($_GET['agent_id']) ? (int) $_GET['agent_id'] : null;
if (!$agent_id) {
    echo "<p>Invalid agent ID.</p>";
    exit();
}

$controller = new ReviewController($mysqli);
$boundary = new ReviewBoundary($controller);
$boundary->render($agent_id);

$mysqli->close();
?>

<?php
session_start();
require '../connectDatabase.php'; // Assuming you have a database connection class

// Entity Class: Review (Handles database queries)
class Review
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    // Method to fetch review by ID, including the connection logic
    public function getReviewById($review_id)
    {
        $stmt = $this->mysqli->prepare("SELECT r.review_id, r.review_details, r.review_stars, r.review_date,
                                       u.username AS reviewer_username, a.username AS agent_username
                                FROM review r
                                JOIN users u ON r.reviewer_id = u.user_id
                                JOIN users a ON r.agent_id = a.user_id
                                WHERE r.review_id = ?");
        $stmt->bind_param('i', $review_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Return review data as an associative array
            return [
                'review_id' => $row['review_id'],
                'details' => $row['review_details'],
                'stars' => $row['review_stars'],
                'date' => $row['review_date'],
                'reviewer_username' => $row['reviewer_username'],
                'agent_username' => $row['agent_username']
            ];
        }
        return null; // Return null if no review is found
    }
}

// Controller Class: ViewReviewController (Fetches data and passes to Boundary)
class ViewReviewController
{
    private $reviewModel;

    public function __construct($mysqli)
    {
        // Initialize the Review model
        $this->reviewModel = new Review($mysqli);
    }

    // Method to fetch review data
    public function getReviewData($review_id)
    {
        return $this->reviewModel->getReviewById($review_id);
    }
}

// Boundary Class: ViewReviewPage (Renders HTML view)
class ViewReviewPage
{
    public function BuyerViewReviewDetailsUI($reviewData)
    {
        if ($reviewData) {
            ?>
            <!DOCTYPE HTML>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Review Details</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Arial', sans-serif;
                        background-color: #f5f5f5;
                        padding: 2rem;
                        line-height: 1.6;
                    }

                    .container {
                        max-width: 800px;
                        margin: 0 auto;
                    }

                    .page-title {
                        text-align: center;
                        color: #333;
                        margin-bottom: 2rem;
                        font-size: 2.5rem;
                    }

                    .review-card {
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        padding: 2rem;
                        margin-bottom: 2rem;
                    }

                    .review-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 1.5rem;
                        padding-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }

                    .review-meta {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 1rem;
                        margin-bottom: 1.5rem;
                    }

                    .meta-item {
                        background: #f8f9fa;
                        padding: 1rem;
                        border-radius: 8px;
                    }

                    .meta-label {
                        color: #666;
                        font-size: 0.9rem;
                        margin-bottom: 0.3rem;
                    }

                    .meta-value {
                        color: #333;
                        font-weight: bold;
                    }

                    .stars-container {
                        display: flex;
                        gap: 0.25rem;
                        margin-bottom: 1.5rem;
                    }

                    .star {
                        width: 30px;
                        height: 30px;
                        transition: transform 0.2s;
                    }

                    .star:hover {
                        transform: scale(1.1);
                    }

                    .review-content {
                        background: #f8f9fa;
                        padding: 1.5rem;
                        border-radius: 8px;
                        margin-bottom: 1.5rem;
                    }

                    .review-text {
                        color: #444;
                        line-height: 1.8;
                    }

                    .button {
                        display: inline-block;
                        padding: 0.8rem 1.5rem;
                        font-size: 1rem;
                        color: white;
                        background-color: #007BFF;
                        border: none;
                        border-radius: 6px;
                        text-decoration: none;
                        text-align: center;
                        transition: all 0.3s ease;
                        cursor: pointer;
                    }

                    .button:hover {
                        background-color: #0056b3;
                        transform: translateY(-2px);
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }

                    .button-container {
                        text-align: center;
                    }
                </style>
            </head>

            <body>
                <div class="container">
                    <h1 class="page-title">Review Details</h1>

                    <div class="review-card">
                        <div class="review-header">
                            <div class="stars-container">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $reviewData['stars']) {
                                        echo "<img src='../star.png' alt='Filled Star' class='star'>";
                                    } else {
                                        echo "<img src='../empty-star.png' alt='Empty Star' class='star'>";
                                    }
                                }
                                ?>
                            </div>
                            <span class="meta-value">Review #<?php echo htmlspecialchars($reviewData['review_id']); ?></span>
                        </div>

                        <div class="review-meta">
                            <div class="meta-item">
                                <div class="meta-label">Reviewer</div>
                                <div class="meta-value"><?php echo htmlspecialchars($reviewData['reviewer_username']); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Agent</div>
                                <div class="meta-value"><?php echo htmlspecialchars($reviewData['agent_username']); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Date</div>
                                <div class="meta-value"><?php echo htmlspecialchars($reviewData['date']); ?></div>
                            </div>
                        </div>

                        <div class="review-content">
                            <p class="review-text"><?php echo htmlspecialchars($reviewData['details']); ?></p>
                        </div>

                        <div class="button-container">
                            <a href="buyerviewReviews.php?username=<?php echo urlencode($reviewData['agent_username']); ?>"
                                class="button">
                                Return to Reviews
                            </a>
                        </div>
                    </div>
                </div>
            </body>

            </html>
            <?php
        } else {
            echo "<p>No review found.</p>";
        }
    }

    // Handles the incoming HTTP request and manages the flow
    public function handleRequest($review_id)
    {
        if ($review_id) {
            $database = new Database();
            $mysqli = $database->getConnection();
            $controller = new ViewReviewController($mysqli);
            $reviewData = $controller->getReviewData($review_id); // Fetch review data from Review model
            $this->BuyerViewReviewDetailsUI($reviewData); // Render the review details in the boundary
            $database->closeConnection();
        } else {
            echo "<p>Review ID is missing.</p>";
        }
    }
}

// Entry Point
$review_id = isset($_GET['review_id']) ? (int) $_GET['review_id'] : null;
$boundary = new ViewReviewPage();
$boundary->handleRequest($review_id); // Handle the request and render the review
?>

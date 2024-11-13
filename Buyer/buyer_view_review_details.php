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

    public function buyerViewReview($review_id)
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
        if ($row = $result->fetch_assoc()) { //Representaton of a Review object
            return [
                'review_id' => $row['review_id'],
                'details' => $row['review_details'],
                'stars' => $row['review_stars'],
                'date' => $row['review_date'],
                'reviewer_username' => $row['reviewer_username'],
                'agent_username' => $row['agent_username']
            ];
        }
        return null;
    }
}

// Controller Class: BuyerViewReviewController (Handles data logic and passes to Boundary)
class BuyerViewReviewController
{
    private $reviewModel;
    private $viewData;

    public function __construct(Review $reviewModel)
    {
        $this->reviewModel = $reviewModel;
        $this->viewData = [];
    }

    public function buyerViewReview($review_id)
    {
        $review = $this->reviewModel->buyerViewReview($review_id);
        if ($review) {
            $this->viewData = [
                'review_id' => $review['review_id'],
                'details' => $review['details'],
                'stars' => $review['stars'],
                'date' => $review['date'],
                'reviewer_username' => $review['reviewer_username'],
                'agent_username' => $review['agent_username']
            ];
            return $review;
        }
    }

    public function getViewData()
    {
        return $this->viewData;
    }
}

// Boundary Class: BuyerViewReviewPage (Renders HTML view)
class BuyerViewReviewPage
{
    private $controller;

    public function __construct(BuyerViewReviewController $controller)
    {
        $this->controller = $controller;
    }

    public function BuyerViewReviewUI()
    {
        $data = $this->controller->getViewData(); // Get the prepared data from the controller


        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Review Details</title>
            <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Arial', sans-serif; background-color: #f5f5f5; padding: 2rem; line-height: 1.6; }
                    .container { max-width: 800px; margin: 0 auto; }
                    .page-title { text-align: center; color: #333; margin-bottom: 2rem; font-size: 2.5rem; }
                    .review-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 2rem; margin-bottom: 2rem; }
                    .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
                    .review-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
                    .meta-item { background: #f8f9fa; padding: 1rem; border-radius: 8px; }
                    .meta-label { color: #666; font-size: 0.9rem; margin-bottom: 0.3rem; }
                    .meta-value { color: #333; font-weight: bold; }
                    .stars-container { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; }
                    .star { width: 30px; height: 30px; transition: transform 0.2s; }
                    .star:hover { transform: scale(1.1); }
                    .review-content { background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
                    .review-text { color: #444; line-height: 1.8; }
                    .button { display: inline-block; padding: 0.8rem 1.5rem; font-size: 1rem; color: white; background-color: #007BFF; border: none; border-radius: 6px; text-decoration: none; text-align: center; transition: all 0.3s ease; cursor: pointer; }
                    .button:hover { background-color: #0056b3; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
                    .button-container { text-align: center; }
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
                                if ($i <= $data['stars']) {
                                    echo "<img src='../star.png' alt='Filled Star' class='star'>";
                                } else {
                                    echo "<img src='../empty-star.png' alt='Empty Star' class='star'>";
                                }
                            }
                            ?>
                        </div>
                        <span class="meta-value">Review #<?php echo htmlspecialchars($data['review_id']); ?></span>
                    </div>
                    <div class="review-meta">
                        <div class="meta-item">
                            <div class="meta-label">Reviewer</div>
                            <div class="meta-value"><?php echo htmlspecialchars($data['reviewer_username']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Agent</div>
                            <div class="meta-value"><?php echo htmlspecialchars($data['agent_username']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Date</div>
                            <div class="meta-value"><?php echo htmlspecialchars($data['date']); ?></div>
                        </div>
                    </div>
                    <div class="review-content">
                        <p class="review-text"><?php echo htmlspecialchars($data['details']); ?></p>
                    </div>
                    <div class="button-container">
                        <a href="buyerviewReviews.php?username=<?php echo urlencode($data['agent_username']); ?>" class="button">Return to Reviews</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    public function handleRequest()
    {
        $this->BuyerViewReviewUI();
    }
}

// Entry Point
$review_id = isset($_GET['review_id']) ? (int) $_GET['review_id'] : null;


$database = new Database();
$mysqli = $database->getConnection();

$reviewModel = new Review($mysqli);
$controller = new BuyerViewReviewController($reviewModel);
$controller->buyerViewReview($review_id); // Fetch data from the controller

$boundary = new BuyerViewReviewPage($controller);
$boundary->handleRequest(); // Render the UI

$database->closeConnection();


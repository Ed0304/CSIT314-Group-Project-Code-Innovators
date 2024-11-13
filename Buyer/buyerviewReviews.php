<?php
session_start();
require '../connectDatabase.php';

// Entity Class: Review
class Review
{
    private $details;
    private $stars;
    private $date;
    private $reviewerUsername;
    private $review_id;
    private $agentFirstName;
    private $agentLastName;

    private function getConnection()
    {
        $database = new Database();
        return $database->getConnection();
    }

    // Getters for properties
    public function getDetails() { return $this->details; }
    public function getStars() { return $this->stars; }
    public function getDate() { return $this->date; }
    public function getReviewerUsername() { return $this->reviewerUsername; }
    public function getReviewId() { return $this->review_id; }
    public function getAgentFirstName() { return $this->agentFirstName; }
    public function getAgentLastName() { return $this->agentLastName; }

    // Retrieve user ID by username
    public function getUserIdByUsername($username)
    {
        $mysqli = $this->getConnection();
        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
        if (!$stmt) return null;

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['user_id'];
        }
        return null;
    }

    // Retrieve reviews for a specific agent
    public function buyerViewAllReviews($agent_id)
    {
        $mysqli = $this->getConnection();
        $query = "SELECT r.review_id, r.review_details, r.review_stars, r.review_date, 
                         u.username, p.first_name, p.last_name
                  FROM review r
                  JOIN users u ON r.reviewer_id = u.user_id
                  JOIN profile p ON r.agent_id = p.user_id
                  WHERE r.agent_id = ?";
        $stmt = $mysqli->prepare($query);
        if (!$stmt) return [];

        $stmt->bind_param('i', $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $review = new self();
            $review->details = $row['review_details'];
            $review->stars = $row['review_stars'];
            $review->date = $row['review_date'];
            $review->reviewerUsername = $row['username'];
            $review->review_id = $row['review_id'];
            $review->agentFirstName = $row['first_name'];
            $review->agentLastName = $row['last_name'];
            $reviews[] = $review;
        }
        return $reviews;
    }
}

// Control Class: BuyerViewAllReviewsController
class BuyerViewAllReviewsController
{
    private $reviewEntity;
    private $reviews = [];
    private $agent_id;
    private $agentFirstName;
    private $agentLastName;

    public function __construct(Review $reviewEntity, $agent_id)
    {
        $this->reviewEntity = $reviewEntity;
        $this->agent_id = $agent_id;
        $this->buyerViewAllReviews($agent_id);
    }

    private function buyerViewAllReviews($agent_id)
    {
        $this->reviews = $this->reviewEntity->buyerViewAllReviews($this->agent_id);
        if (!empty($this->reviews)) {
            $this->agentFirstName = $this->reviews[0]->getAgentFirstName();
            $this->agentLastName = $this->reviews[0]->getAgentLastName();
        }
    }

    public function getReviews() { return $this->reviews; }
    public function getAgentId() { return $this->agent_id; }
    public function getAgentFirstName() { return $this->agentFirstName; }
    public function getAgentLastName() { return $this->agentLastName; }
}


// Boundary Class: BuyerViewAllReviewsPage
class BuyerViewAllReviewsPage
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function buyerViewAllReviewsUI()
    {
        $agent_id = $this->controller->getAgentId();
        $reviews = $this->controller->getReviews($agent_id);
        $agentFirstName = $this->controller->getAgentFirstName();
        $agentLastName = $this->controller->getAgentLastName(); 
        

        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Agent Ratings & Reviews</title>
        </head>
        <style>
                /* General body styling */
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f9f9f9;
                }

                /* Centering the content */
                h1 {
                    color: #333;
                    margin-top: 30px;
                    font-size: 2em;
                    text-align: center;
                }

                /* Styling the table */
                table {
                    width: 80%;
                    margin: 20px auto;
                    border-collapse: collapse;
                    background-color: #fff;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }

                th, td {
                    padding: 12px;
                    text-align: center;
                    border: 1px solid #ddd;
                }

                th {
                    background-color: #4CAF50;
                    color: white;
                }

                td {
                    background-color: #f2f2f2;
                }

                /* Styling the star images */
                .star {
                    width: 20px;
                    height: 20px;
                    vertical-align: middle;
                }

                /* Button styling */
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    margin: 10px 0;
                    background-color: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 1em;
                }

                .button:hover {
                    background-color: #45a049;
                }

                /* Styling for the "Create a review" button */
                .button {
                    margin-top: 20px;
                }

                /* Empty state message */
                p {
                    text-align: center;
                    font-size: 1.2em;
                    color: #888;
                }

                /* Styling for the return button */
                .button-return {
                    background-color: #2196F3;
                }

                .button-return:hover {
                    background-color: #1976D2;
                }

                /* Responsive design for smaller screens */
                @media (max-width: 768px) {
                    table {
                        width: 100%;
                        margin: 10px;
                    }

                    h1 {
                        font-size: 1.5em;
                    }

                    .button {
                        font-size: 0.9em;
                        padding: 8px 16px;
                    }
                }
            </style>
        <body>
            <h1 style="text-align:center">Ratings for <?php echo htmlspecialchars($agentFirstName . ' ' . $agentLastName); ?></h1>
            <div style="text-align:center">
                <a href="buyer_give_reviews.php?agent_id=<?php echo htmlspecialchars($agent_id); ?>" class="button">Create a review</a>
            </div>
            <?php if (!empty($reviews)) : ?>
                <table id='reviews-table'>
                    <tr><th>Ratings</th><th>Rated By:</th><th>Action</th></tr>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <img src='../<?php echo ($i <= $review->getStars()) ? 'star.png' : 'empty-star.png'; ?>' alt='<?php echo ($i <= $review->getStars()) ? 'Filled' : 'Empty'; ?> Star' class='star'>
                                <?php endfor; ?>
                            </td>
                            <td><?php echo htmlspecialchars($review->getReviewerUsername()); ?></td>
                            <td><a href='buyer_view_review_details.php?review_id=<?php echo htmlspecialchars($review->getReviewId()); ?>' class='button'>See Review Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No ratings or reviews found for this agent.</p>
            <?php endif; ?>
            <div style="text-align:center">
                <a href="buyer_view_agent_details.php?user_id=<?php echo htmlspecialchars($agent_id); ?>" class="button">Return</a>
            </div>
        </body>
        </html>
        <?php
    }
}

// Entry Point
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$reviewEntity = new Review();
$agent_id = isset($_GET['agent_id']) ? intval($_GET['agent_id']) : null;

$controller = new BuyerViewAllReviewsController($reviewEntity, $agent_id);
$view = new BuyerViewAllReviewsPage($controller);
$view->buyerViewAllReviewsUI();

?>

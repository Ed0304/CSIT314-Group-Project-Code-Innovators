<?php
session_start();
require_once "../connectDatabase.php";

// Entity Layer
class Review
{
    private $db;
    private $details;
    private $stars;
    private $reviewer_id;
    private $agent_id;
    private $date;

    public function __construct()
    {
        $this->db = new mysqli("mariadb", "root", "", "csit314");
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
        $this->date = date('Y-m-d H:i:s');
    }

    // Set up the review's data
    public function setReviewData($details, $stars, $reviewer_id, $agent_id)
    {
        $this->details = $details;
        $this->stars = $stars;
        $this->reviewer_id = $reviewer_id;
        $this->agent_id = $agent_id;
    }

    // CreateReview review to the database

    public function sellerCreateReview($details, $stars, $reviewer_id, $agent_id)
    {
        $stmt = $this->db->prepare("
            INSERT INTO review (review_details, review_stars, reviewer_id, agent_id, review_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siiss",
            $details,
            $stars,
            $reviewer_id,
            $agent_id,
            $this->date
        );

        return $stmt->execute();
    }

    public function getAgentDetails($agent_id)
    {
        $stmt = $this->db->prepare("
            SELECT user_id, username 
            FROM users 
            WHERE user_id = ? AND role_id = 2
        ");
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

    public function closeConnection()
    {
        $this->db->close();
    }
}

// Controller Layer
class CreateReviewController
{
    private $review;

    public function __construct($review)
    {
        $this->review = $review;
    }

    // Process review creation
    public function sellerCreateReview($details, $stars, $reviewer_id, $agent_id)
    {
        return $this->review->sellerCreateReview($details, $stars, $reviewer_id, $agent_id);
    }

    // Retrieve agent details
    public function getAgentDetails($agent_id)
    {
        return $this->review->getAgentDetails($agent_id);
    }
}

// Boundary Layer
class CreateReviewBoundary
{
    private $controller;
    private $agent_details;
    private $is_success;
    private $message;

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->agent_details = null;
        $this->is_success = true;
        $this->message = '';
    }

    public function processRequest($request_method, $get_data, $post_data, $session_data)
    {
        if (!isset($session_data['user_id'])) {
            $this->is_success = false;
            $this->message = 'Please log in to submit a review';
            return $this->CreateReviewUI();
        }

        $agent_id = isset($get_data['agent_id']) ? (int) $get_data['agent_id'] : null;
        if (!$agent_id) {
            $this->is_success = false;
            $this->message = 'Invalid agent ID';
            return $this->CreateReviewUI();
        }

        $this->agent_details = $this->controller->getAgentDetails($agent_id);
        if (!$this->agent_details) {
            $this->is_success = false;
            $this->message = 'Agent not found';
            return $this->CreateReviewUI();
        }

        // Inside processRequest() method in the Boundary layer
if ($request_method === 'POST') {
    if (!$this->validateInput($post_data)) {
        $this->is_success = false;
        $this->message = 'Invalid input data';
        return $this->CreateReviewUI();
    }

    $details = $post_data['details'];
    $stars = $post_data['stars'];
    $reviewer_id = $session_data['user_id'];
    $agent_id = $post_data['agent_id'];

    $this->is_success = $this->controller->sellerCreateReview($details, $stars, $reviewer_id, $agent_id);
    $this->message = $this->is_success ? 'Review submitted successfully' : 'Failed to submit review';
    return $this->CreateReviewUI();
}

        $this->CreateReviewUI();
    }
    private function validateInput($post_data)
        {
            if (empty($post_data['details']) || strlen($post_data['details']) > 1000) {
                return false;
            }
            if (!isset($post_data['stars']) || !is_numeric($post_data['stars']) || $post_data['stars'] < 1 || $post_data['stars'] > 5) {
                return false;
            }
            if (empty($post_data['agent_id'])) {
                return false;
            }

            return true;
        }


    // Validation and other methods remain the same
    public function CreateReviewUI()
    {
        ob_start();
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Create Review</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }

                .container {
                    width: 100%;
                    max-width: 600px;
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }

                .form-title {
                    text-align: center;
                    font-size: 24px;
                    margin-bottom: 20px;
                }

                .error-message,
                .success-message {
                    background: #ffdddd;
                    padding: 10px;
                    margin-bottom: 15px;
                    color: #d8000c;
                    border-radius: 5px;
                }

                .success-message {
                    background: #ddffdd;
                    color: #4caf50;
                }

                .agent-info h2 {
                    font-size: 18px;
                    margin-bottom: 10px;
                }

                .form-group {
                    margin-bottom: 15px;
                }

                .form-group label {
                    display: block;
                    font-weight: bold;
                    margin-bottom: 5px;
                }

                .star-rating {
                    display: flex;
                    flex-direction: row-reverse;
                    justify-content: flex-end;
                    font-size: 24px;
                }

                .star-rating input {
                    display: none;
                }

                .star-rating label {
                    color: #ddd;
                    cursor: pointer;
                    padding: 5px;
                }

                .star-rating input:checked~label {
                    color: #ffbb33;
                }

                .star-rating label:hover,
                .star-rating label:hover~label {
                    color: #ffbb33;
                }

                .form-control {
                    width: 100%;
                    padding: 8px;
                    border-radius: 4px;
                    border: 1px solid #ccc;
                    resize: vertical;
                }

                .btn {
                    width: 100%;
                    background: #4caf50;
                    color: #fff;
                    padding: 10px;
                    border: none;
                    border-radius: 5px;
                    font-size: 16px;
                    cursor: pointer;
                }

                .btn:hover {
                    background: #45a049;
                }

                .return-btn {
                    background-color: #6c757d;
                    color: white;
                    padding: 10px;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                    display: block;
                    text-align: center;
                }

                .return-btn:hover {
                    background-color: #5a6268;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1 class="form-title">Write a Review</h1>
                <?php if ($this->message): ?>
                    <div class="message <?php echo $this->is_success ? 'success' : 'error'; ?>">
                        <p><?php echo htmlspecialchars($this->message); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($this->agent_details): ?>
                    <div class="agent-info">
                        <h2>Reviewing Agent: <?php echo htmlspecialchars($this->agent_details['username']); ?></h2>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="agent_id" value="<?php echo htmlspecialchars($this->agent_details['user_id']); ?>">
                        <div class="form-group">
                            <label>Rating</label>
                            <div class="star-rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="stars" value="<?php echo $i; ?>" required>
                                    <label for="star<?php echo $i; ?>">★</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="details">Review Details</label>
                            <textarea id="details" name="details" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn">Submit Review</button>
                    </form>
                <?php endif; ?>

                <?php if ($this->agent_details): ?>
                    <!-- Button to return to seller_manage_review.php with username -->
                    <a href="seller_manage_review.php?username=<?php echo htmlspecialchars($this->agent_details['username']); ?>" class="return-btn">Return to Manage Reviews</a>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        ob_end_flush();
    }
}

// Usage
$review = new Review();
$controller = new CreateReviewController($review);
$boundary = new CreateReviewBoundary($controller);
$boundary->processRequest($_SERVER['REQUEST_METHOD'], $_GET, $_POST, $_SESSION);
$review->closeConnection();
?>

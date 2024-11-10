<?php
session_start();
require_once "../connectDatabase.php";

// Entity Layer: Manages database connection and interactions
class Review
{
    private $db;
    private $details;
    private $stars;
    private $reviewer_id;
    private $agent_id;
    private $date;

    // Constructor initializes the database connection
    public function __construct()
    {
        $this->db = new mysqli("localhost", "root", "", "csit314");
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
        $this->date = date('Y-m-d H:i:s');
    }

    // Set review details
    public function setReviewDetails($details, $stars, $reviewer_id, $agent_id)
    {
        $this->details = $details;
        $this->stars = $stars;
        $this->reviewer_id = $reviewer_id;
        $this->agent_id = $agent_id;
    }

    // Persist review to the database
    public function save()
    {
        $stmt = $this->db->prepare("
            INSERT INTO review (review_details, review_stars, reviewer_id, agent_id, review_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siiss",
            $this->details,
            $this->stars,
            $this->reviewer_id,
            $this->agent_id,
            $this->date
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Review submitted successfully'];
        }
        return ['success' => false, 'errors' => ['Database error occurred']];
    }

    // Retrieve agent details
    public function getAgentDetails($agent_id)
    {
        $stmt = $this->db->prepare("
            SELECT user_id, username 
            FROM users 
            WHERE user_id = ? AND role_id = 2
        ");
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Close the database connection
    public function closeConnection()
    {
        $this->db->close();
    }
}

// Controller Layer: Interacts with the Entity layer
class CreateReviewController
{
    private $review;

    public function __construct($review)
    {
        $this->review = $review;
    }

    // Process review creation (delegates to entity layer for persistence)
    public function processCreateReview($post_data, $reviewer_id)
    {
        $this->review->setReviewDetails(
            $post_data['details'],
            $post_data['stars'],
            $reviewer_id,
            $post_data['agent_id']
        );

        return $this->review->save();
    }

    // Retrieve agent details
    public function getAgentDetails($agent_id)
    {
        return $this->review->getAgentDetails($agent_id);
    }
}

//Boundary layer
class CreateReviewBoundary
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    // Main entry point for the boundary
    public function processRequest($request_method, $get_data, $post_data, $session_data)
    {
        if (!isset($session_data['user_id'])) {
            return $this->render(null, ['Please log in to submit a review']);
        }

        $agent_id = isset($get_data['agent_id']) ? (int) $get_data['agent_id'] : null;
        if (!$agent_id) {
            return $this->render(null, ['Invalid agent ID']);
        }

        $agent_details = $this->controller->getAgentDetails($agent_id);
        if (!$agent_details) {
            return $this->render(null, ['Agent not found']);
        }

        if ($request_method === 'POST') {
            // Perform validation in the boundary layer
            $validation_result = $this->validateInput($post_data);
            if (!$validation_result['success']) {
                return $this->render($agent_details, $validation_result['errors']);
            }

            // Process the review creation
            $result = $this->controller->processCreateReview($post_data, $session_data['user_id']);
            return $this->render(
                $agent_details,
                $result['success'] ? [] : $result['errors'],
                $result['success'] ? $result['message'] : ''
            );
        }

        return $this->render($agent_details);
    }

    // Input validation moved to boundary layer
    private function validateInput($post_data)
    {
        $errors = [];

        // Validate review details
        if (empty($post_data['details'])) {
            $errors[] = "Review details cannot be empty";
        }
        if (strlen($post_data['details']) > 1000) {
            $errors[] = "Review details cannot exceed 1000 characters";
        }

        // Validate stars rating
        if (!isset($post_data['stars']) || !is_numeric($post_data['stars']) || $post_data['stars'] < 1 || $post_data['stars'] > 5) {
            $errors[] = "Rating must be between 1 and 5 stars";
        }

        // Check if agent ID is provided
        if (empty($post_data['agent_id'])) {
            $errors[] = "Agent ID is required";
        }

        return ['success' => empty($errors), 'errors' => $errors];
    }


    // Render view
    public function render($agent_details = null, $errors = [], $success_message = '')
    {
        // Start output buffering
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
            </style>
        </head>

        <body>
            <div class="container">
                <h1 class="form-title">Write a Review</h1>
                <?php if ($errors): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <div class="success-message">
                        <p><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($agent_details): ?>
                    <div class="agent-info">
                        <h2>Reviewing Agent: <?php echo htmlspecialchars($agent_details['username']); ?></h2>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="agent_id" value="<?php echo htmlspecialchars($agent_details['user_id']); ?>">
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
<?php
session_start();
require_once "connectDatabase.php";
// Entity Layer
class Review {
    private $review_id;
    private $details;
    private $stars;
    private $reviewer_id;
    private $agent_id;
    private $date;

    // Constructor only accepts raw data
    public function __construct($details, $stars, $reviewer_id, $agent_id) {
        $this->details = $details;
        $this->stars = $stars;
        $this->reviewer_id = $reviewer_id;
        $this->agent_id = $agent_id;
        $this->date = date('Y-m-d H:i:s');
    }

    // Getters - Entity only provides data access
    public function getDetails() { return $this->details; }
    public function getStars() { return $this->stars; }
    public function getReviewerId() { return $this->reviewer_id; }
    public function getAgentId() { return $this->agent_id; }
    public function getDate() { return $this->date; }

    // Entity-level validation
    public function validate() {
        $errors = [];
        if (empty($this->details)) {
            $errors[] = "Review details cannot be empty";
        }
        if (strlen($this->details) > 1000) {
            $errors[] = "Review details cannot exceed 1000 characters";
        }
        if (!is_numeric($this->stars) || $this->stars < 1 || $this->stars > 5) {
            $errors[] = "Rating must be between 1 and 5 stars";
        }
        return $errors;
    }
}

// Control Layer
class CreateReviewController {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    // Main control method that orchestrates the review creation process
    public function processCreateReview($post_data, $reviewer_id) {
        // Input validation
        if (!$this->validateInput($post_data)) {
            return ['success' => false, 'errors' => ['Invalid input data']];
        }

        // Create review entity
        $review = new Review(
            $post_data['details'],
            $post_data['stars'],
            $reviewer_id,
            $post_data['agent_id']
        );

        // Entity validation
        $errors = $review->validate();
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Persist review
        return $this->saveReview($review);
    }

    // Input validation
    private function validateInput($post_data) {
        return isset($post_data['details']) && 
               isset($post_data['stars']) && 
               isset($post_data['agent_id']);
    }

    // Data persistence
    private function saveReview(Review $review) {
        $stmt = $this->mysqli->prepare(
            "INSERT INTO review (review_details, review_stars, reviewer_id, agent_id, review_date) 
             VALUES (?, ?, ?, ?, ?)"
        );

        // Store return values in temporary variables
        $details = $review->getDetails();
        $stars = $review->getStars();
        $reviewer_id = $review->getReviewerId();
        $agent_id = $review->getAgentId();
        $date = $review->getDate();

        $stmt->bind_param(
            "siiss",
            $details,
            $stars,
            $reviewer_id,
            $agent_id,
            $date
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Review submitted successfully'];
        }
        return ['success' => false, 'errors' => ['Database error occurred']];
    }

    // Agent data retrieval
    public function getAgentDetails($agent_id) {
        $stmt = $this->mysqli->prepare(
            "SELECT user_id, username 
             FROM users 
             WHERE user_id = ? AND role_id = 2"
        );
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

// Boundary Layer
class CreateReviewBoundary {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    // Main entry point for the boundary
    public function processRequest($request_method, $get_data, $post_data, $session_data) {
        // Authentication check
        if (!isset($session_data['user_id'])) {
            return $this->render(null, ['Please log in to submit a review']);
        }

        // Get agent_id from URL parameter
        $agent_id = isset($get_data['agent_id']) ? (int)$get_data['agent_id'] : null;
        if (!$agent_id) {
            return $this->render(null, ['Invalid agent ID']);
        }

        // Get agent details
        $agent_details = $this->controller->getAgentDetails($agent_id);
        if (!$agent_details) {
            return $this->render(null, ['Agent not found']);
        }

        // Handle form submission
        if ($request_method === 'POST') {
            $result = $this->controller->processCreateReview($post_data, $session_data['user_id']);
            return $this->render($agent_details, 
                               $result['success'] ? [] : $result['errors'],
                               $result['success'] ? $result['message'] : '');
        }

        // Display initial form
        return $this->render($agent_details);
    }

    // Render view
    public function render($agent_details = null, $errors = [], $success_message = '') {
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
                body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .container { width: 100%; max-width: 600px; background: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .form-title { text-align: center; font-size: 24px; margin-bottom: 20px; }
                .error-message, .success-message { background: #ffdddd; padding: 10px; margin-bottom: 15px; color: #d8000c; border-radius: 5px; }
                .success-message { background: #ddffdd; color: #4caf50; }
                .agent-info h2 { font-size: 18px; margin-bottom: 10px; }
                .form-group { margin-bottom: 15px; }
                .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
                .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; font-size: 24px; }
                .star-rating input { display: none; }
                .star-rating label { color: #ddd; cursor: pointer; padding: 5px; }
                .star-rating input:checked ~ label { color: #ffbb33; }
                .star-rating label:hover, .star-rating label:hover ~ label { color: #ffbb33; }
                .form-control { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; resize: vertical; }
                .btn { width: 100%; background: #4caf50; color: #fff; padding: 10px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
                .btn:hover { background: #45a049; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1 class="form-title">Write a Review</h1>

                <?php if (!empty($errors)): ?>
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
        // End output buffering and flush output
        ob_end_flush();
    }
}

// Usage Example
$mysqli = new mysqli("localhost", "root", "", "csit314");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$controller = new CreateReviewController($mysqli);
$boundary = new CreateReviewBoundary($controller);
$boundary->processRequest($_SERVER['REQUEST_METHOD'], $_GET, $_POST, $_SESSION);

$mysqli->close();
?>

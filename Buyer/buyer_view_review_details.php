<?php
session_start();

require 'connectDatabase.php'; // Assuming you have a database connection class

// Entity Class: Review
class Review {
    private $review_id;
    private $details;
    private $stars;
    private $date;
    private $reviewerUsername;
    private $agentUsername; // New property for agent username

    public function __construct($review_id, $details, $stars, $date, $reviewerUsername, $agentUsername) {
        $this->review_id = $review_id;
        $this->details = $details;
        $this->stars = $stars;
        $this->date = $date;
        $this->reviewerUsername = $reviewerUsername;
        $this->agentUsername = $agentUsername; // Initialize agent username
    }

    public function getReviewId() {
        return $this->review_id;
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

    public function getAgentUsername() { // New method for agent username
        return $this->agentUsername;
    }
}

// Control Class: ViewReviewController
class ViewReviewController {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getReviewById($review_id) {
        // Updated SQL query to include agent username
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
            return new Review($row['review_id'], $row['review_details'], $row['review_stars'], $row['review_date'],
                              $row['reviewer_username'], $row['agent_username']); // Pass agent username
        }
        return null; // Return null if no review is found
    }
}

// Boundary Class: ViewReviewBoundary
// Boundary Class: ViewReviewBoundary
class ViewReviewBoundary {
    public function render(Review $review, $username) {
        if ($review) {
            ?>
            <!DOCTYPE HTML>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Review Details</title>
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
                        text-align: center;
                    }
                    .star {
                        width: 25px;
                        height: 25px;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        font-size: 24px;
                        color: white;
                        background-color: #007BFF; /* Bootstrap primary color */
                        border: none;
                        border-radius: 5px;
                        text-decoration: none; /* Remove underline */
                        text-align: center;
                        transition: background-color 0.3s;
                    }
                    .button:hover {
                        background-color: #0056b3; /* Darker shade on hover */
                    }
                </style>
            </head>
            <body>
                <h1 style="text-align:center">Review Details</h1>
                <table id='review-table'>
                    <tr>
                        <th>Review ID</th>
                        <th>Reviewer Name</th>
                        <th>Agent Name</th> <!-- New column for agent name -->
                        <th>Review Stars</th>
                        <th>Review Details</th>
                    </tr>
                    <tr>
                        <td><?php echo htmlspecialchars($review->getReviewId()); ?></td>
                        <td><?php echo htmlspecialchars($review->getReviewerUsername()); ?></td>
                        <td><?php echo htmlspecialchars($review->getAgentUsername()); ?></td> <!-- Display agent username -->
                        <td>
                            <?php
                            // Display stars
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review->getStars()) {
                                    echo "<img src='star.png' alt='Filled Star' class='star'>";
                                } else {
                                    echo "<img src='empty-star.png' alt='Empty Star' class='star'>";
                                }
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($review->getDetails()); ?></td>
                    </tr>
                </table>
                <div style="text-align:center; margin-top: 20px;">
                    <a href="buyerviewReviews.php?username=<?php echo urlencode($review->getAgentUsername()); ?>" class="button">Return</a>
                </div>
            </body>
            </html>
            <?php
        } else {
            echo "<p>No review found.</p>";
        }
    }
}



// Entry Point
$database = new Database();
$mysqli = $database->getConnection();

$review_id = isset($_GET['review_id']) ? (int)$_GET['review_id'] : null;

if ($review_id) {
    $controller = new ViewReviewController($mysqli);
    $review = $controller->getReviewById($review_id); // Get the review by ID
    $username = isset($_GET['username']) ? $_GET['username'] : ''; // Get the username from the query string

    $view = new ViewReviewBoundary();
    $view->render($review, $username); // Render the review details, including the username
} else {
    echo "<p>Review ID is missing.</p>";
}

$database->closeConnection();
?>

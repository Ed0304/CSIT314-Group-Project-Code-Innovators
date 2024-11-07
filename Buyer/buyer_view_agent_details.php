<?php
require "../connectDatabase.php";
session_start();

// Retrieve user_id from GET parameters
if (!isset($_GET['user_id'])) {
    echo "Agent ID not provided.";
    exit;
}

$user_id = $_GET['user_id'];

// Entity class representing the Agent
class Agent
{
    private $user_id;
    private $first_name;
    private $last_name;
    private $about;
    private $profile_image;
    private $status_id;
    private $email;
    private $phone;
    private $username; // Add username property

    public function __construct($user_id, $first_name, $last_name, $about, $profile_image, $status_id, $email, $phone, $username)
    {
        $this->user_id = $user_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->about = $about;
        $this->profile_image = $profile_image;
        $this->status_id = $status_id;
        $this->email = $email;
        $this->phone = $phone;
        $this->username = $username; // Initialize username
    }

    // Getters for Agent properties
    public function getUserID()
    {
        return $this->user_id;
    }
    public function getFirstName()
    {
        return $this->first_name;
    }
    public function getLastName()
    {
        return $this->last_name;
    }
    public function getAbout()
    {
        return $this->about;
    }
    public function getProfileImage()
    {
        return $this->profile_image;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPhone()
    {
        return $this->phone;
    }
    public function getUsername()
    {
        return $this->username;
    } // Getter for username
}

// Controller class to handle fetching agent details
class viewAgentController
{
    private $mysqli;
    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }
    public function getAgentDetails($user_id)
    {
        $query = "
            SELECT p.user_id, p.first_name, p.last_name, p.about, p.profile_image, p.status_id, u.email, u.phone_num, u.username
            FROM profile p
            JOIN users u ON p.user_id = u.user_id
            WHERE p.user_id = ?
        ";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($user_id, $first_name, $last_name, $about, $profile_image, $status_id, $email, $phone, $username);
        if ($stmt->fetch()) {
            return new Agent($user_id, $first_name, $last_name, $about, $profile_image, $status_id, $email, $phone, $username);
        }
        return null; // No agent found
    }
}

// Boundary class to present agent details to the buyer
class viewAgentBoundary
{
    public function render($agent)
    {
        $referrer = isset($_GET['referrer']) ? $_GET['referrer'] : (isset($_POST['referrer']) ? $_POST['referrer'] : 'dashboard');

        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Agent Details</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f9fa;
                    margin: 0;
                    padding: 20px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }
                .container {
                    width: 100%;
                    max-width: 600px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                }
                h2 {
                    text-align: center;
                    color: #343a40;
                    font-size: 1.5em;
                }
                p {
                    color: #495057;
                    font-size: 1em;
                    line-height: 1.6;
                }
                .profile-image {
                    display: block;
                    margin: 0 auto;
                    border-radius: 50%;
                    width: 120px;
                    height: 120px;
                    object-fit: cover;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                .contact-info {
                    margin-top: 20px;
                    display: flex;
                    justify-content: space-between;
                }
                .contact-info p {
                    margin: 0;
                }
                .buttons {
                    margin-top: 20px;
                    display: flex;
                    justify-content: space-between;
                }
                .buttons a {
                    text-decoration: none;
                    padding: 10px 20px;
                    color: #fff;
                    border-radius: 5px;
                    font-size: 0.9em;
                    text-align: center;
                }
                .back-button {
                    background-color: #6c757d;
                }
                .reviews-button {
                    background-color: #007bff;
                }
            </style>
        </head>
        <body>
        <div class='container'>";
        if ($agent) {
            echo "<h2>Agent Details</h2>";
            // Check if the agent has a profile image
            if ($agent->getProfileImage()) {
                $imageData = base64_encode($agent->getProfileImage());
                echo "<img src='data:image/jpeg;base64,$imageData' alt='Profile Image' class='profile-image' />";
            } else {
                // Display a default profile image
                echo "<img src='default-profile.jpg' alt='Default Profile Image' class='profile-image' />";
            }
            echo "<p><strong>Name:</strong> " . $agent->getFirstName() . " " . $agent->getLastName() . "</p>";
            echo "<p><strong>About:</strong> " . $agent->getAbout() . "</p>";
            echo "<div class='contact-info'>";
            echo "<p><strong>Email:</strong> " . $agent->getEmail() . "</p>";
            echo "<p><strong>Phone:</strong> " . $agent->getPhone() . "</p>";
            echo "</div>";
            // Buttons for navigating back and viewing reviews
            echo "<div class='buttons'>";
            echo "<a href='" . ($referrer === 'shortlist' ? 'buyer_view_shortlist.php' : 'buyer_dashboard.php') . "' class='back-button'>Return</a>";
            echo "<a href='buyerviewReviews.php?username=" . $agent->getUsername() . "' class='reviews-button'>View Reviews</a>";
            echo "</div>";
        } else {
            echo "<p>Agent details not found.</p>";
        }
        echo "</div></body></html>";
    }
}

// Main script logic
$mysqli = new mysqli("localhost", "root", "", "csit314");
$controller = new viewAgentController($mysqli);
$agent = $controller->getAgentDetails($user_id);
$boundary = new viewAgentBoundary();
$boundary->render($agent);
$mysqli->close();
?>
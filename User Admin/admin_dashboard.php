<?php
session_start(); // Start the session
require '../connectDatabase.php';

// Entity Layer
class User {
    private $username;

    public function __construct($username) {
        $this->username = htmlspecialchars($username); // Sanitize username input
    }

    public function getUsername() {
        return $this->username;
    }
}

// Boundary Layer
class DashboardView {
    private $controller;
    private $username;

    public function __construct($controller) {
        $this->controller = $controller;
        $this->username = ''; // Initial state; no user set until initialized
    }

    public function setUsername($username) {
        $this->username = htmlspecialchars($username); // Sanitize when setting username
    }

    public function handleRequest() {
        // Ensure the user is logged in
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        $username = $_SESSION['username']; // Retrieve the username from the session
        $user = new User($username); // Create a User entity
        $this->setUsername($user->getUsername()); // Set the username in the view

        // Process form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleFormSubmission();
        }

        // Render the dashboard view
        $this->render();
    }

    private function handleFormSubmission() {
        if (isset($_POST['userAcc'])) {
            $this->redirectTo('admin_manage_user_acc.php');
        } elseif (isset($_POST['userProfile'])) {
            $this->redirectTo('admin_manage_user_profiles.php');
        } elseif (isset($_POST['logout'])) {
            $this->redirectTo('../logout.php');
        }
    }

    private function render() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Dashboard</title>
            <style>
                .header { text-align: center; }
                .headDiv { text-align: center; background-color: green; border-bottom: 2px solid black; }
                .formBody { text-align: center; }
                #logout, #userAcc, #userProfile { font-size: 18px; }
                .mainInterface { text-align: center; background-color: white; border: 1px solid black; padding: 10px; }
            </style>
        </head>
        <body>
            <div class="headDiv">
                <h1 class="header">Welcome to the Admin Dashboard, <?php echo $this->username; ?>!</h1>
                <h2 class="header">What would you like to do for today?</h2>
            </div>

            <div class="mainInterface">
                <form method="post" class="formBody">
                    <br/><br/>
                    <button type="submit" id="userAcc" name="userAcc">Manage user accounts</button>
                    <br/><br/>
                    <button type="submit" id="userProfile" name="userProfile">Manage user profiles</button>
                    <br/><br/>
                    <input type="submit" id="logout" value="Logout" name="logout">
                    <br/><br/>
                </form>
            </div>
        </body>
        </html>
        <?php
    }

    private function redirectTo($location) {
        header("Location: $location");
        exit();
    }
}

// Control Layer
class DashboardController {
    public function initView() {
        // Initialize the view and let the boundary handle the request
        $view = new DashboardView($this);
        $view->handleRequest(); // Boundary initiates the request handling
    }
}

// Main logic: Instantiate the controller and initialize the view
$controller = new DashboardController();
$controller->initView();
?>

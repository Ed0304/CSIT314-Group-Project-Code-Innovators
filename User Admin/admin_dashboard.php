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

// Control Layer
class DashboardController {
    public function getUsernameFromSession() {
        return $_SESSION['username'] ?? null;
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

    public function handleRequest() {
        // Ensure the user is logged in
        $username = $this->controller->getUsernameFromSession();
        if (!$username) {
            header("Location: login.php");
            exit();
        }

        // Create a User entity and set the username in the view
        $user = new User($username);
        $this->setUsername($user->getUsername());

        // Process form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleFormSubmission();
        }

        // Render the dashboard view
        $this->render();
    }

    public function setUsername($username) {
        $this->username = htmlspecialchars($username); // Sanitize when setting username
    }

    private function handleFormSubmission() {
        if (isset($_POST['userAcc'])) {
            $this->redirectTo('admin_view_account.php');
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
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }

                .header {
                    text-align: center;
                    color: white;
                }

                .headDiv {
                    text-align: center;
                    background-color: #28a745;
                    border-bottom: 3px solid #003366;
                    padding: 20px;
                }

                .formBody {
                    text-align: center;
                    margin-top: 20px;
                }

                button, input[type="submit"] {
                    font-size: 18px;
                    padding: 12px 20px;
                    border: none;
                    background-color: #28a745;
                    color: white;
                    cursor: pointer;
                    border-radius: 5px;
                    margin: 10px 0;
                    width: 100%; /* Makes the buttons span full width */
                    transition: background-color 0.3s ease;
                }

                button:hover, input[type="submit"]:hover {
                    background-color: #218838;
                }

                .mainInterface {
                    text-align: center;
                    background-color: white;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 20px;
                    margin: 20px auto;
                    width: 50%;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .logout-button {
                    background-color: #dc3545;
                    border-radius: 5px;
                    transition: background-color 0.3s ease;
                }

                .logout-button:hover {
                    background-color: #c82333;
                }

                h1, h2 {
                    margin: 0;
                    padding: 0;
                }

                p {
                    font-size: 18px;
                    color: #333;
                }
            </style>
        </head>
        <body>
            <div class="headDiv">
                <h1>Welcome to the Admin Dashboard, <?php echo $this->username; ?>!</h1>
                <h2>What would you like to do for today?</h2>
            </div>

            <div class="mainInterface">
                <form method="post" class="formBody">
                    <button type="submit" name="userAcc">Manage user accounts</button>
                    <button type="submit" name="userProfile">Manage user profiles</button>
                    <input type="submit" class="logout-button" value="Logout" name="logout">
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

// Main logic: Instantiate the boundary and controller
$controller = new DashboardController();
$dashboardView = new DashboardView($controller);
$dashboardView->handleRequest();
?>

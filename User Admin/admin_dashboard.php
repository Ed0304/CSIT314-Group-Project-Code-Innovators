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
                body {
                    font-family: 'Arial', sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f6f9;
                    color: #333;
                }

                .header {
                    text-align: center;
                    margin-top: 50px;
                    color: #fff;
                    font-size: 1.8em;
                }

                .headDiv {
                    background-color: #28a745;
                    padding: 20px;
                    border-bottom: 2px solid #333;
                }

                .formBody {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 20px;
                }

                button, input[type="submit"] {
                    background-color: #007bff;
                    color: white;
                    border: none;
                    padding: 15px 30px;
                    margin: 10px 0;
                    border-radius: 5px;
                    font-size: 1em;
                    cursor: pointer;
                    width: 80%;
                    transition: background-color 0.3s;
                }

                button:hover, input[type="submit"]:hover {
                    background-color: #0056b3;
                }

                .logout-button {
                    background-color: #dc3545 !important;
                    color: white;
                    width: 80%;
                    transition: background-color 0.3s ease;
                }

                .logout-button:hover {
                    background-color: #c82333 !important;
                }


                .mainInterface {
                    text-align: center;
                    background-color: #fff;
                    border: 1px solid #ddd;
                    padding: 20px;
                    margin-top: 50px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                    width: 50%;
                    margin-left: auto;
                    margin-right: auto;
                }

                h2 {
                    color: #333;
                    text-align: center;
                    margin-bottom: 20px;
                }

                @media (max-width: 768px) {
                    .mainInterface {
                        width: 80%;
                    }

                    .formBody button {
                        width: 90%;
                    }
                }
            </style>
        </head>
        <body>
            <div class="headDiv">
                <h1 class="header">Welcome to the Admin Dashboard, <?php echo $this->username; ?>!</h1>
                <h2>What would you like to do today?</h2>
            </div>

            <div class="mainInterface">
                <form method="post" class="formBody">
                    <button type="submit" name="userAcc">Manage User Accounts</button>
                    <button type="submit" name="userProfile">Manage User Profiles</button>
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

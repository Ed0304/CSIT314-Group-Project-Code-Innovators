<?php
session_start(); // Start the session

// Entity Layer
class UserAccount {
    private $username;

    public function __construct($username) {
        $this->username = $username;
    }

    public function getUserAccountname() {
        return $this->username;
    }
}

// Boundary Layer
class LogoutPage {
    public function initiateLogout() {
        // Check if the user is logged in before proceeding
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        $username = $_SESSION['username'];
        
        // Create a UserAccount entity and instantiate the controller
        $user = new UserAccount($username);
        $controller = new LogoutController($user);

        // Start the logout process
        $controller->logout();
    }

    public function LogoutUI() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Logout Confirmation</title>
        </head>
        <body>
            <h1 style="text-align:center">You have been successfully logged out.</h1>
            <!-- Back to Login button -->
            <form method="post" action="login.php" style="text-align:center">
                <br/>
                <input type="submit" value="Return to Login" style="font-size: 24px">
            </form>
        </body>
        </html>
        <?php
    }
}

// Control Layer
class LogoutController {
    private $view;
    private $user;

    public function __construct(UserAccount $user) {
        $this->user = $user;
        $this->view = new LogoutPage();
    }

    public function logout() {
        // render the logout view
        $this->view->LogoutUI();

        // Clear user-related session data to avoid undefined key issues
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
    }
}

// Main Logic
$LogoutPage = new LogoutPage();
$LogoutPage->initiateLogout();
?>

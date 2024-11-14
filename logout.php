<?php
session_start(); // Start the session

// Boundary Layer
class LogoutPage {
    public function LogoutUI() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Logout Confirmation</title>
            <link rel="stylesheet" href="./style.css">
        </head>
        <body>
            <div class="center-container">
                <h1>You have been successfully logged out.</h1>
                <!-- Back to Login button -->
                <form method="post" action="login.php" class="return-button-form">
                    <input type="submit" value="Return to Login" class="return-btn">
                </form>
            </div>
        </body>
        </html>
        <?php
    }
}

// Control Layer
class LogoutController {
    private $view;

    public function __construct() {
        $this->view = new LogoutPage();
    }

    public function terminateSession() {
        // Render the logout view
        $this->view->LogoutUI();

        // Clear user-related session data
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session

        // Indicate successful logout
        return true;
    }
}

// Main Logic
$controller = new LogoutController();
$logoutSuccessful = $controller->terminateSession();
?>

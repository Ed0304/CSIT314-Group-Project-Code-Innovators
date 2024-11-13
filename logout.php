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

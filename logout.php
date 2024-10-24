<?php
session_start(); // Start the session

// Entity Layer
class User {
    private $username;

    public function __construct($username) {
        $this->username = htmlspecialchars($username);
    }

    public function getUsername() {
        return $this->username;
    }
}

// Boundary Layer
class LogoutView {
    private $username;

    public function __construct(User $user) {
        $this->username = $user->getUsername();
    }

    public function render() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Logout Confirmation</title>
        </head>
        <body>
            <h1 style="text-align:center">Goodbye, <?php echo $this->username; ?>. You have been logged out.</h1>
            <!-- Back to Login button -->
            <form method="post" action="" style="text-align:center">
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

    public function __construct(User $user) {
        $this->view = new LogoutView($user);
    }

    public function logout() {
        // Render the logout view before session destruction
        $this->view->render();

        // Destroy the session after rendering
        session_destroy();
    }
}

// Main logic
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // Retrieve the username from the session

// Create a User entity
$user = new User($username);

// Instantiate the controller and handle the logout
$controller = new LogoutController($user);
$controller->logout();
?>

<?php
session_start(); // Start the session

// Entity Layer: User represents the user entity
class User {
    private $username;
    private $isLoggedIn;

    public function __construct($username) {
        $this->username = $username;
        $this->isLoggedIn = isset($_SESSION['username']);
    }

    public function getUsername() {
        return $this->username;
    }

    public function isLoggedIn() {
        return $this->isLoggedIn;
    }
}

// Entity Layer: SessionEntity manages session data and operations
class SessionEntity {
    public function clearSessionData() {
        // Unset all session variables
        $_SESSION = [];
    }

    public function destroySession() {
        // Destroy the session
        session_destroy();
    }
}

// Control Layer: SessionController handles session logic
class SessionController {
    private $sessionEntity;
    private $user;

    public function __construct() {
        $this->sessionEntity = new SessionEntity();

        // Assuming the user's session contains their username
        if (isset($_SESSION['username'])) {
            $this->user = new User($_SESSION['username']);
        } else {
            $this->user = null; // No user is logged in
        }
    }

    public function logout() {
        if ($this->user && $this->user->isLoggedIn()) {
            // Clear session for the logged-in user
            $this->sessionEntity->clearSessionData();
            $this->sessionEntity->destroySession();
        }
    }

    public function getUser() {
        return $this->user;
    }
}

// Boundary Layer: LogoutBoundary manages the logout process and redirection
class LogoutBoundary {
    private $sessionController;

    public function __construct() {
        $this->sessionController = new SessionController();
    }

    public function handleLogout() {
        // Handle session destruction
        $this->sessionController->logout();
        
        // Display logout message
        $this->displayLogoutMessage();
    }

    public function displayLogoutMessage() {
        $user = $this->sessionController->getUser();
        $username = $user ? $user->getUsername() : 'Guest';

        // This method serves the boundary layer responsibility by displaying the logout message
        echo '
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <title>Logout Confirmation</title>
        </head>
        <body>
            <h1 style="text-align:center">Goodbye, ' . htmlspecialchars($username) . '. You have been logged out.</h1>
            
            <!-- Back to Login button -->
            <form method="post" action="login.php" style="text-align:center">
                <br/>
                <input type="submit" value="Return to Login" style="font-size: 24px">
            </form>
        </body>
        </html>';
    }
}

// Create an instance of LogoutBoundary and handle the logout
$logoutBoundary = new LogoutBoundary();
$logoutBoundary->handleLogout();

?>

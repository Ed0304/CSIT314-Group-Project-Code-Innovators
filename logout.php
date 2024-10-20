<?php
session_start(); // Start the session

class SessionController {
    public function destroySession() {
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();
    }

    public function redirectToLogin() {
        // Redirect to the login page
        header("Location: login.php");
        exit();
    }
}

class LogoutBoundary {
    private $sessionController;

    public function __construct() {
        $this->sessionController = new SessionController();
    }

    public function handleLogout() {
        // Handle the session destruction and redirection
        $this->sessionController->destroySession();
        $this->sessionController->redirectToLogin();
    }
}

// Create an instance of LogoutBoundary and handle the logout
$logoutBoundary = new LogoutBoundary();
$logoutBoundary->handleLogout();
?>

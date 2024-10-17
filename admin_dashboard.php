<?php
session_start(); // Start the session

// Entity layer
class User {
    private $username;

    public function __construct($username) {
        $this->username = htmlspecialchars($username);
    }

    public function getUsername() {
        return $this->username;
    }
}

// Boundary layer
class DashboardView {
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
}

// Control layer
class DashboardController {
    private $view;

    public function __construct(User $user) {
        $this->view = new DashboardView($user);
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['userAcc'])) {
                header("Location: admin_manage_user_acc.php");
                exit();
            }

            if (isset($_POST['userProfile'])) {
                header("Location: admin_manage_user_profiles.php");
                exit();
            }

            if (isset($_POST['logout'])) {
                header("Location: logout.php");
                exit();
            }
        }

        // Render the dashboard view if no button is clicked
        $this->view->render();
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

// Instantiate the controller and handle the request
$controller = new DashboardController($user);
$controller->handleRequest();
?>

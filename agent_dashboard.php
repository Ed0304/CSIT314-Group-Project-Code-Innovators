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
            <title>Used Car Dashboard</title>
            <style>
                .header { text-align: center; }
                .headDiv { text-align: center; background-color: green; border-bottom: 2px solid black; }
                .formBody { text-align: center; }
                #logout, #create, #view, #update, #delete, #manageProfile{ font-size: 18px; }
                .mainInterface { text-align: center; background-color: white; border: 1px solid black; padding: 10px; }
            </style>
        </head>
        <body>
            <div class="headDiv">
                <h1 class="header">Welcome to the Used Car Agent Dashboard, <?php echo $this->username; ?>!</h1>
                <h2 class="header">What would you like to do for today?</h2>
            </div>
            <div class="mainInterface">
                <form method="post" class="formBody">
                    <button type ="submit" id="manageProfile" name="manageProfile">View/Update profile </button>
                    <br/><br/>
                    <button type="submit" id="create" name="create">Create new listings</button>
                    <br/><br/>
                    <button type="submit" id="view" name="view">View my listings</button>
                    <br/><br/>
                    <button type="submit" id="update" name="update">Update existing listings</button>
                    <br/><br/>
                    <button type="submit" id="delete" name="delete">Delete existing listings</button>
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
            if (isset($_POST['manageProfile'])) {
                header("Location: agent_manage_profile.php");
                exit();
            }

            if (isset($_POST['create'])) {
                header("Location: agent_create_listings.php");
                exit();
            }

            if (isset($_POST['view'])) {
                header("Location: agent_view_listings.php");
                exit();
            }

            if (isset($_POST['update'])) {
                header("Location: agent_update_listings.php");
                exit();
            }

            if (isset($_POST['delete'])) {
                header("Location: agent_delete_listings.php");
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

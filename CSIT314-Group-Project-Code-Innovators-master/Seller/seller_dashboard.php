<?php
session_start();

// Entity layer
class Seller {
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

    public function setUsername($username) { // Method to set username
        $this->username = $username;
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
                #logout, #create, #view, #update, #delete, #manageProfile, #reviews { font-size: 18px; }
                .mainInterface { text-align: center; background-color: white; border: 1px solid black; padding: 10px; }
            </style>
        </head>
        <body>
            <div class="headDiv">
                <h1 class="header">Welcome to the seller Dashboard, <?php echo $this->username; ?>!</h1>
                <h2 class="header">What would you like to do for today?</h2>
            </div>
            <div class="mainInterface">
                <form method="post" class="formBody">
                    <button type="submit" id="manageProfile" name="manageProfile">View/Update profile</button>
                    <br/><br/>
                    <button type="submit" id="view" name="view">Manage my listings</button>
                    <br/><br/>
                    <button type="submit" id="reviews" name="reviews">Manage reviews and ratings</button>
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
    private $seller;

    public function __construct(Seller $seller) {
        $this->seller = $seller;
        $this->view = new DashboardView();
        $this->view->setUsername($this->seller->getUsername()); // Set the username in the view
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['manageProfile'])) {
                header("Location: seller_manage_profile.php");
                exit();
            }

            if (isset($_POST['view'])) {
                $username = urlencode($this->seller->getUsername());
                header("Location: seller_view_listings.php?username=" . $username);
                exit();
            }

            if (isset($_POST['logout'])) {
                header("Location: ../logout.php");
                exit();
            }

            if (isset($_POST['reviews'])) {
                $username = urlencode($this->seller->getUsername());
                header("Location: seller_view_ratings_and_reviews.php?username=" . $username);
                exit();
            }
        }

        // Render the view without parameters
        $this->view->render();
    }
}

// Main logic
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$seller = new Seller($username);
$controller = new DashboardController($seller);
$controller->handleRequest();

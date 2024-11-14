<?php
session_start();

// Entity layer
class UserAccount {
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

                #logout {
                    background-color: #dc3545;
                }

                #logout:hover {
                    background-color: #c82333;
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
                <h1 class="header">Welcome to the Used Car Agent Dashboard, <?php echo $this->username; ?>!</h1>
                <h2>What would you like to do today?</h2>
            </div>
            <div class="mainInterface">
                <form method="post" class="formBody">
                    <button type="submit" id="manageProfile" name="manageProfile">View/Update Profile</button>
                    <button type="submit" id="view" name="view">View My Listings</button>
                    <button type="submit" id="reviews" name="reviews">See My Reviews and Ratings</button>
                    <input type="submit" id="logout" value="Logout" name="logout">
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
    private $agent;

    public function __construct(UserAccount $agent) {
        $this->agent = $agent;
        $this->view = new DashboardView();
        $this->view->setUsername($this->agent->getUsername()); // Set the username in the view
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['manageProfile'])) {
                header("Location: agent_manage_profile.php");
                exit();
            }

            if (isset($_POST['view'])) {
                $username = urlencode($this->agent->getUsername());
                header("Location: agent_view_listings.php?username=" . $username);
                exit();
            }

            if (isset($_POST['logout'])) {
                header("Location: ../logout.php");
                exit();
            }

            if (isset($_POST['reviews'])) {
                $username = urlencode($this->agent->getUsername());
                header("Location: agent_view_ratings_and_reviews.php?username=" . $username);
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
$agent = new UserAccount($username);
$controller = new DashboardController($agent);
$controller->handleRequest();
?>

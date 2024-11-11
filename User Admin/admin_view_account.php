<?php
session_start();
require '../connectDatabase.php';

// ENTITY: Represents user data and database retrieval
class UserAccount {
    private $pdo;

    public function __construct() {
        $this->connectDatabase();
    }

    private function connectDatabase() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getProfileByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT u.user_id, u.username, u.password, r.role_name, u.email, u.phone_num, 
            s.status_name, p.first_name, p.last_name, p.about, p.profile_image
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON s.status_id = u.status_id
            LEFT JOIN profile p ON u.user_id = p.user_id
            WHERE u.username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


// CONTROL LAYER: Manages data flow between boundary and entity layers
class ViewUserAccountController {
    private $userAccountModel;

    public function __construct($userAccountModel) {
        $this->userAccountModel = $userAccountModel;
    }

    public function getProfile($username) {
        return $this->userAccountModel->getProfileByUsername($username);
    }
}

// BOUNDARY LAYER: Handles user interactions and rendering user information
class ViewUserAccountPage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function handleRequest() {
        // Check if user is logged in
        if (!isset($_SESSION['username'])) {
            header("Location: ../login.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } else {
            $username = $_GET['username'] ?? '';
            if ($username) {
                $profileData = $this->controller->getProfile($username);
                $this->ViewUserAccountUI($profileData);
            }
        }
    }

    private function handlePostRequest() {
        if (isset($_POST['action'])) {
            $username = $_POST['username'];
            switch ($_POST['action']) {
                case 'return':
                    header("Location: admin_manage_user_acc.php");
                    exit();
                case 'update':
                    header("Location: admin_update_user_acc.php?username=" . urlencode($username));
                    exit();
                case 'suspend':
                    header("Location: admin_suspend_user_acc.php?username=" . urlencode($username));
                    exit();
            }
        }
    }

    public function ViewUserAccountUI($profileData) {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <style>
            #infoTable th, td {
                font-size: 24px;
                text-align: center;
            }
            #infoTable {
                margin: auto;
            }
        </style>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Information</title>
        </head>
        <body>
            <h1 style="text-align: center">Account Information</h1>
            <table id="infoTable">
                <tr>
                    <td><strong>Profile Image</strong></td>
                    <td colspan="2">
                        <?php
                        if (!empty($profileData['profile_image'])) {
                            // Assuming profile_image is stored as a BLOB, display it as base64
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($profileData['profile_image']) . '" alt="Profile Image" style="max-width: 200px; max-height: 200px;" />';
                        } else {
                            echo 'No profile image available.';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Full Name</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['first_name'] .' '.htmlspecialchars($profileData['last_name'] ?? '')); ?></td>
                </tr>
                <tr>
                <tr>
                    <td><strong>About</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['about'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Username</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['username'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Password</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['password'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Role</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['role_name'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['email'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Phone Number</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['phone_num'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($profileData['status_name']); ?></td>
                </tr>                
                <tr>
                    <td><br/></td><td><br/></td>
                </tr>
                <tr>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($profileData['username'] ?? ''); ?>">
                            <button type="submit" name="action" value="return" style="font-size: 24px">Return to accounts list</button>
                        </form>
                    </td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($profileData['username'] ?? ''); ?>">
                            <button type="submit" name="action" value="update" style="font-size: 24px">Update account information</button>
                        </form>
                    </td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($profileData['username'] ?? ''); ?>">
                            <button type="submit" name="action" value="suspend" style="font-size: 24px">Suspend this account</button>
                        </form>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
    }
    
}

// Main logic: Initialization and request handling
$userAccountEntity = new UserAccount();
$controller = new ViewUserAccountController($userAccountEntity);
$view = new ViewUserAccountPage($controller);
$view->handleRequest();
?>

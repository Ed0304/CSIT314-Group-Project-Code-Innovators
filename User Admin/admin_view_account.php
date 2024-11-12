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

    public function viewUserAccountByUsername($username) {
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

    public function viewUserAccount($username) {
        return $this->userAccountModel->viewUserAccountByUsername($username);
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
                $accountData = $this->controller->viewUserAccount($username);
                $this->ViewUserAccountUI($accountData);
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

    public function ViewUserAccountUI($accountData) {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Information</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }

                h1 {
                    font-size: 36px;
                    color: #333;
                    text-align: center;
                    margin-top: 20px;
                }

                .container {
                    width: 80%;
                    margin: auto;
                    background-color: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                }

                table {
                    width: 100%;
                    margin-bottom: 20px;
                    border-collapse: collapse;
                }

                th, td {
                    padding: 10px;
                    text-align: left;
                    font-size: 18px;
                    border-bottom: 1px solid #ddd;
                }

                td {
                    text-align: center;
                }

                .button-container {
                    text-align: center;
                    margin-top: 20px;
                }

                .action-button {
                    background-color: #007bff;
                    color: white;
                    padding: 12px 20px;
                    margin: 5px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 18px;
                    text-decoration: none;
                }

                .action-button:hover {
                    background-color: #0056b3;
                }

                .return-button {
                    background-color: #28a745;
                }

                .return-button:hover {
                    background-color: #218838;
                }

                .action-button.suspend {
                    background-color: #dc3545;
                }

                .action-button.suspend:hover {
                    background-color: #c82333;
                }

                img {
                    max-width: 200px;
                    max-height: 200px;
                    border-radius: 50%;
                }

                .profile-info {
                    margin-top: 10px;
                    font-size: 20px;
                    color: #555;
                }

                .profile-info strong {
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Account Information</h1>
                <table>
                    <tr>
                        <td><strong>Profile Image</strong></td>
                        <td colspan="2">
                            <?php
                            if (!empty($accountData['profile_image'])) {
                                // Assuming profile_image is stored as a BLOB, display it as base64
                                echo '<img src="data:image/jpeg;base64,' . base64_encode($accountData['profile_image']) . '" alt="Profile Image" />';
                            } else {
                                echo 'No profile image available.';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Full Name</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['first_name'] . ' ' . htmlspecialchars($accountData['last_name'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>About</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['about'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Username</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['username'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Password</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['password'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['role_name'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['email'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Phone Number</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['phone_num'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($accountData['status_name']); ?></td>
                    </tr>
                </table>

                <div class="button-container">
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($accountData['username']); ?>">
                        <button type="submit" name="action" value="return" class="action-button return-button">Return to accounts list</button>
                    </form>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($accountData['username']); ?>">
                        <button type="submit" name="action" value="update" class="action-button">Update account information</button>
                    </form>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($accountData['username']); ?>">
                        <button type="submit" name="action" value="suspend" class="action-button suspend">Suspend this account</button>
                    </form>
                </div>
            </div>
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

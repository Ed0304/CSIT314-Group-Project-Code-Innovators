<?php
session_start();

// BOUNDARY LAYER: Responsible for rendering user information and handling requests
class SuspendUserAccountPage {
    private $accountController;
    private $accountData;

    public function __construct() {
        $this->accountController = new SuspendUserAccountController();
    }

    public function handleRequest() {
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $username = $_POST['username'];
            $action = $_POST['action'];

            if ($action === 'suspend') {
                $success = $this->accountController->suspendUserAccount($username);
                $_SESSION['message'] = $success ? "User account suspended successfully." : "Failed to suspend user account.";
            } elseif ($action === 'Remove') {
                $success = $this->accountController->unsuspendUserAccount($username);
                $_SESSION['message'] = $success ? "User account suspension removed." : "Failed to remove suspension.";
            }

            echo "<script>
                alert('" . htmlspecialchars($_SESSION['message']) . "');
                window.location.href = 'admin_manage_user_acc.php';
            </script>";
            exit();
        }

        $username = isset($_GET['username']) ? $_GET['username'] : '';
        if ($username) {
            $this->accountData = $this->accountController->getUserAccount($username);
        } else {
            echo "No username provided.";
        }
    }

    private function getUsername() {
        return $this->accountData['username'] ?? '';
    }

    private function getPassword() {
        return $this->accountData['password'] ?? '';
    }

    private function getRoleName() {
        return $this->accountData['role_name'] ?? '';
    }

    private function getEmail() {
        return $this->accountData['email'] ?? '';
    }

    private function getPhoneNumber() {
        return $this->accountData['phone_num'] ?? '';
    }

    private function getStatusName() {
        return $this->accountData['status_name'] ?? '';
    }

    public function SuspendUserAccountUI() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Suspend Confirmation</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }

                h1 {
                    text-align: center;
                    margin-top: 20px;
                    font-size: 36px;
                }

                #infoTable {
                    margin: 50px auto;
                    border-collapse: collapse;
                    width: 80%;
                    background-color: white;
                    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                }

                #infoTable th, td {
                    font-size: 20px;
                    text-align: left;
                    padding: 12px;
                    border: 1px solid #ddd;
                }

                #infoTable td {
                    background-color: #f9f9f9;
                }

                #infoTable th {
                    background-color: #4CAF50;
                    color: white;
                }

                form {
                    display: inline-block;
                    margin: 20px 0;
                }

                button {
                    font-size: 20px;
                    padding: 10px 20px;
                    border-radius: 5px;
                    border: none;
                    cursor: pointer;
                    margin: 0 10px;
                    background-color: #007BFF;
                    color: white;
                }

                button:hover {
                    background-color: #0056b3;
                }

                .return-btn {
                    background-color: #4CAF50;
                }

                .return-btn:hover {
                    background-color: #45a049;
                }

                .form-body {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
            </style>
        </head>
        <body>
            <h1>Suspend this account?</h1>
            <table id="infoTable">
                <tr>
                    <td><strong>Username</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->getUsername()); ?></td>
                </tr>
                <tr>
                    <td><strong>Password</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->getPassword()); ?></td>
                </tr>
                <tr>
                    <td><strong>Role</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->getRoleName()); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->getEmail()); ?></td>
                </tr>
                <tr>
                    <td><strong>Phone Number</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->getPhoneNumber()); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->getStatusName()); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="height: 20px;"></td>
                </tr>
                <tr>
                    <td>
                        <form action="" method="POST" class="form-body"> 
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->getUsername()); ?>">
                            <input type="hidden" name="action" value="suspend">
                            <button type="submit">Suspend</button>
                        </form>
                    </td>
                    <td>
                        <form action="" method="POST" class="form-body"> 
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->getUsername()); ?>">
                            <input type="hidden" name="action" value="Remove">
                            <button type="submit">Remove Suspension</button>
                        </form>
                    </td>
                    <td>
                        <form action="admin_manage_user_acc.php" class="form-body">
                            <button type="submit" class="return-btn">Return</button>
                        </form>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
    }
}


// CONTROL LAYER: Serves as an intermediary between view and entity
class SuspendUserAccountController {
    private $userAccountModel;

    public function __construct() {
        $this->userAccountModel = new UserAccount();
    }

    public function getUserAccount($username) {
        return $this->userAccountModel->getUserAccountByUsername($username); //Returns the user account
    }

    public function suspendUserAccount($username) {
        return $this->userAccountModel->suspendUserAccount($username); //Returns true/false
    }

    public function unsuspendUserAccount($username) {
        return $this->userAccountModel->unsuspendUserAccount($username); //Returns true/false
    }
}


// ENTITY: Handles all logic for user data and database interactions
class UserAccount {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getUserAccountByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT u.username, u.password, r.role_name, u.email, u.phone_num, s.status_name
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON s.status_id = u.status_id
            WHERE u.username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function suspendUserAccount($username) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 2 WHERE username = :username");
        $stmt->bindParam(':username', $username);
        return $stmt->execute(); //return true after successfully updated database
    }

    public function unsuspendUserAccount($username) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 1 WHERE username = :username");
        $stmt->bindParam(':username', $username);
        return $stmt->execute(); //return true after successfully updated database
    }
}


// Now instantiate and handle the request in the Boundary layer
$page = new SuspendUserAccountPage();
$page->handleRequest();
$page->SuspendUserAccountUI();
?>

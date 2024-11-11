<?php
session_start();

// Boundary Layer: SuspendUserAccountPage class for handling form display and user interaction
class SuspendUserAccountPage {
    private $accountController;
    private $profileData;

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
                $success = $this->accountController->setSuspend($username);
                $_SESSION['message'] = $success ? "User account suspended successfully." : "Failed to suspend user account.";
            } elseif ($action === 'Remove') {
                $success = $this->accountController->setRemoveSuspend($username);
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
            $this->profileData = $this->accountController->getProfile($username);
        } else {
            echo "No username provided.";
        }
    }

    private function getUsername() {
        return $this->profileData['username'] ?? '';
    }

    private function getPassword() {
        return $this->profileData['password'] ?? '';
    }

    private function getRoleName() {
        return $this->profileData['role_name'] ?? '';
    }

    private function getEmail() {
        return $this->profileData['email'] ?? '';
    }

    private function getPhoneNumber() {
        return $this->profileData['phone_num'] ?? '';
    }

    private function getStatusName() {
        return $this->profileData['status_name'] ?? '';
    }

    public function SuspendUserAccountUI() {
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
            <title>Suspend Confirmation</title>
        </head>
        <body>
            <h1 style="text-align: center">Suspend this account?</h1>
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
                    <td><br/></td>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>
                    <form action="" method="POST" class="form-body"> 
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->getUsername()); ?>">
                        <input type="hidden" name="action" value="suspend">
                        <button type="submit" style="font-size: 24px">Suspend</button>
                    </form>
                    </td>
                    <td>
                    <form action="" method="POST" class="form-body"> 
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->getUsername()); ?>">
                        <input type="hidden" name="action" value="Remove">
                        <button type="submit" style="font-size: 24px">Remove Suspension</button>
                    </form>
                    </td>
                    <td>
                        <form action="admin_manage_user_acc.php" class="form-body">
                            <button type="submit" style="font-size: 24px; margin-left: 20px;">Return</button>
                        </form>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
    }
}

// Control Layer: SuspendUserAccountController class for managing data flow between boundary and entity layers
class SuspendUserAccountController {
    private $userAccountModel;

    public function __construct() {
        $this->userAccountModel = new UserAccount();
    }

    public function getProfile($username) {
        return $this->userAccountModel->getProfileByUsername($username);
    }

    public function setSuspend($username) {
        return $this->userAccountModel->Suspend($username);
    }

    public function setRemoveSuspend($username) {
        return $this->userAccountModel->RemoveSuspend($username);
    }
}

// Entity Layer: UserAccount class for interacting with the database
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

    public function getProfileByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT u.username, u.password, r.role_name, u.email, u.phone_num, s.status_name
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON s.status_id = u.status_id
            WHERE u.username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function Suspend($username) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 2 WHERE username = :username");
        $stmt->bindParam(':username', $username);
        return $stmt->execute(); //return true after successfully updated database
    }

    public function RemoveSuspend($username) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 1 WHERE username = :username");
        $stmt->bindParam(':username', $username);
        return $stmt->execute(); //return true after successfully updated database
    }
}

// Global Layer: Initializing the components
$page = new SuspendUserAccountPage();
$page->handleRequest();
$page->SuspendUserAccountUI();
?>
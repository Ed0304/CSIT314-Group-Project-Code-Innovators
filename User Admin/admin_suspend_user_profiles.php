<?php
session_start();

// BOUNDARY LAYER: Responsible for rendering user information and handling requests
class ProfileView {
    private $profileData;

    public function __construct($profileData) {
        $this->profileData = $profileData;
    }

    public function render() {
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
            <h1 style="text-align: center">Suspend this role?</h1>
            <table id="infoTable">
                <tr>
                    <td><strong>Profile</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['role_name'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Number of Accounts</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['account_count'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['status_name']); ?></td>
                </tr>
                <tr>
                    <td><br/></td>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>
                    <form action="" method="POST" class="form-body"> 
                        <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->profileData['role_id']); ?>">
                        <input type="hidden" name="action" value="suspend">
                        <button type="submit" style="font-size: 24px">Suspend</button>
                    </form>
                    </td>
                    <td>
                    <form action="" method="POST" class="form-body"> 
                        <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->profileData['role_id']); ?>">
                        <input type="hidden" name="action" value="Remove">
                        <button type="submit" style="font-size: 24px">Remove Suspension</button>
                    </form>
                    </td>
                    <td>
                        <form action="admin_manage_user_profiles.php" class="form-body">
                            <button type="submit" style="font-size: 24px; margin-left: 20px;">Return</button>
                        </form>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
    }

    public function handleRequest() {
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        // Use GET parameter to fetch the role_id
        $role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $role_id = $_POST['role_id'] ?? '';

            if ($action === 'suspend') {
                $accountController = new AccountController();
                $accountController->setSuspend($role_id);

                // Prompt message before redirecting
                $_SESSION['success_message'] = "Profile suspended successfully.";
                echo "<script>
                    alert('" . htmlspecialchars($_SESSION['success_message']) . "');
                    window.location.href = 'admin_manage_user_profiles.php';
                </script>";
                exit();
            }

            if ($action === 'Remove') {
                $accountController = new AccountController();
                $accountController->setRemoveSuspend($role_id);

                // Prompt message before redirecting
                $_SESSION['removeSuspend_message'] = "Profile suspension removed.";
                echo "<script>
                    alert('" . htmlspecialchars($_SESSION['removeSuspend_message']) . "');
                    window.location.href = 'admin_manage_user_profiles.php';
                </script>";
                exit();
            }
        }

        if ($role_id) {
            $accountController = new AccountController();
            $profileData = $accountController->getProfile($role_id);
            $this->profileData = $profileData;
            $this->render();
        } else {
            echo "No profile provided.";
        }
    }
}

// CONTROL LAYER: Serves as an intermediary between view and entity
class AccountController {
    private $userAccountModel;

    public function __construct() {
        $this->userAccountModel = new UserAccount();
    }

    public function getProfile($role_id) {
        return $this->userAccountModel->getProfileByRole($role_id);
    }

    public function setSuspend($role_id) {
        return $this->userAccountModel->Suspend($role_id);
    }

    public function setRemoveSuspend($role_id) {
        return $this->userAccountModel->RemoveSuspend($role_id);
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

    public function getProfileByRole($role_id) {
        $stmt = $this->pdo->prepare("SELECT r.role_id, r.role_name, s.status_name, COUNT(*) AS account_count
            FROM users u
            JOIN role r ON r.role_id = u.role_id
            JOIN status s ON s.status_id = u.status_id
            WHERE u.role_id = :role_id
            GROUP BY u.role_id
            ORDER BY u.role_id ASC");

        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function Suspend($role_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 2 WHERE role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();
    }

    public function RemoveSuspend($role_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 1 WHERE role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();
    }
}

// MAIN EXECUTION: Initialize and handle the request in the Boundary layer
$profileView = new ProfileView([]);
$profileView->handleRequest();
?>

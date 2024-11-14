<?php
session_start();

// BOUNDARY LAYER: Responsible for rendering user information and handling requests
class SuspendUserProfilePage {
    private $controller;
    private $profileData; // Store profile data internally

    public function __construct($controller) {
        $this->controller = $controller;
    }

    // This method will output the CSS styles for the page
    private function renderStyles() {
        ?>
        <style>
            body {
                font-family: Arial, sans-serif;
            }
            #infoTable th, td {
                font-size: 24px;
                text-align: center;
                padding: 10px;
            }
            #infoTable {
                margin: auto;
                border-collapse: collapse;
            }
            #infoTable th {
                background-color: #f2f2f2;
            }
            button {
                font-size: 24px;
                padding: 10px 20px;
                margin: 10px;
                cursor: pointer;
                border: none;
                border-radius: 5px;
            }
            /* Style for the Suspend and Remove Suspension buttons (blue) */
            .suspend-btn, .remove-suspend-btn {
                background-color: #007bff;
                color: white;
            }
            .suspend-btn:hover, .remove-suspend-btn:hover {
                background-color: #0056b3;
            }
            /* Style for the Return button (green) */
            .return-btn {
                background-color: #28a745;
                color: white;
            }
            .return-btn:hover {
                background-color: #218838;
            }
            .form-body {
                text-align: center;
            }
            h1 {
                text-align: center;
                color: #333;
            }
        </style>
        <?php
    }

    public function SuspendUserProfileUI() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Suspend Confirmation</title>
            <?php $this->renderStyles(); ?> <!-- Call the renderStyles method to inject CSS -->
        </head>
        <body>
            <h1>Suspend this role?</h1>
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
                            <button type="submit" class="suspend-btn">Suspend</button>
                        </form>
                    </td>
                    <td>
                        <form action="" method="POST" class="form-body"> 
                            <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->profileData['role_id']); ?>">
                            <input type="hidden" name="action" value="Remove">
                            <button type="submit" class="remove-suspend-btn">Remove Suspension</button>
                        </form>
                    </td>
                    <td>
                        <form action="admin_manage_user_profiles.php" class="form-body">
                            <button type="submit" class="return-btn">Return</button>
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
        $role_id = $_GET['role_id'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $role_id = $_POST['role_id'] ?? '';

            if ($action === 'suspend') {
                $this->controller->suspendUserProfile($role_id);
                $_SESSION['success_message'] = "Profile suspended successfully.";
                echo "<script>
                    alert('" . htmlspecialchars($_SESSION['success_message']) . "');
                    window.location.href = 'admin_manage_user_profiles.php';
                </script>";
                exit();
            }

            if ($action === 'Remove') {
                $this->controller->setRemoveSuspend($role_id);
                $_SESSION['removeSuspend_message'] = "Profile suspension removed.";
                echo "<script>
                    alert('" . htmlspecialchars($_SESSION['removeSuspend_message']) . "');
                    window.location.href = 'admin_manage_user_profiles.php';
                </script>";
                exit();
            }
        }

        if ($role_id) {
            // Retrieve profile data and store it in the property
            $this->profileData = $this->controller->getProfile($role_id);
            $this->SuspendUserProfileUI();
        } else {
            echo "No profile provided.";
        }
    }
}

// CONTROL LAYER: Serves as an intermediary between view and entity
class SuspendUserProfileController {
    private $userAccountModel;

    public function __construct() {
        $this->userAccountModel = new UserProfile();
    }

    public function getProfile($role_id) {
        return $this->userAccountModel->getProfileByRole($role_id);
    }

    public function suspendUserProfile($role_id) {
        return $this->userAccountModel->suspendUserProfile($role_id);
    }

    public function setRemoveSuspend($role_id) {
        return $this->userAccountModel->removeSuspend($role_id);
    }
}

// ENTITY: Handles all logic for user data and database interactions
class UserProfile {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');
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

    public function suspendUserProfile($role_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 2 WHERE role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();
    }

    public function removeSuspend($role_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 1 WHERE role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();
    }
}

// MAIN EXECUTION: Initialize and handle the request in the Boundary layer
$accountController = new SuspendUserProfileController();
$profileView = new SuspendUserProfilePage($accountController);
$profileView->handleRequest();
?>

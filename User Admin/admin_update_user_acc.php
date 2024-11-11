<?php
require '../connectDatabase.php';

// Entity Layer: UserAccount class for interacting with the database
class UserAccount {
    private $conn;
    public $user_id;
    public $username;
    public $password;
    public $role_id;
    public $email;
    public $phone_num;
    public $status_id;

    public function __construct($userData = null) {
        // Initialize database connection
        $database = new Database();
        $this->conn = $database->getConnection();

        // If user data is provided, initialize properties
        if ($userData) {
            $this->user_id = $userData['user_id'];
            $this->username = $userData['username'];
            $this->password = $userData['password'];
            $this->role_id = $userData['role_id'];
            $this->email = $userData['email'];
            $this->phone_num = $userData['phone_num'];
            $this->status_id = $userData['status_id'];
        }
    }

    public function getUserDetails($username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();

        if ($userData) {
            $this->user_id = $userData['user_id'];
            $this->username = $userData['username'];
            $this->password = $userData['password'];
            $this->role_id = $userData['role_id'];
            $this->email = $userData['email'];
            $this->phone_num = $userData['phone_num'];
            $this->status_id = $userData['status_id'];
            return $this;
        }
        return null;
    }

    public function updateUser($data) {
        $stmt = $this->conn->prepare(
            "UPDATE users SET username = ?, password = ?, role_id = ?, email = ?, phone_num = ?, status_id = ? WHERE user_id = ?"
        );
        $stmt->bind_param(
            "ssissii",
            $data['username'],
            $data['password'],
            $data['role_id'],
            $data['email'],
            $data['phone_num'],
            $data['status_id'],
            $data['user_id']
        );
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// Control Layer: UpdateUserProfileController class for managing data flow between boundary and entity layers
class UpdateUserAccountController {
    private $useraccount;

    public function __construct() {
        $this->useraccount = new UserAccount();
    }

    public function getUserAccount($username) {
        return $this->useraccount->getUserDetails($username);
    }

    public function handleAccountUpdate($data) {
        return $this->useraccount->updateUser($data);
    }

    public function closeEntityConnection() {
        $this->useraccount->closeConnection();
    }
}

// Boundary Layer: UpdateUserAccountPage class for handling form display and user interaction
class UpdateUserAccountPage {
    private $controller;

    public function __construct() {
        $this->controller = new UpdateUserAccountController();
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $updateSuccess = $this->controller->handleAccountUpdate($data);
            $this->controller->closeEntityConnection();

            if ($updateSuccess) {
                header("Location: admin_manage_user_acc.php");
                exit();
            } else {
                echo "Error updating user account.";
            }
        }
    }

    public function UpdateUserAccountUI() {
        if (!isset($_GET['username'])) {
            header("Location: admin_manage_user_acc.php");
            exit();
        }

        $username = $_GET['username'];
        $userAccount = $this->controller->getUserAccount($username);
        $this->controller->closeEntityConnection();

        if (!$userAccount) {
            die("User not found.");
        }
        
        ?>
        <html>
        <head>
            <style>
                .form-body { font-size: 24px; text-align: center; }
                h1 { font-size: 48px; text-align: center; }
                table { font-size: 24px; margin: 0 auto; border-collapse: collapse; }
                td { padding: 10px; }
            </style>
        </head>
        <body>
        <h1>Update User Account</h1>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($userAccount->user_id); ?>" />
            <table>
                <tr>
                    <td><label for="username">Username:</label></td>
                    <td><input type="text" id="username" name="username" value="<?= htmlspecialchars($userAccount->username); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="password">Password:</label></td>
                    <td><input type="password" id="password" name="password" value="<?= htmlspecialchars($userAccount->password); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="role_id">Role:</label></td>
                    <td>
                        <select id="role_id" name="role_id">
                            <option value="1" <?= $userAccount->role_id == 1 ? 'selected' : ''; ?>>Admin</option>
                            <option value="2" <?= $userAccount->role_id == 2 ? 'selected' : ''; ?>>Used Car Agent</option>
                            <option value="3" <?= $userAccount->role_id == 3 ? 'selected' : ''; ?>>Buyer</option>
                            <option value="4" <?= $userAccount->role_id == 4 ? 'selected' : ''; ?>>Seller</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="email">Email:</label></td>
                    <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($userAccount->email); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="phone_num">Phone:</label></td>
                    <td><input type="text" id="phone_num" name="phone_num" value="<?= htmlspecialchars($userAccount->phone_num); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="status_id">Status:</label></td>
                    <td>
                        <select id="status_id" name="status_id"> 
                            <option value="1" <?= $userAccount->status_id == 1 ? 'selected' : ''; ?>>Active</option>
                            <option value="2" <?= $userAccount->status_id == 2 ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><button type="submit">Update Account</button></td>
                    <td><button type="button" onclick="window.location.href='admin_manage_user_acc.php'">Return to dashboard</button></td>
                </tr>
            </table>
        </form>
        </body>
        </html>
        <?php
    }
}

// Global Layer: Initializing the components
$view = new UpdateUserAccountPage();
$view->handleFormSubmission();
$view->UpdateUserAccountUI();
?>

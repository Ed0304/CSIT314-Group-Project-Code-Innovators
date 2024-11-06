<?php
require '../connectDatabase.php';

// ENTITY LAYER: Represents data structure for User Account
class UserAccount {
    public $user_id;
    public $username;
    public $password;
    public $role_id;
    public $email;
    public $phone_num;
    public $status_id;

    public function __construct($userData) {
        $this->user_id = $userData['user_id'];
        $this->username = $userData['username'];
        $this->password = $userData['password'];
        $this->role_id = $userData['role_id'];
        $this->email = $userData['email'];
        $this->phone_num = $userData['phone_num'];
        $this->status_id = $userData['status_id'];
    }
}

// CONTROL LAYER: Manages account updates and data retrieval
class UserAccountController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserDetails($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        return $userData ? new UserAccount($userData) : null;
    }

    public function updateUser($data) {
        $stmt = $this->db->prepare(
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
        return $stmt->execute();
    }

    public function handleRequest($username) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['user_id'] = $_POST['user_id'];
            $this->updateUser($data);
            header("Location: admin_manage_user_acc.php");
            exit();
        }
        $userAccount = $this->getUserDetails($username);
        return $userAccount ? (array) $userAccount : null; // Convert to array
    }
}

// BOUNDARY LAYER: Renders the form and accepts simple data
class UserAccountView {
    public function render($userData) {
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
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($userData['user_id']); ?>" />
            <table>
                <tr>
                    <td><label for="username">Username:</label></td>
                    <td><input type="text" id="username" name="username" value="<?= htmlspecialchars($userData['username']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="password">Password:</label></td>
                    <td><input type="password" id="password" name="password" value="<?= htmlspecialchars($userData['password']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="role_id">Role:</label></td>
                    <td>
                        <select id="role_id" name="role_id">
                            <option value="1" <?= $userData['role_id'] == 1 ? 'selected' : ''; ?>>Admin</option>
                            <option value="2" <?= $userData['role_id'] == 2 ? 'selected' : ''; ?>>Used Car Agent</option>
                            <option value="3" <?= $userData['role_id'] == 3 ? 'selected' : ''; ?>>Buyer</option>
                            <option value="4" <?= $userData['role_id'] == 4 ? 'selected' : ''; ?>>Seller</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="email">Email:</label></td>
                    <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="phone_num">Phone:</label></td>
                    <td><input type="text" id="phone_num" name="phone_num" value="<?= htmlspecialchars($userData['phone_num']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="status_id">Status:</label></td>
                    <td>
                        <select id="status_id" name="status_id"> 
                            <option value="1" <?= $userData['status_id'] == 1 ? 'selected' : ''; ?>>Active</option>
                            <option value="2" <?= $userData['status_id'] == 2 ? 'selected' : ''; ?>>Suspended</option>
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

if (!isset($_GET['username'])) {
    header("Location: admin_manage_user_acc.php");
    exit();
}

$username = $_GET['username'];
$controller = new UserAccountController($conn);
$userData = $controller->handleRequest($username);
if (!$userData) {
    die("User not found.");
}

$view = new UserAccountView();
$view->render($userData); // Now passing simple data
?>

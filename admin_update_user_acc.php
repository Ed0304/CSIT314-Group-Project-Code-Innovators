<?php
require 'connectDatabase.php';

// ENTITY LAYER: Represents User Account
class UserAccount {
    public function getUserDetails($conn, $username) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateUser($conn, $data) {
        $stmt = $conn->prepare(
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
}

// CONTROL LAYER: Manages account updates
class UserAccountController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest($conn, $username) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['user_id'] = $_POST['user_id'];
            $this->model->updateUser($conn, $data);
            header("Location: admin_manage_user_acc.php");
            exit();
        }
        return $this->model->getUserDetails($conn, $username);
    }
}

// VIEW LAYER: Renders the form
class UserAccountView {
    private $user;

    public function __construct($user) {
        $this->user = $user;
    }

    public function render() {
        ?>
        <html>
        <body>
        <h1>Update User Account</h1>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $this->user['user_id']; ?>" />
            Username: <input type="text" name="username" value="<?= $this->user['username']; ?>" required /><br/>
            Password: <input type="password" name="password" value="<?= $this->user['password']; ?>" required /><br/>
            Role:
            <select name="role_id">
                <option value="1" <?= $this->user['role_id'] == 1 ? 'selected' : ''; ?>>Admin</option>
                <option value="2" <?= $this->user['role_id'] == 2 ? 'selected' : ''; ?>>Used Car Agent</option>
                <option value="3" <?= $this->user['role_id'] == 3 ? 'selected' : ''; ?>>Buyer</option>
                <option value="4" <?= $this->user['role_id'] == 4 ? 'selected' : ''; ?>>Seller</option>
            </select><br/>
            Email: <input type="email" name="email" value="<?= $this->user['email']; ?>" required /><br/>
            Phone: <input type="text" name="phone_num" value="<?= $this->user['phone_num']; ?>" required /><br/>
            Status:
            <select name="status_id">
                <option value="1" <?= $this->user['status_id'] == 1 ? 'selected' : ''; ?>>Active</option>
                <option value="2" <?= $this->user['status_id'] == 2 ? 'selected' : ''; ?>>Suspended</option>
            </select><br/>
            <button type="submit">Update Account</button>
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
$model = new UserAccount();
$controller = new UserAccountController($model);
$user = $controller->handleRequest($conn, $username);
if (!$user) {
    die("User not found.");
}
$view = new UserAccountView($user);

$view->render();
?>

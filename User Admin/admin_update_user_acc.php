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
        <head>
            <style>
                .form-body {
                    font-size: 24px;
                    text-align: center;
                }
                h1 {
                    font-size: 48px;
                    text-align: center;
                }
                table {
                    font-size: 24px;
                    margin: 0 auto;
                    border-collapse: collapse;
                }
                td {
                    padding: 10px;
                }
            </style>
        </head>
        <body>
        <h1>Update User Account</h1>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($this->user['user_id']); ?>" />
            <table>
                <tr>
                    <td><label for="username" style="font-size:24px">Username:</label></td>
                    <td><input type="text" id="username" name="username" style="font-size:24px"value="<?= htmlspecialchars($this->user['username']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="password" style="font-size:24px">Password:</label></td>
                    <td><input type="password" id="password" name="password" style="font-size:24px" value="<?= htmlspecialchars($this->user['password']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="role_id">Role:</label></td>
                    <td>
                        <select id="role_id" name="role_id" style="font-size:24px">
                            <option value="1" style="font-size:24px"<?= $this->user['role_id'] == 1 ? 'selected' : ''; ?>>Admin</option>
                            <option value="2" style="font-size:24px"<?= $this->user['role_id'] == 2 ? 'selected' : ''; ?>>Used Car Agent</option>
                            <option value="3" style="font-size:24px"<?= $this->user['role_id'] == 3 ? 'selected' : ''; ?>>Buyer</option>
                            <option value="4" style="font-size:24px"<?= $this->user['role_id'] == 4 ? 'selected' : ''; ?>>Seller</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="email" style="font-size:24px">Email:</label></td>
                    <td><input type="email" id="email" name="email" style="font-size:24px"value="<?= htmlspecialchars($this->user['email']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="phone_num" style="font-size:24px">Phone:</label></td>
                    <td><input type="text" id="phone_num" name="phone_num" style="font-size:24px"value="<?= htmlspecialchars($this->user['phone_num']); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="status_id">Status:</label></td>
                    <td>
                        <select id="status_id" name="status_id" style="font-size:24px"> 
                            <option value="1" <?= $this->user['status_id'] == 1 ? 'selected' : ''; ?>>Active</option>
                            <option value="2" <?= $this->user['status_id'] == 2 ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><button type="submit" style="font-size:24px">Update Account</button></td>
                    <td><button type="return" style="font-size:24px">Return to dashboard</button></td>
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
$model = new UserAccount();
$controller = new UserAccountController($model);
$user = $controller->handleRequest($conn, $username);
if (!$user) {
    die("User not found.");
}

$view = new UserAccountView($user);
$view->render();
?>

<?php
session_start();

// ENTITY: Represents user data and database retrieval
class UserAccount {
    public function getProfileByUsername($pdo, $username) {
        $stmt = $pdo->prepare("SELECT u.user_id, u.username, u.password, r.role_name, u.email, u.phone_num, s.status_name
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON s.status_id = u.status_id
            WHERE u.username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// CONTROL LAYER: Passes the connection to the entity layer when necessary
class AccountController {
    private $userAccountModel;
    private $pdo;

    public function __construct($pdo) {
        $this->userAccountModel = new UserAccount();
        $this->pdo = $pdo;
    }

    public function getProfile($username) {
        return $this->userAccountModel->getProfileByUsername($this->pdo, $username);
    }
}

// BOUNDARY LAYER: Responsible for rendering user information
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
            <title>Account Information</title>
        </head>
        <body>
            <h1 style="text-align: center">Account Information</h1>
            <table id="infoTable">
                <tr>
                    <td><strong>Username</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['username'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Password</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['password'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Role</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['role_name'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['email'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Phone Number</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['phone_num'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->profileData['status_name']); ?></td>
                </tr>
                <tr>
                    <!-- Empty table row, just to give spacing with acc info and buttons -->
                    <td><br/></td>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>
                        <form action="admin_manage_user_acc.php" class="form-body" method="post">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData['username'] ?? ''); ?>">
                            <button type="submit" name="action" value="return" style="font-size: 24px">Return to accounts list</button>
                        </form>
                    </td>
                    <td>
                        <form action="" class="form-body" method="post">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData['username'] ?? ''); ?>">
                            <button type="submit" name="action" value="update" style="font-size: 24px">Update account information</button>
                        </form>
                    </td>
                    <td>
                        <form action="" class="form-body" method="post">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData['username'] ?? ''); ?>">
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

// MAIN LOGIC: Coordinates the application
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $username = $_POST['username']; // Get the username from the form

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

// Use GET parameter to fetch the username
$username = isset($_GET['username']) ? $_GET['username'] : '';

if ($username) {
    try {
        // Establish database connection
        $pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Controller instance creation
        $accountController = new AccountController($pdo);
        $profileData = $accountController->getProfile($username);

        // Render the view with retrieved profile data
        $profileView = new ProfileView($profileData);
        $profileView->render();
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
} else {
    echo "No username provided.";
}
?>

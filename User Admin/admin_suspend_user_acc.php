<?php
session_start();

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
            #infoTable th,td {
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
                    <td><br/></td>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>
                    <form action="" method="POST" class="form-body"> 
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData['username']); ?>">
                        <input type="hidden" name="action" value="suspend">
                        <button type="submit" style="font-size: 24px">Suspend</button>
                    </form>
                    </td>
                    <td>
                    <form action="" method="POST" class="form-body"> 
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData['username']); ?>">
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

// MAIN LOGIC: Coordinates the application
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Use GET parameter to fetch the username
$username = isset($_GET['username']) ? $_GET['username'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'suspend') {
    $username = $_POST['username'];

    
    // Create an instance of AccountController and suspend the user
    $accountController = new AccountController();
    $accountController->setSuspend($username);

    //prompt message before redirecting
    $_SESSION['success_message'] = "User account suspended successfully.";
    echo "<script>
    alert('" . htmlspecialchars($_SESSION['success_message']) . "');
    window.location.href = 'admin_manage_user_acc.php';
    </script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'Remove') {
    $username = $_POST['username'];

    // Create an instance of AccountController and suspend the user
    $accountController = new AccountController();
    $accountController->setRemoveSuspend($username);
    
    //Prompt message before redirecting
    $_SESSION['removeSuspend_message'] = "User account suspension removed.";
    echo "<script>
    alert('" . htmlspecialchars($_SESSION['removeSuspend_message']) . "');
    window.location.href = 'admin_manage_user_acc.php';
    </script>";
    exit();
}

if ($username) {
    // Controller instance creation
    $accountController = new AccountController();
    $profileData = $accountController->getProfile($username);

    // Render the view with retrieved profile data
    $profileView = new ProfileView($profileData);
    $profileView->render();
} else {
    echo "No username provided.";
}

// CONTROL LAYER: Serves as an intermediary between view and entity
class AccountController {
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

    public function setRemoveSuspend($username){
        return $this->userAccountModel->RemoveSuspend($username);
    }
}

// ENTITY: Handles all logic for user data and database interactions
class UserAccount {
    private $pdo;

    public function __construct() {
        try {
            // Establish database connection within the entity
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

    //suspend user function (status_id = 1 is active, status_id = 2 is suspended)
    public function Suspend($username){
        $stmt = $this -> pdo->prepare("UPDATE users  
                                       SET status_id = 2
                                       WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
    }

    
    //remove suspend function
    public function RemoveSuspend($username){
        $stmt = $this -> pdo->prepare("UPDATE users  
                                       SET status_id = 1
                                       WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
    }
}
?>
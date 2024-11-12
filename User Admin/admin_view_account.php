<?php
session_start();
require '../connectDatabase.php';

// Entity Layer: UserAccount class for interacting with the database
class UserAccount {
    private $pdo;
    private $user_id;
    private $username;
    private $password;
    private $role_name;
    private $email;
    private $phone_num;
    private $status_name;
    private $first_name;
    private $last_name;
    private $about;
    private $profile_image;

    public function __construct() {
        $this->connectDatabase();
    }

    private function connectDatabase() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function viewUserAccount($username) {
        $stmt = $this->pdo->prepare("SELECT u.user_id, u.username, u.password, r.role_name, u.email, u.phone_num, 
            s.status_name, p.first_name, p.last_name, p.about, p.profile_image
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON s.status_id = u.status_id
            LEFT JOIN profile p ON u.user_id = p.user_id
            WHERE u.username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $accountData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create and return UserAccount object
        return $this->getUserAccountData($accountData);
    }

    private function getUserAccountData($data) {
        $userAccount = new UserAccount();
        $userAccount->user_id = $data['user_id'];
        $userAccount->username = $data['username'];
        $userAccount->password = $data['password'];
        $userAccount->role_name = $data['role_name'];
        $userAccount->email = $data['email'];
        $userAccount->phone_num = $data['phone_num'];
        $userAccount->status_name = $data['status_name'];
        $userAccount->first_name = $data['first_name'];
        $userAccount->last_name = $data['last_name'];
        $userAccount->about = $data['about'];
        $userAccount->profile_image = $data['profile_image'];

        return $userAccount;
    }

    // Getter methods for each property
    public function getUserId() { return $this->user_id; }
    public function getUsername() { return $this->username; }
    public function getPassword() { return $this->password; }
    public function getRoleName() { return $this->role_name; }
    public function getEmail() { return $this->email; }
    public function getPhoneNum() { return $this->phone_num; }
    public function getStatusName() { return $this->status_name; }
    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getAbout() { return $this->about; }
    public function getProfileImage() { return $this->profile_image; }
}

// Control Layer: ViewUserAccountController class for managing data flow between boundary and entity layers
class ViewUserAccountController {
    private $userAccount;

    public function __construct($userAccount) {
        $this->userAccount = $userAccount;
    }

    public function viewUserAccount($username) {
        return $this->userAccount->viewUserAccount($username);
    }
}

// Boundary Layer: ViewUserAccountPage class for handling form display and user interaction
class ViewUserAccountPage {
    private $controller;
    private $userAccount;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function handleRequest() {
        // Check if user is logged in
        if (!isset($_SESSION['username'])) {
            header("Location: ../login.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } else {
            $username = $_GET['username'] ?? '';
            if ($username) {
                $this->userAccount = $this->controller->viewUserAccount($username);
                $this->ViewUserAccountUI();  // Fetch and display UserAccount information
            }
        }
    }

    private function handlePostRequest() {
        if (isset($_POST['action'])) {
            $username = $_POST['username'];
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

    public function ViewUserAccountUI() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Information</title>
            <style>
                /* Global Styles */
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                h1 {
                    text-align: center;
                    margin-top: 20px;
                    color: #333;
                }
                table {
                    width: 80%;
                    margin: 20px auto;
                    border-collapse: collapse;
                    background-color: #fff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                th, td {
                    padding: 12px;
                    text-align: left;
                    font-size: 18px;
                    color: #333;
                    border-bottom: 1px solid #ddd;
                }
                th {
                    background-color: #4CAF50;
                    color: white;
                }
                td {
                    background-color: #f9f9f9;
                }
                td img {
                    border-radius: 50%;
                    max-width: 150px;
                    max-height: 150px;
                }
                /* Button Styles */
                button {
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    font-size: 16px;
                    cursor: pointer;
                    border-radius: 5px;
                    margin: 10px 5px;
                    transition: background-color 0.3s ease;
                }
                button:hover {
                    background-color: #45a049;
                }
            </style>
        </head>
        <body>
            <h1>Account Information</h1>
            <table>
                <tr>
                    <td><strong>Profile Image</strong></td>
                    <td colspan="2">
                        <?php
                        if ($this->userAccount->getProfileImage()) {
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($this->userAccount->getProfileImage()) . '" alt="Profile Image" />';
                        } else {
                            echo 'No profile image available.';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Full Name</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getFirstName() .' '. htmlspecialchars($this->userAccount->getLastName() ?? '')); ?></td>
                </tr>
                <tr>
                    <td><strong>About</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getAbout() ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Username</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getUsername() ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Password</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getPassword() ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Role</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getRoleName() ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getEmail() ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Phone Number</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getPhoneNum() ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td colspan="2"><?php echo htmlspecialchars($this->userAccount->getStatusName()); ?></td>
                </tr>                
                <tr>
                    <td colspan="3" style="text-align: center;">
                        <form action="" method="post">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->userAccount->getUsername()); ?>">
                            <button type="submit" name="action" value="return">Return</button>
                            <button type="submit" name="action" value="update">Update</button>
                            <button type="submit" name="action" value="suspend">Suspend</button>
                        </form>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
    }
}

// Create the UserAccount entity and ViewUserAccountController
$userAccount = new UserAccount();
$controller = new ViewUserAccountController($userAccount);
$page = new ViewUserAccountPage($controller);
$page->handleRequest();
?>

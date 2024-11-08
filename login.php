<?php
session_start();
include 'connectDatabase.php';

// Entity Layer: User class to handle user authentication with direct database interaction
class User {
    public $username;
    public $password;
    public $role;
    public $db;

    public function __construct($db, $username, $password, $role) {
        $this->db = $db;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }

    // Handles database query to authenticate the user
    public function getUserData() {
        $roleMapping = [
            'user admin' => 1,
            'used car agent' => 2,
            'buyer' => 3,
            'seller' => 4
        ];

        if (!array_key_exists($this->role, $roleMapping)) {
            return false;
        }

        $role_id = $roleMapping[$this->role];
        $stmt = $this->db->prepare("SELECT user_id, password, status_id FROM users WHERE username = ? AND role_id = ?");
        $stmt->bind_param("si", $this->username, $role_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $stored_password, $status_id);
            $stmt->fetch();

            return ['user_id' => $user_id, 'password' => $stored_password, 'status_id' => $status_id];
        } else {
            return false;
        }
    }
}

// Controller Layer: AuthController class to handle user authentication and communication with the User entity
class AuthController {

    private $user;

    public function __construct($user) {
        $this->user = $user;
    }

    // Authenticate the user by interacting with the User entity
    public function authenticateUser() {
        $userData = $this->user->getUserData();

        if ($userData === false) {
            return "Invalid username, password, or role.";
        }

        // Check if the account is suspended
        if ($userData['status_id'] == 2) {
            return "Account suspended. Please contact support.";
        }

        // Authenticate password
        if ($userData['password'] === $this->user->password) {
            return ['authenticated' => true, 'user_id' => $userData['user_id']];
        } else {
            return "Invalid username, password, or role.";
        }
    }
}

// Boundary Layer: LoginForm class to generate and display the login form HTML and handle user interaction
class LoginForm {

    public static function display($message = "") {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <link rel="stylesheet" href="login.css"/>
            <title>CSIT314-PROJECT</title>
        </head>
        <body>
            <div class="website-title">
                <h1>CSIT314-GROUP PROJECT</h1>
                <h2>Made by: Code Innovators!</h2>
            </div>

            <?php if ($message): ?>
                <div class="error-message" style="color: red; font-weight: bold; margin-bottom: 20px; text-align:center">
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-body">
                    <label for="role" class="form-label">Login As:</label>
                    <select id="role" name="role" class="form-label" required>
                        <option value="user admin">User Admin</option>
                        <option value="used car agent">Used Car Agent</option>
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                    </select>
                    <br/><br/>
                    <label for="username" class="form-label">Username </label>
                    <input type="text" id="username" name="username" class="form-label" required/>
                    <br/><br/>
                    <label for="password" class="form-label">Password </label>
                    <input type="password" id="password" name="password" class="form-label" required/>
                    <br/><br/>
                    <button type="submit" class="form-label">Login</button>
                    <br/>
                </div>
            </form>
        </body>
        </html>
        <?php
    }

    public static function handleLogin() {
        // Only handle post if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get input from the form
            $username = htmlspecialchars($_POST['username']);
            $password = htmlspecialchars($_POST['password']);
            $role = htmlspecialchars($_POST['role']);

            // Ensure all fields are filled
            if ($username && $password && $role) {
                // Create a new User entity
                $db = (new Database())->getConnection();
                $user = new User($db, $username, $password, $role);

                // Create AuthController and authenticate
                $authController = new AuthController($user);
                $authResult = $authController->authenticateUser();

                // Check authentication result
                if (is_array($authResult) && $authResult['authenticated'] === true) {
                    $_SESSION['username'] = $username;
                    $_SESSION['user_id'] = $authResult['user_id'];

                    // Redirect based on the role
                    self::redirectToDashboard($role);
                } else {
                    // If authentication fails, display the error message
                    self::display($authResult); // Error message handled by the Boundary
                }
            } else {
                self::display("Please fill in all fields.");
            }
        } else {
            self::display();
        }
    }

    // Redirect the user based on their role
    public static function redirectToDashboard($role) {
        switch($role) {
            case 'user admin':
                header("Location: User Admin/admin_dashboard.php");
                break;
            case 'used car agent':
                header("Location: Used Car Agent/agent_dashboard.php");
                break;
            case 'buyer':
                header("Location: Buyer/buyer_dashboard.php");
                break;
            case 'seller':
                header("Location: Seller/seller_dashboard.php");
                break;
            default:
                echo "Invalid role selected.";
                break;
        }
        exit();
    }
}

// Handle login request
LoginForm::handleLogin();
?>

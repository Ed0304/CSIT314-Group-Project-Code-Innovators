<?php
session_start();
include '../connectDatabase.php';

class UserAccount {
    public $username;
    public $password;
    public $role;
    private $db;

    public function __construct($db, $username, $password, $role) {
        $this->db = $db;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }

    // Retrieves user data from the database, using $userAccount as a parameter
    public function verifyLoginCredentials($userAccount) {
        $roleMapping = [
            'user admin' => 1,
            'used car agent' => 2,
            'buyer' => 3,
            'seller' => 4
        ];

        if (!array_key_exists($userAccount->role, $roleMapping)) {
            return false;
        }

        $role_id = $roleMapping[$userAccount->role];
        $stmt = $this->db->prepare("SELECT user_id, password, status_id FROM users WHERE username = ? AND role_id = ?");
        $stmt->bind_param("si", $userAccount->username, $role_id);
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

// Controller Layer: LoginController class for handling user authentication logic
class LoginController {
    private $isSuspended = false;

    public function verifyLoginCredentials($userAccount) {
        $userData = $userAccount->verifyLoginCredentials($userAccount);

        if ($userData === false) {
            return false;
        }

        // Check if the account is suspended
        if ($userData['status_id'] == 2) {
            $this->isSuspended = true;
            return false;
        }

        // Verify the password
        return $userData['password'] === $userAccount->password;
    }

    public function isSuspended() {
        return $this->isSuspended;
    }

    public function getUserId($userAccount) {
        $userData = $userAccount->verifyLoginCredentials($userAccount);
        return $userData['user_id'];
    }
}

// Boundary Layer: LoginPage class to handle form display and user interaction
class LoginPage {

        // This function generates the login UI based on the selected role
        public static function LoginUI($selectedRole = null) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Login - Code Innovators</title>
                <link rel="stylesheet" href="./style.css">
                <link rel="stylesheet" href="./fontawesome-free-6.4.0-web/css/all.css">
            </head>
            <body>
                <nav>
                    <div class="container nav-container">
                        <a href="index.html" class="logo"><h3>Code Innovators</h3></a>
                        <ul class="nav-link">
                            <li><a href="index.html" class="active">Home</a></li>
                            <li><a href="about.html">About</a></li>
                            <li><a href="login.php?role=buyer">Buy</a></li>
                            <li><a href="login.php?role=seller">Sell</a></li>
                            <li><a href="login.php?role=used car agent">Publicize</a></li>  
                        </ul>
                        <ul class="social-link">
                            <li><a href="https://t.me/+WvqfOz0QNlA0ZjI1" target="_blank"><i class="fab fa-telegram"></i></a></li>
                        </ul>
                    </div>
                </nav>
    
                <div class="login-section">
                    <div class="login-container">
                        <!-- Dynamic Heading based on Role -->
                        <h2>
                            <?php
                                if ($selectedRole) {
                                    if ($selectedRole == 'seller') {
                                        echo 'Start Selling Today!';
                                    } elseif ($selectedRole == 'used car agent') {
                                        echo 'Start Publicizing Today!';
                                    } elseif ($selectedRole == 'user admin') {
                                        echo 'User Admin';
                                    } elseif ($selectedRole == 'buyer') {
                                        echo 'Start Buying Today!';
                                    }
                                } else {
                                    echo 'Log In';
                                }
                            ?>
                        </h2>
    
                        <!-- Login Form -->
                        <form method="POST" class="login-form">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required placeholder="Enter your username">
                            </div>
    
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required placeholder="Enter your password">
                            </div>
    
                            <div class="form-group">
                                <label for="role">Login As</label>
                                <select id="role" name="role" required>
                                    <option value="user admin" <?php echo ($selectedRole == 'user admin') ? 'selected' : ''; ?>>User Admin</option>
                                    <option value="buyer" <?php echo ($selectedRole == 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                                    <option value="seller" <?php echo ($selectedRole == 'seller') ? 'selected' : ''; ?>>Seller</option>
                                    <option value="used car agent" <?php echo ($selectedRole == 'used car agent') ? 'selected' : ''; ?>>Used Car Agent</option>
                                </select>
                            </div>
    
                            <button type="submit" class="login-btn">Sign In</button>
    
                            <p style="text-align: center; margin-top: 1rem;">
                                Don't have an account? <a href="#" style="color: var(--color-primary);">Sign up</a>
                            </p>
                        </form>
                    </div>
                </div>
            </body>
            </html>
            <?php
        }    

    public static function handleLogin() {
        // Only handle POST if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Use null coalescing to handle undefined keys
            $username = htmlspecialchars($_POST['username'] ?? '');
            $password = htmlspecialchars($_POST['password'] ?? '');
            $role = htmlspecialchars($_POST['role'] ?? '');

            if ($username && $password && $role) {
                $db = (new Database())->getConnection();
                $user = new UserAccount($db, $username, $password, $role);

                $authController = new LoginController();
                $authResult = $authController->verifyLoginCredentials($user);

                if ($authResult === true) {
                    $_SESSION['username'] = $username;
                    $_SESSION['user_id'] = $authController->getUserId($user);
                    self::redirectToDashboard($role);
                } else {
                    // Set session message for UI
                    $_SESSION['message'] = $authController->isSuspended()
                        ? "Account suspended. Please contact support."
                        : "Invalid username, password, or role.";
                    self::LoginUI($role);  // Pass the role to keep it selected
                }
            } else {
                // Set session message for UI
                $_SESSION['message'] = "Please fill in all fields.";
                self::LoginUI($role);  // Pass the role to keep it selected
            }
        } else {
            $role = $_GET['role'] ?? '';  
            self::LoginUI($role);
        }
    }

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
        }
    }
}

if (isset($_GET['role'])) {
    $role = $_GET['role']; 
    LoginPage::LoginUI($role);
} else {
    
    LoginPage::LoginUI('');
}

LoginPage::handleLogin(); // Handle login flow
?>

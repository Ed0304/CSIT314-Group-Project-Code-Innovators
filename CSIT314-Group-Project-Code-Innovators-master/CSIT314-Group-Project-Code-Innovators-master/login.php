<?php
session_start(); // Start the session to maintain user data across requests
include 'connectDatabase.php';

// Entity Layer: User class to handle user authentication
class User {
    private $username;
    private $role;
    private $password;

    public function __construct($username, $password, $role) {
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }

    public function authenticate($db) {
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
        // Modify the query to also select user_id
        $stmt = $db->prepare("SELECT user_id, password, status_id FROM users WHERE username = ? AND role_id = ?");
        $stmt->bind_param("si", $this->username, $role_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $stored_password, $status_id);
            $stmt->fetch();

            // Check if the user is suspended (status_id = 2)
            if ($status_id == 2) {
                return "Account suspended. Please contact support.";
            }
            // Return user_id and authentication result
            return ['authenticated' => $this->password === $stored_password, 'user_id' => $user_id];
        } else {
            return false;
        }
    }
}

// Control Layer: AuthController class to handle form submission and user authentication
class AuthController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    public function authenticateUser($username, $password, $role) {
        $user = new User($username, $password, $role);
        $dbConnection = $this->database->getConnection();

        $authResult = $user->authenticate($dbConnection);

        if (is_array($authResult) && $authResult['authenticated'] === true) {
            // Store user_id in session
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $authResult['user_id']; // Store user_id
            return $this->getRedirectLocation($role);
        } elseif ($authResult === "Account suspended. Please contact support.") {
            return $authResult;
        }
        return "Invalid username, password, or role.";
    }

    private function getRedirectLocation($role) {
        switch($role) {
            case 'user admin':
                return "User Admin/admin_dashboard.php";
            case 'used car agent':
                return "Used Car Agent/agent_dashboard.php";
            case 'buyer':
                return "Buyer/buyer_dashboard.php";
            case 'seller':
                return "seller_dashboard.php";
            default:
                return "Invalid role selected.";
        }
    }

    public function closeDatabaseConnection() {
        $this->database->closeConnection();
    }
}

// Boundary Layer: LoginForm class to generate the login form HTML
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
                    <button type="submit" class="form-label">Submit</button>
                    <br/>
                </div>
            </form>
        </body>
        </html>
        <?php
    }
}

// Handle form submission and interaction with the controller
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller = new AuthController();
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $role = htmlspecialchars($_POST['role']);

    if ($username && $password && $role) {
        $location = $controller->authenticateUser($username, $password, $role);

        if ($location && strpos($location, '.php') !== false) {
            header("Location: $location");
            exit();
        } else {
            LoginForm::display($location);
        }
    } else {
        LoginForm::display("Please fill in all fields.");
    }
    $controller->closeDatabaseConnection();
} else {
    LoginForm::display();
}
?>

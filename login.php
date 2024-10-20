<?php
session_start(); // Start the session

// Entity Layer: Database class to handle the connection
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "csit314";
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// Entity Layer: User class to handle user authentication
class User {
    private $conn;
    private $username;
    private $role;
    private $password;

    public function __construct($db, $username, $password, $role) {
        $this->conn = $db;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }

    public function authenticate() {
        // Map role to role_id
        $roleMapping = [
            'admin' => 1,
            'agent' => 2,
            'buyer' => 3,
            'seller' => 4
        ];
        $role_id = $roleMapping[$this->role];

        // Prepare and bind
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE username = ? AND role_id = ?");
        $stmt->bind_param("si", $this->username, $role_id);
        $stmt->execute();
        $stmt->store_result();

        // Check if username and role exist
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($stored_password);
            $stmt->fetch();

            // Verify password (use password_hash in a real app)
            if ($this->password === $stored_password) {
                return $this->redirectBasedOnRole();
            } else {
                return "Invalid username or password.";
            }
        } else {
            return "Invalid username, password, or role.";
        }
        $stmt->close();
    }

    private function redirectBasedOnRole() {
        // Store the username in session
        $_SESSION['username'] = $this->username;

        // Redirect based on role
        switch($this->role) {
            case 'admin':
                header("Location: admin_dashboard.php");
                exit();
            case 'agent':
                header("Location: agent_dashboard.php");
                exit();
            case 'buyer':
                header("Location: buyer_dashboard.php");
                exit();
            case 'seller':
                header("Location: seller_dashboard.php");
                exit();
            default:
                return "Invalid role selected.";
        }
    }
}

// Control Layer: AuthController class to handle form submission and user authentication
class AuthController {
    private $database;
    private $user;

    public function __construct() {
        // Instantiate the Database object
        $this->database = new Database();
    }

    public function handleLogin($username, $password, $role) {
        // Create User object for authentication
        $this->user = new User($this->database->getConnection(), $username, $password, $role);
        return $this->user->authenticate();
    }

    public function closeDatabaseConnection() {
        // Close the database connection
        $this->database->closeConnection();
    }
}

// Control Layer logic for handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller = new AuthController();

    // Retrieve and sanitize form input
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $role = htmlspecialchars($_POST['role']); 

    // Authenticate the user
    $message = $controller->handleLogin($username, $password, $role);

    if ($message) {
        echo $message;
    }

    // Close the database connection
    $controller->closeDatabaseConnection();
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <link rel="stylesheet" href="login.css"/>
    <title>CSIT314-PROJECT</title>
</head>
<body>
    <div class="website-title">
        <br/><h1>CSIT314-GROUP PROJECT</h1>
        <h2>Made by: Code Innovators!</h2>
    </div>

    <!-- Boundary: HTML Form for user login -->
    <form action="" method="POST">
        <div class="form-body">
            <br/><br/>
            <label for="role" class="form-label">Login As:</label>
            <select id="role" name="role" class="form-label" required>
                <option value="admin" class="form-label">User Admin</option>
                <option value="agent" class="form-label">Used Car Agent</option>
                <option value="buyer" class="form-label">Buyer</option>
                <option value="seller" class="form-label">Seller</option>
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

    <!-- Boundary: Credits button -->
    <div class="submit">
        <br/>
        <button onclick="hello_world()" style="display: block; margin: 0 auto; font-size: 24px;" title="See who are behind the scenes of this project!">Credits</button>
    </div>

    <script src="login.js"></script>
</body>
</html>

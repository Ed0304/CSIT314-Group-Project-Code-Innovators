<?php
// ENTITY LAYER: Handles data-related tasks (database interactions)
class UserAccount {
    private $conn;

    // Constructor to initialize the database connection
    public function __construct($servername, $username, $password, $dbname) {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // Check for a connection error
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Fetch the role ID based on role name
    public function getRoleId($role_name) {
        $stmt = $this->conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $stmt->bind_result($role_id);
        $stmt->fetch();
        $stmt->close();
        return $role_id;
    }

    // Insert a new user into the users table
    public function createUser($username, $password, $role_id, $email, $phone_num) {
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, role_id, email, phone_num) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $username, $password, $role_id, $email, $phone_num);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Close the connection
    public function close() {
        $this->conn->close();
    }
}

// CONTROL LAYER: Handles the logic and mediates between boundary and entity layers
class AccountController {
    private $userAccountModel;

    // Constructor to initialize the UserAccount entity model
    public function __construct($userAccountModel) {
        $this->userAccountModel = $userAccountModel;
    }

    // Handle form submission for account creation
    public function handleAccountCreation($formData) {
        $username = $formData['username'];
        $password = $formData['password'];
        $email = $formData['email'];
        $phone_num = $formData['phone_num'];
        $role_name = $formData['role'];

        // Get the role ID based on the role name
        $role_id = $this->userAccountModel->getRoleId($role_name);

        // Check if the role ID exists
        if (!$role_id) {
            return "Error: Role not found.";
        }

        // Insert the new account into the users table
        $result = $this->userAccountModel->createUser($username, $password, $role_id, $email, $phone_num);

        return $result ? "New account created successfully." : "Error: Failed to create account.";
    }
}

// BOUNDARY LAYER: Manages the user interface (display form and messages)
class AccountCreationView {
    private $message;

    // Constructor to initialize any message to display
    public function __construct($message = "") {
        $this->message = $message;
    }

    // Render the account creation form
    public function render() {
        ?>
        <html>
        <head>
            <title>Account Creation Page</title>
            <style>
                .form-body { text-align: center; }
                .select-label { font-size: 24px; }
                .invisible-table {
                    border-collapse: collapse;
                    width: 0%;
                    margin: auto;
                }
                .invisible-table td { border: none; padding: 10px; }
            </style>
        </head>
        <body>
            <div style="background-color: red" class="header">
                <h1 style="text-align:center">Account Creation</h1>
                <h2 style="text-align:center">Please fill in the following details</h2>
                <h3 style="text-align:center">All fields are mandatory</h3>
            </div>

            <!-- Display success or error messages -->
            <?php if ($this->message): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

            <!-- Form for account creation -->
            <form class="form-body" method="POST" action="">
                <table class="invisible-table">
                    <tr>
                        <td><label style="font-size: 24px">Username:</label></td>
                        <td><input type="text" name="username" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Password:</label></td>
                        <td><input type="password" name="password" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label for="role" class="select-label">Role:</label></td>
                        <td>
                            <select id="role" name="role" class="select-label" required>
                                <option value="used car agent" class="select-label">Used Car Agent</option>
                                <option value="buyer" class="select-label">Buyer</option>
                                <option value="seller" class="select-label">Seller</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Email:</label></td>
                        <td><input type="text" name="email" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Phone Number:</label></td>
                        <td><input type="text" name="phone_num" style="font-size: 24px" required/></td>
                    </tr>
                </table>
                <br/>
                <button type="submit" style="font-size: 24px">Create New Account</button>
            </form>
            <br/>
            <hr/>
            <form action="admin_manage_user_acc.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Return to accounts list</button>
            </form>
        </body>
        </html>
        <?php
    }
}

// MAIN LOGIC: Connects the BCE components

// Database configuration
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "csit314";

// Entity layer: Initialize UserAccount model with the database connection
$userAccountModel = new UserAccount($servername, $dbUsername, $dbPassword, $dbname);

// Control layer: Initialize AccountController with the entity model
$controller = new AccountController($userAccountModel);

// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $controller->handleAccountCreation($_POST);
}

// Boundary layer: Initialize AccountCreationView with any message and render the form
$view = new AccountCreationView($message);
$view->render();

// Close the database connection
$userAccountModel->close();
?>

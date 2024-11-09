<?php
require '../connectDatabase.php';

class UserAccount {
    public function __construct() {
        // Empty constructor
    }

    // Fetch the role ID based on role name
    public function getRoleId($conn, $role_name) {
        $stmt = $conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $stmt->bind_result($role_id);
        $stmt->fetch();
        $stmt->close();
        return $role_id;
    }

    // Insert a new user and profile into the respective tables
    public function createUser($conn, $username, $password, $role_id, $email, $phone_num, $first_name, $last_name, $about, $gender, $profile_image) {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Insert the new user into users table
            $stmt = $conn->prepare("INSERT INTO users (username, password, role_id, email, phone_num, status_id) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("ssiss", $username, $password, $role_id, $email, $phone_num);
            $stmt->execute();
            $user_id = $conn->insert_id; // Get the last inserted user_id
            $stmt->close();

            // Insert profile data into the profile table with raw binary image data
            $stmt = $conn->prepare("INSERT INTO profile (user_id, first_name, last_name, about, gender, profile_image, status_id) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("issssb", $user_id, $first_name, $last_name, $about, $gender, $null);

            // Bind and send raw binary data
            $stmt->send_long_data(5, file_get_contents($profile_image['tmp_name']));

            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }
}

class CreateAccountController {
    private $userAccountModel;

    public function __construct($userAccountModel) {
        $this->userAccountModel = $userAccountModel;
    }

    public function handleAccountCreation($formData, $fileData, $conn) {
        $username = $formData['username'];
        $password = $formData['password'];
        $email = $formData['email'];
        $phone_num = $formData['phone_num'];
        $role_name = $formData['role'];
        $first_name = $formData['first_name'];
        $last_name = $formData['last_name'];
        $about = $formData['about'];
        $gender = $formData['gender'];

        // Handle profile image file upload
        $profile_image = $fileData['profile_image'];

        // Get the role ID based on the role name
        $role_id = $this->userAccountModel->getRoleId($conn, $role_name);
        if (!$role_id) {
            return "Error: Role not found.";
        }

        // Insert the new account into the users table and profile into profile table
        $result = $this->userAccountModel->createUser($conn, $username, $password, $role_id, $email, $phone_num, $first_name, $last_name, $about, $gender, $profile_image);

        return $result ? "New account created successfully." : "Error: Failed to create account.";
    }
}

class CreateAccountBoundary {
    private $message;

    public function __construct($message = "") {
        $this->message = $message;
    }

    public function render() {
        ?>
        <html>
        <head>
            <title>Account Creation Page</title>
            <style>
                .form-body { text-align: center; }
                .select-label { font-size: 24px; }
                .invisible-table { border-collapse: collapse; width: 0%; margin: auto; }
                .invisible-table td { border: none; padding: 10px; }
            </style>
        </head>
        <body>
            <div style="background-color: red" class="header">
                <h1 style="text-align:center">Account Creation</h1>
                <h2 style="text-align:center">Please fill in the following details</h2>
                <h3 style="text-align:center">All fields are mandatory</h3>
            </div>

            <?php if ($this->message): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

            <form class="form-body" method="POST" action="" enctype="multipart/form-data">
                <table class="invisible-table">
                    <tr><td><label style="font-size: 24px">Username:</label></td><td><input type="text" name="username" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Password:</label></td><td><input type="password" name="password" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">First Name:</label></td><td><input type="text" name="first_name" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Last Name:</label></td><td><input type="text" name="last_name" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">About:</label></td><td><textarea name="about" style="font-size: 24px" required></textarea></td></tr>
                    <tr>
                        <td><label style="font-size: 24px">Gender:</label></td>
                        <td>
                            <select name="gender" style="font-size: 24px" required>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr><td><label for="role" class="select-label">Role:</label></td><td><select id="role" name="role" class="select-label" required>
                        <option value="used car agent">Used Car Agent</option>
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                    </select></td></tr>
                    <tr><td><label style="font-size: 24px">Email:</label></td><td><input type="text" name="email" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Phone Number:</label></td><td><input type="text" name="phone_num" style="font-size: 24px" required/></td></tr>
                    <tr><td><label style="font-size: 24px">Profile Image:</label></td><td><input type="file" name="profile_image" style="font-size: 24px" required/></td></tr>
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

// MAIN LOGIC: Connect BCE components
$message = "";
$userAccountModel = new UserAccount();
$controller = new CreateAccountController($userAccountModel);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $controller->handleAccountCreation($_POST, $_FILES, $conn);
}

$view = new CreateAccountBoundary($message);
$view->render();

$database->closeConnection();
?>

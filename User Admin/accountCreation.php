<?php
require '../connectDatabase.php';

// Entity Layer: UserAccount class for interacting with the database
class UserAccount {
    public $conn;
    public $username;
    public $password;
    public $role_id;
    public $email;
    public $phone_num;
    public $first_name;
    public $last_name;
    public $about;
    public $gender;
    public $profile_image;

    public function __construct($conn, $username = "", $password = "", $role_id = 0, $email = "", $phone_num = "", $first_name = "", $last_name = "", $about = "", $gender = "", $profile_image = null) {
        $this->conn = $conn;
        $this->username = $username;
        $this->password = $password;
        $this->role_id = $role_id;
        $this->email = $email;
        $this->phone_num = $phone_num;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->about = $about;
        $this->gender = $gender;
        $this->profile_image = $profile_image;
    }

    public function getRoleId($role_name) {
        $stmt = $this->conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $stmt->bind_result($role_id);
        $stmt->fetch();
        $stmt->close();
        return $role_id;
    }

    public function createUserAccount() {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("INSERT INTO users (username, password, role_id, email, phone_num, status_id) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("ssiss", $this->username, $this->password, $this->role_id, $this->email, $this->phone_num);
            $stmt->execute();
            $user_id = $this->conn->insert_id;
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO profile (user_id, first_name, last_name, about, gender, profile_image, status_id) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("issssb", $user_id, $this->first_name, $this->last_name, $this->about, $this->gender, $null);
            $stmt->send_long_data(5, file_get_contents($this->profile_image['tmp_name']));
            $stmt->execute();
            $stmt->close();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}

// Control Layer: CreateUserAccountController class for managing data flow between boundary and entity layers
class CreateUserAccountController {
    public $userAccount;

    public function __construct($userAccount) {
        $this->userAccountModel = $userAccount;
    }

    public function getRoleId($role_name) {
        return $this->userAccountModel->getRoleId($role_name);
    }

    public function createUserAccount($userAccount) {
        $this->userAccountModel = $userAccount;
        return $this->userAccountModel->createUserAccount();
    }
}

// Boundary Layer: CreateUserAccountPage class for handling form display and user interaction
class CreateUserAccountPage {
    private $message;

    public function __construct($message = "") {
        $this->message = $message;
    }

    public function CreateUserAccountUI() {
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
                    <tr><td><label style="font-size: 24px">Profile Image:</label></td><td><input type="file" name="profile_image" style="font-size: 24px" /></td></tr>
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

    public function handleAccountCreation($formData, $fileData, $controller) {
        $userAccount = new UserAccount(
            $controller->userAccountModel->conn, 
            isset($formData['username']) ? $formData['username'] : '', 
            isset($formData['password']) ? $formData['password'] : '', 
            isset($formData['role']) ? $controller->getRoleId($formData['role']) : 0,
            isset($formData['email']) ? $formData['email'] : '',
            isset($formData['phone_num']) ? $formData['phone_num'] : '',
            isset($formData['first_name']) ? $formData['first_name'] : '',
            isset($formData['last_name']) ? $formData['last_name'] : '',
            isset($formData['about']) ? $formData['about'] : '',
            isset($formData['gender']) ? $formData['gender'] : '',
            isset($fileData['profile_image']) ? $fileData['profile_image'] : null
        );
    
        $result = $controller->createUserAccount($userAccount);
    
        return $result ? "New account created successfully." : "Please fill in all the fields";
    }
     

    public function processFormSubmission($controller) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->message = $this->handleAccountCreation($_POST, $_FILES, $controller);
        }
    }
}

// Global Layer: Initializing the components
$database = new Database();
$userAccount = new UserAccount($database->getConnection());
$controller = new CreateUserAccountController($userAccount);
$view = new CreateUserAccountPage();
$view->processFormSubmission($controller);
$view->CreateUserAccountUI();
?>

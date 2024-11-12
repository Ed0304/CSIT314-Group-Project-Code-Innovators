<?php
require "../connectDatabase.php"; // Ensure this file contains your Database class

// ENTITY LAYER: Handles DB interactions
class UserProfile {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createUserProfile($role_name, $role_description) {
        $stmt = $this->conn->prepare("INSERT INTO role (role_name, role_description) VALUES (?, ?)");
        $stmt->bind_param("ss", $role_name, $role_description);
        $success = $stmt->execute();
        $stmt->close();
        return $success; //return true/false
    }
    
    public function checkRoleExists($role_name) {
        $stmt = $this->conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $roleExists = $result->num_rows > 0;
        $stmt->close();
        return $roleExists;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// CONTROLLER LAYER: Manages data flow between Boundary and Entity
class CreateUserProfileController {
    private $entity;

    public function __construct($entity) {
        $this->entity = $entity;
    }

    public function createUserProfile($role_name, $role_description) {
        return $this->entity->createUserProfile($role_name, $role_description); //return true/false
    }

    public function checkRoleExists($role_name) {
        return $this->entity->checkRoleExists($role_name);
    }
}

// BOUNDARY LAYER: Handles user interface tasks for profile creation and validation
class CreateUserProfilePage {
    private $controller;
    private $message;

    public function __construct($controller) {
        $this->controller = $controller;
        $this->message = "";
    }

    // Display function
    public function CreateUserProfileUI() {
        ?>
        <html>
        <head>
            <title>Profile Creation Page</title>
            <style>
                .form-body { text-align: center; }
                .invisible-table {
                    border-collapse: collapse;
                    width: 50%;
                    margin: auto;
                }
                .invisible-table td { border: none; padding: 10px; }
            </style>
        </head>
        <body>
            <div style="background-color: red" class="header">
                <h1 style="text-align:center">Profile Creation</h1>
                <h2 style="text-align:center">Please fill in the following details</h2>
                <h3 style="text-align:center">All fields are mandatory</h3>
            </div>

            <?php if ($this->message): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

            <form class="form-body" method="POST" action="">
                <h4>Note: Profile Name should not exist in the list!</h4>
                <table class="invisible-table">
                    <tr>
                        <td><label style="font-size: 24px">Profile Name:</label></td>
                        <td><input type="text" name="role_name" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Profile Description:</label></td>
                        <td><input type="text" name="role_description" style="font-size: 24px" required/></td>
                    </tr>
                </table>
                <br/>
                <button type="submit" style="font-size: 24px">Create Profile</button>
            </form>
            <br/>
            <form action="admin_manage_user_profiles.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Return to profiles list</button>
            </form>
        </body>
        </html>
        <?php
    }

    // Handle user input and validate before passing to the controller
    public function handleFormSubmission() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $role_name = trim($_POST['role_name'] ?? "");
            $role_description = trim($_POST['role_description'] ?? "");

            if (empty($role_name) || empty($role_description)) {
                $this->message = "All fields are required.";
                return;
            }

            // Check for role existence before passing data to the controller
            $exists = $this->controller->checkRoleExists($role_name);
            if ($exists) {
                $this->message = "Profile name already exists. Please choose a different name.";
                return;
            }

            // Send validated data to controller
            $isProfileCreated = $this->controller->createUserProfile($role_name, $role_description);

            if ($isProfileCreated) {
                header("Location: admin_manage_user_profiles.php");
                exit();
            } else {
                $this->message = "Error creating profile. Please try again.";
            }
        }
    }

}

// MAIN APPLICATION LOGIC
$userProfileEntity = new UserProfile();
$controller = new CreateUserProfileController($userProfileEntity);
$view = new CreateUserProfilePage($controller);

// Process form and render the page
$view->handleFormSubmission();
$view->CreateUserProfileUI();

// Close the database connection
$userProfileEntity->closeConnection();
?>

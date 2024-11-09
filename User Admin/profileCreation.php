<?php
require "../connectDatabase.php"; // Ensure this file contains your Database class

// ENTITY LAYER: Represents the UserProfile data structure
class UserProfileEntity {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function checkRoleExists($role_name) {
        $stmt = $this->conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        return $role ? $role['role_id'] : null;
    }

    public function createProfile($role_name, $role_description) {
        $stmt = $this->conn->prepare("INSERT INTO role (role_name, role_description) VALUES (?, ?)");
        $stmt->bind_param("ss", $role_name, $role_description);
        $stmt->execute();
        $stmt->close();
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// CONTROLLER LAYER: Handles business logic and database interactions for user profiles
class ProfileController {
    private $entity;

    public function __construct($entity) {
        $this->entity = $entity;
    }

    public function handleProfileCreation($formData) {
        $role_name = $formData['role_name'] ?? null;
        $role_description = $formData['role_description'] ?? null;

        if (is_null($role_name) || is_null($role_description)) {
            return null;
        }

        if ($this->entity->checkRoleExists($role_name)) {
            return "Profile name already exists. Please choose a different name.";
        }

        $this->entity->createProfile($role_name, $role_description);
        return true;
    }
}

// BOUNDARY LAYER: Handles user interface tasks for profile creation
class ProfileCreationView {
    private $controller;
    private $message;

    public function __construct($controller, $message = "") {
        $this->controller = $controller;
        $this->message = $message;
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $result = $this->controller->handleProfileCreation($_POST);

            if ($result === true) {
                header("Location: admin_manage_user_profiles.php");
                exit();
            } else {
                $this->message = $result;
            }
        }
    }

    public function render() {
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
}

// MAIN APPLICATION LOGIC
$userProfileEntity = new UserProfileEntity();
$controller = new ProfileController($userProfileEntity);
$view = new ProfileCreationView($controller);

// Handle the request and render the form
$view->handleRequest();
$view->render();

// Close the database connection
$userProfileEntity->closeConnection();

?>

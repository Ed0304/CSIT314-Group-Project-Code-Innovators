<?php
require "../connectDatabase.php"; // Ensure this file contains your Database class

// ENTITY LAYER: Represents the UserProfile data structure
class UserProfileEntity {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Check if a role exists in the database
    public function checkRoleExists($role_name) {
        $stmt = $this->conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        return $role ? $role['role_id'] : null;
    }

    // Inserts a UserProfile entity data into the database
    public function createProfile($role_name, $role_description) {
        $stmt = $this->conn->prepare("INSERT INTO role (role_name, role_description) VALUES (?, ?)");
        $stmt->bind_param("ss", $role_name, $role_description);
        $stmt->execute();
        $stmt->close();
    }
}

// CONTROLLER LAYER: Handles business logic and database interactions for user profiles
class ProfileController {
    private $entity;

    public function __construct($entity) {
        $this->entity = $entity;
    }

    // Handle form submission for profile creation
    public function handleProfileCreation($formData) {
        $role_name = isset($formData['role_name']) ? $formData['role_name'] : null;
        $role_description = isset($formData['role_description']) ? $formData['role_description'] : null;

        // Ensure all required fields are present
        if (is_null($role_name) || is_null($role_description)) {
            return null; // Indicate that fields are missing
        }

        // Check if the role already exists in the database
        if ($this->entity->checkRoleExists($role_name)) {
            return "Profile name already exists. Please choose a different name."; // Return error message
        }

        // Insert the profile into the database
        $this->entity->createProfile($role_name, $role_description);
        return true; // Indicate success
    }
}

// BOUNDARY LAYER: Handles user interface tasks for profile creation
class ProfileCreationView {
    private $message;

    public function __construct($message = "") {
        $this->message = $message;
    }

    // Render the profile creation form
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

            <!-- Display success or error messages -->
            <?php if ($this->message): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

            <!-- Form for profile creation -->
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

// MAIN APPLICATION LOGIC: Connects BCE components and processes form submissions
$database = new Database();
$conn = $database->getConnection();

// Initialize the entity and controller
$userProfileEntity = new UserProfileEntity($conn);
$controller = new ProfileController($userProfileEntity);

$message = null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = $controller->handleProfileCreation($_POST);
    
    // Check the result and redirect if successful
    if ($result === true) {
        header("Location: admin_manage_user_profiles.php");
        exit(); // Make sure to exit after redirection
    } else {
        $message = $result; // Set error message if the role already exists
    }
}

// Initialize and render the boundary layer
$view = new ProfileCreationView($message);
$view->render();

// Close the database connection
$database->closeConnection();
?>

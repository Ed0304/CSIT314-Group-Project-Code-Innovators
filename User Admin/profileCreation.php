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
            <title>Profile Creation</title>
            <style>
                /* General Styling */
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f9f9f9;
                    margin: 0;
                    padding: 0;
                }

                h1, h2, h3, h4 {
                    font-weight: normal;
                    color: #333;
                }

                .header {
                
                    color: white;
                    text-align: center;
                    padding: 15px;
                }

                .form-body {
                    width: 50%;
                    margin: 30px auto;
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
                    text-align: center; /* Add this to center the button */
                }

                .invisible-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .invisible-table td {
                    padding: 15px;
                    font-size: 18px;
                    text-align: left;
                }

                .invisible-table input[type="text"] {
                    width: 100%;
                    padding: 10px;
                    font-size: 18px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    margin-top: 5px;
                }

                .invisible-table input[type="text"]:focus {
                    border-color: #007BFF;
                    outline: none;
                }

                button[type="submit"] {
                    background-color: #007bff; /* Primary color */
                    color: white;
                    font-size: 18px;
                    padding: 12px 30px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    width: 50%; /* Optional: Control the button width if needed */
                    margin-top: 20px;
                    display: inline-block; /* Ensures the button is centered */
                }

                button[type="submit"]:hover {
                    background-color: #0056b3; /* Darker on hover */
                }

                .message {
                    font-size: 18px;
                    color: #e74c3c;
                    margin-top: 20px;
                    text-align: center;
                }

                .form-footer {
                    text-align: center;
                    margin-top: 20px;
                }

                .form-footer button {
                    background-color: #5cb85c; /* Neutral color for non-primary actions */
                    color: white;
                    font-size: 18px;
                    padding: 12px 30px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    width: 20%;
                }

                .form-footer button:hover {
                    background-color:  #4cae4c;
                }
            </style>
        </head>
        <body>

            <div class="header">
                <h1>Profile Creation</h1>
                <h2>Please fill in the following details</h2>
                <h3>All fields are mandatory</h3>
            </div>

            <?php if ($this->message): ?>
                <p class="message"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

            <form class="form-body" method="POST" action="">
                <h4>Note: Profile Name should not exist in the list!</h4>
                <table class="invisible-table">
                    <tr>
                        <td><label>Profile Name:</label></td>
                        <td><input type="text" name="role_name" required/></td>
                    </tr>
                    <tr>
                        <td><label>Profile Description:</label></td>
                        <td><input type="text" name="role_description" required/></td>
                    </tr>
                </table>
                <button type="submit">Create Profile</button>
            </form>

            <div class="form-footer">
                <form action="admin_manage_user_profiles.php">
                    <button type="submit">Return to Profiles List</button>
                </form>
            </div>

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

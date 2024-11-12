<?php
include "../connectDatabase.php";
session_start();

$role_id = isset($_GET['role_id']) ? $_GET['role_id'] : null;
if (!$role_id) {
    die("UserProfile ID not provided.");
}

// Entity Layer: UserProfile class for interacting with the database
class UserProfile {
    private $conn;
    private $role_id;
    private $role_description;

    public function __construct($role_id = null) {
        $this->conn = $this->getConnection();
        if ($role_id) {
            $this->role_id = $role_id;
            $this->loadRole();
        }
    }

    private function getConnection() {
        // Assuming connectDatabase.php sets up $conn globally
        global $conn;
        return $conn;
    }

    public function getRoleId() {
        return $this->role_id;
    }

    public function getRoleDescription() {
        return $this->role_description;
    }

    public function setRoleDescription($role_description) {
        $this->role_description = $role_description;
    }

    public function loadRole() {
        $stmt = $this->conn->prepare("SELECT * FROM role WHERE role_id = ?");
        $stmt->bind_param("i", $this->role_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $this->role_description = $row['role_description'];
        } else {
            throw new Exception("User Profile not found");
        }
    }

    public function updateRoleDescription() {
        $stmt = $this->conn->prepare("UPDATE role SET role_description = ? WHERE role_id = ?");
        $stmt->bind_param("si", $this->role_description, $this->role_id);
        return $stmt->execute();
    }
}

// Control Layer: UpdateUserProfileController class for managing data flow between boundary and entity layers
class UpdateUserProfileController {
    private $profile;

    public function __construct($role_id) {
        $this->profile = new UserProfile($role_id);
    }

    public function getRole() {
        return $this->profile;
    }

    public function updateRoleDescription($new_description) {
        $this->profile->setRoleDescription($new_description);
        return $this->profile->updateRoleDescription();
    }
}

// Boundary Layer: UpdateUserProfilePage class for handling form display and user interaction
class UpdateUserProfilePage {
    private $profileController;

    public function __construct($profileController) {
        $this->profileController = $profileController;
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_description'])) {
            $new_description = trim($_POST['role_description']);

            if ($this->profileController->updateRoleDescription($new_description)) {
                echo "<p style='color: green;'>User Profile description updated successfully.</p>";
            } else {
                echo "<p style='color: red;'>Error updating description.</p>";
            }
        }
    }

    public function UpdateUserProfileUI() {
        $profile = $this->profileController->getRole();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Update UserProfile Description</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                }

                h1 {
                    color: #333;
                    text-align: center;
                }

                form {
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    max-width: 600px;
                    margin: auto;
                }

                label {
                    display: block;
                    margin-bottom: 10px;
                    font-weight: bold;
                }

                textarea {
                    width: 100%;
                    height: 120px;
                    margin-bottom: 20px;
                    padding: 12px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    box-sizing: border-box;
                }

                button {
                    background-color: #5cb85c;
                    color: white;
                    border: none;
                    padding: 12px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                }

                button:hover {
                    background-color: #4cae4c;
                }

                .return-button {
                    margin-top: 20px;
                    display: inline-block;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    padding: 12px 18px;
                    border-radius: 5px;
                    font-size: 16px;
                    text-align: center;
                }

                .return-button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <h1>Update User Profile Description</h1>
            <form action="" method="post">
                <label for="role_description">New Description:</label>
                <textarea name="role_description" id="role_description" required><?php echo htmlspecialchars($profile->getRoleDescription()); ?></textarea>
                <button type="submit">Update Description</button>
            </form>
            <a href="admin_manage_user_profiles.php" class="return-button">Return</a>
        </body>
        </html>
        <?php
    }
}

// Global Layer: Initializing the components
$profileController = new UpdateUserProfileController($role_id);
$roleBoundary = new UpdateUserProfilePage($profileController);
$roleBoundary->handleFormSubmission();
$roleBoundary->UpdateUserProfileUI();
?>

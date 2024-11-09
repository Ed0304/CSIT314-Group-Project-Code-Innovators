<?php
include "../connectDatabase.php";
session_start();

$role_id = isset($_GET['role_id']) ? $_GET['role_id'] : null;
if (!$role_id) {
    die("UserProfile ID not provided.");
}

// Entity class: Handles database operations and acts as the data structure for UserProfile
class UserProfile {
    private $conn;
    private $role_id;
    private $role_description;

    public function __construct($connection, $role_id = null) {
        $this->conn = $connection;
        if ($role_id) {
            $this->role_id = $role_id;
            $this->loadRole();
        }
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
            throw new Exception("UserProfile not found");
        }
    }

    public function updateRoleDescription() {
        $stmt = $this->conn->prepare("UPDATE role SET role_description = ? WHERE role_id = ?");
        $stmt->bind_param("si", $this->role_description, $this->role_id);
        return $stmt->execute();
    }
}

// Controller class: Calls methods in the UserProfile entity
class UpdateUserProfileController {
    private $role;

    public function __construct($connection, $role_id) {
        $this->role = new UserProfile($connection, $role_id);
    }

    public function getRole() {
        return $this->role;
    }

    public function updateRoleDescription($new_description) {
        $this->role->setRoleDescription($new_description);
        return $this->role->updateRoleDescription();
    }
}

// Boundary class: Handles display and form interactions
class UpdateUserProfilePage {
    private $roleController;
    private $role;

    public function __construct($connection, $role_id) {
        $this->roleController = new UpdateUserProfileController($connection, $role_id);
        $this->role = $this->roleController->getRole();
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_description'])) {
            $new_description = trim($_POST['role_description']);

            if ($this->roleController->updateRoleDescription($new_description)) {
                echo "<p style='color: green;'>User Profile description updated successfully.</p>";
            } else {
                echo "<p style='color: red;'>Error updating description.</p>";
            }
        }
    }

    public function renderForm() {
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
                }
                form {
                    background: white;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    max-width: 500px;
                    margin: auto;
                }
                label {
                    display: block;
                    margin-bottom: 10px;
                    font-weight: bold;
                }
                textarea {
                    width: 100%;
                    height: 100px;
                    margin-bottom: 10px;
                    padding: 10px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                }
                button {
                    background-color: #5cb85c;
                    color: white;
                    border: none;
                    padding: 10px 15px;
                    border-radius: 5px;
                    cursor: pointer;
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
                    padding: 10px 15px;
                    border-radius: 5px;
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
                <textarea name="role_description" id="role_description" required><?php echo htmlspecialchars($this->role->getRoleDescription()); ?></textarea>
                <button type="submit">Update Description</button>
            </form>
            <a href="admin_manage_user_profiles.php" class="return-button">Return</a>
        </body>
        </html>
        <?php
    }
}

$roleBoundary = new UpdateUserProfilePage($conn, $role_id);
$roleBoundary->handleFormSubmission();
$roleBoundary->renderForm();
?>

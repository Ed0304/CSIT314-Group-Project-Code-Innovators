<?php
include "../connectDatabase.php";
session_start();

$userprofile_id = isset($_GET['role_id']) ? $_GET['role_id'] : null;
if (!$userprofile_id) {
    die("UserProfile ID not provided.");
}

// Entity class: Handles database operations and acts as the data structure for UserProfile
class UserProfile {
    private $conn;
    private $userprofile_id;
    private $userprofile_description;

    public function __construct($userprofile_id = null) {
        $this->conn = $this->getConnection();
        if ($userprofile_id) {
            $this->role_id = $userprofile_id;
            $this->loadUserProfile();
        }
    }

    private function getConnection() {
        // Assuming connectDatabase.php sets up $conn globally
        global $conn;
        return $conn;
    }

    public function getUserProfileId() {
        return $this->role_id;
    }

    public function getUserProfileDescription() {
        return $this->role_description;
    }

    public function setUserProfileDescription($userprofile_description) {
        $this->role_description = $userprofile_description;
    }

    public function loadUserProfile() {
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

    public function updateUserProfileDescription() {
        $stmt = $this->conn->prepare("UPDATE role SET role_description = ? WHERE role_id = ?");
        $stmt->bind_param("si", $this->role_description, $this->role_id);
        return $stmt->execute();
    }
}

// Controller class: Calls methods in the UserProfile entity
class UpdateUserProfileDescriptionController {
    private $profile;

    public function __construct($userprofile_id) {
        $this->profile = new UserProfile($userprofile_id);
    }

    public function getUserProfile() {
        return $this->profile;
    }

    public function updateUserProfileDescription($new_description) {
        $this->profile->setUserProfileDescription($new_description);
        return $this->profile->updateUserProfileDescription();
    }
}

// Boundary class: Handles display and form interactions
class UpdateUserProfileDescriptionPage {
    private $profileController;

    public function __construct($profileController) {
        $this->profileController = $profileController;
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_description'])) {
            $new_description = trim($_POST['role_description']);

            if ($this->profileController->updateUserProfileDescription($new_description)) {
                echo "<p style='color: green;'>User Profile description updated successfully.</p>";
            } else {
                echo "<p style='color: red;'>Error updating description.</p>";
            }
        }
    }

    public function UpdateUserProfileDescriptionUI() {
        $profile = $this->profileController->getUserProfile();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Update User Profile Description</title>
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
                <textarea name="role_description" id="role_description" required><?php echo htmlspecialchars($profile->getUserProfileDescription()); ?></textarea>
                <button type="submit">Update Description</button>
            </form>
            <a href="admin_manage_user_profiles.php" class="return-button">Return</a>
        </body>
        </html>
        <?php
    }
}

// Main script to initialize the Controller and Boundary
$profileController = new UpdateUserProfileDescriptionController($userprofile_id);
$userprofileBoundary = new UpdateUserProfileDescriptionPage($profileController);
$userprofileBoundary->handleFormSubmission();
$userprofileBoundary->UpdateUserProfileDescriptionUI();
?>

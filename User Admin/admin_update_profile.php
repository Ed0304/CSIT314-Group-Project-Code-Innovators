<?php
require '../connectDatabase.php';
$userprofile_id = isset($_GET['userprofile_id']) ? intval($_GET['userprofile_id']) : null;
// Entity class: Handles database operations and acts as the data structure for UserProfile
class UserProfile {
    private $conn;
    private $userprofile_id;
    private $userprofile_description; // Use 'userprofile_description' consistently

    public function __construct($userprofile_id = null) {
        $this->conn = $this->getConnection();
        if ($userprofile_id) {
            $this->userprofile_id = $userprofile_id;
            $this->userprofile_description = $userprofile_description;
            $this->loadUserProfile();
        }
    }

    private function getConnection() {
        global $conn;
        return $conn;
    }

    public function getUserProfileId() {
        return $userprofile_id;
    }

    public function getUserProfileDescription() {
        return $userprofile_description;
    }

    public function setUserProfileDescription($userprofile_description) {
        $this->userprofile_description = $userprofile_description;
    }

    public function loadUserProfile() {
        $stmt = $this->conn->prepare("SELECT * FROM role WHERE role_id = ?");
        $stmt->bind_param("i", $this->userprofile_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $this->userprofile_description = $row['role_description']; // Maps correctly to the database column
        } 
    }

    public function updateUserProfileDescription(UserProfile $userProfile) {
        $stmt = $this->conn->prepare("UPDATE role SET role_description = ? WHERE role_id = ?");
        $stmt->bind_param("si", $userProfile->userprofile_description, $userProfile->userprofile_id);
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

    public function updateUserProfileDescription(UserProfile $userProfile) {
        return $userProfile->updateUserProfileDescription($userProfile);
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
            $profile = $this->profileController->getUserProfile();
            $profile->setUserProfileDescription($new_description);

            if ($this->profileController->updateUserProfileDescription($profile)) {
                echo "<p class='success-message'>User Profile description updated successfully.</p>";
            } else {
                echo "<p class='error-message'>Error updating description.</p>";
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
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Roboto', sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }

                h1 {
                    color: #333;
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 24px;
                }

                .form-container {
                    background: #fff;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    max-width: 600px;
                    margin: auto;
                }

                label {
                    display: block;
                    margin-bottom: 10px;
                    font-weight: 500;
                    font-size: 16px;
                }

                textarea {
                    width: 100%;
                    height: 120px;
                    margin-bottom: 20px;
                    padding: 12px;
                    border: 1px solid #ccc;
                    border-radius: 6px;
                    font-size: 16px;
                    resize: vertical;
                }

                button {
                    background-color: #007bff;
                    color: white;
                    border: none;
                    padding: 14px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                    width: 100%;
                    transition: background-color 0.3s ease;
                }

                button:hover {
                    background-color: #0056b3;
                }

                .return-button {
                    margin-top: 20px;
                    display: inline-block;
                    background-color: #5cb85c;
                    color: white;
                    text-decoration: none;
                    padding: 12px 1px;
                    border-radius: 6px;
                    font-size: 16px;
                    text-align: center;
                    transition: background-color 0.3s ease;
                }

                .return-button:hover {
                    background-color: #4cae4c;
                }

                .success-message {
                    color: #28a745;
                    font-size: 16px;
                    text-align: center;
                    margin-top: 20px;
                }

                .error-message {
                    color: #dc3545;
                    font-size: 16px;
                    text-align: center;
                    margin-top: 20px;
                }

                .form-container a {
                    display: inline-block;
                    margin-top: 10px;
                    text-align: center;
                    width: 100%;
                }
            </style>
        </head>
        <body>
            <h1>Update User Profile Description</h1>
            <div class="form-container">
                <form action="" method="post">
                    <label for="role_description">New Description:</label>
                    <textarea name="role_description" id="role_description" required><?php echo htmlspecialchars($profile->getUserProfileDescription()); ?></textarea>
                    <button type="submit">Update Description</button>
                </form>
                <a href="admin_manage_user_profiles.php" class="return-button">Return</a>
            </div>
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

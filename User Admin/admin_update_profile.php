<?php
require '../connectDatabase.php';
if (!$this->conn) {
    echo "Database connection error: " . mysqli_connect_error();
}

$userprofile_id = isset($_GET['userprofile_id']) ? intval($_GET['userprofile_id']) : null;

if ($userprofile_id === null) {
    echo "Error: userprofile_id is null.";
}


// Entity class: Handles database operations and acts as the data structure for UserProfile
class UserProfile {
    private $conn;
    private $userprofile_id;
    private $userprofile_description;

    public function __construct($userprofile_id = null) {
        $this->conn = $this->getConnection();
        if ($userprofile_id) {
            $this->userprofile_id = $userprofile_id;
            $this->loadUserProfile();
        }
    }

    private function getConnection() {
        global $conn;
        return $conn;
    }

    public function getUserProfileId() {
        return $this->userprofile_id;
    }

    public function getUserProfileDescription() {
        return $this->userprofile_description;
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
            $this->userprofile_description = $row['role_description'];
        }
    }

    public function updateUserProfileDescription(UserProfile $userProfile) {
        $stmt = $this->conn->prepare("UPDATE role SET role_description = ? WHERE role_id = ?");
        $stmt->bind_param("si", $userProfile->userprofile_description, $userProfile->userprofile_id);

        // Debugging: Check if the statement prepares correctly
        if (!$stmt) {
            echo "Error preparing statement: " . $this->conn->error;
            return false;
        }

        if (!$stmt->execute()) {
            echo "SQL execution error: " . $stmt->error;
            return false;
        }


        $executeResult = $stmt->execute();

        // Debugging: Check if the statement executes correctly
        if (!$executeResult) {
            echo "Error executing statement: " . $stmt->error;
            return false;
        }

        return $executeResult;
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
                /* Styles omitted for brevity */
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

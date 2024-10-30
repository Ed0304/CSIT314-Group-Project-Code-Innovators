<?php
require 'connectDatabase.php';

// ENTITY LAYER: Represents User Profile
class UserProfile {
    public function getProfileDetails($conn, $user_id) {
        $stmt = $conn->prepare("SELECT * FROM profile WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateProfile($conn, $data) {
        // Prepare the SQL statement for profile update
        $stmt = $conn->prepare(
            "UPDATE profile SET first_name = ?, last_name = ?, gender = ?, about = ?, profile_image = ?, status_id = ? WHERE user_id = ?"
        );
    
        // Handle the image upload
        if ($data['profile_image'] !== null) {
            // Bind parameters including the BLOB
            $stmt->bind_param(
                "ssssbii",
                $data['first_name'],
                $data['last_name'],
                $data['gender'],
                $data['about'],
                $data['profile_image'], // BLOB data
                $data['status_id'],
                $data['user_id']
            );
        } else {
            // Prepare statement excluding the BLOB if no new image
            $stmt = $conn->prepare(
                "UPDATE profile SET first_name = ?, last_name = ?, gender = ?, about = ?, status_id = ? WHERE user_id = ?"
            );
    
            // Bind parameters excluding the BLOB
            $stmt->bind_param(
                "ssssi",
                $data['first_name'],
                $data['last_name'],
                $data['gender'],
                $data['about'],
                $data['status_id'],
                $data['user_id']
            );
        }
    
        // Execute the statement
        if (!$stmt->execute()) {
            // Log the error for debugging
            error_log("Profile update failed: " . $stmt->error);
            return false;
        }
        return true;
    }
    
}


// CONTROL LAYER: Handles profile updates
class UserProfileController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest($conn, $user_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $data['profile_image'] = file_get_contents($_FILES['profile_image']['tmp_name']);
            } else {
                $data['profile_image'] = null;
            }
            $this->model->updateProfile($conn, $data);
            header("Location: admin_manage_user_profiles.php");
            exit();
        }
        return $this->model->getProfileDetails($conn, $user_id);
    }
}

// VIEW LAYER: Displays the form
class UserProfileView {
    private $profile;

    public function __construct($profile) {
        $this->profile = $profile;
    }

    public function render() {
        ?>
        <html>
        <head>
            <style>
                .form-body {
                    font-size: 24px;
                    text-align: center;
                }
                h1 {
                    font-size: 48px;
                    text-align: center;
                }
                table {
                    font-size: 24px;
                    margin: 0 auto;
                    border-collapse: collapse;
                }
                td {
                    padding: 10px;
                }
            </style>
        </head>
        <body>
        <h1>Update User Profile</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($this->profile['user_id']); ?>" />
            <table>
                <tr>
                    <td class="form-group">
                        <label for="first_name" style="font-size:24px">First Name:</label>
                        <input type="text" id="first_name" name="first_name" style="font-size:24px" value="<?= htmlspecialchars($this->profile['first_name']); ?>" required />
                    </td>
                </tr>
                <tr>
                    <td class="form-group">
                        <label for="last_name" style="font-size:24px" >Last Name:</label>
                        <input type="text" id="last_name" name="last_name" style="font-size:24px" value="<?= htmlspecialchars($this->profile['last_name']); ?>" required />
                    </td>
                </tr>
                <tr>
                    <td class="form-group">
                        <label style="font-size:24px">Gender:</label>
                        <label><input type="radio" name="gender" value="M" style="font-size:24px"<?= $this->profile['gender'] == 'M' ? 'checked' : ''; ?>> Male</label>
                        <label><input type="radio" name="gender" value="F" style="font-size:24px"<?= $this->profile['gender'] == 'F' ? 'checked' : ''; ?>> Female</label>
                    </td>
                </tr>
                <tr>
                    <td class="form-group">
                        <label for="about"style="font-size:24px">About:</label>
                        <textarea id="about" name="about" style="font-size:24px" required><?= htmlspecialchars($this->profile['about']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="form-group">
                        <label for="profile_image" style="font-size:24px">Profile Image:</label>
                        <input type="file" id="profile_image" name="profile_image" style="font-size:24px"/>
                    </td>
                </tr>
                <tr>
                    <td class="form-group">
                        <label for="status_id" style="font-size:24px">Status:</label>
                        <select id="status_id" name="status_id" style="font-size:24px">
                            <option value="1" style="font-size:24px"<?= $this->profile['status_id'] == 1 ? 'selected' : ''; ?>>Active</option>
                            <option value="2" style="font-size:24px" <?= $this->profile['status_id'] == 2 ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <button type="submit" style="font-size:24px">Update Profile</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href="admin_manage_user_profiles.php">
                            <button type="button" style="background-color: #ccc; font-size:24px;">Return to Dashboard</button>
                        </a>
                    </td>
                </tr>
            </table>
        </form>
        </body>
        </html>
        <?php
    }
}

$user_id = $_GET['user_id'];
$model = new UserProfile();
$controller = new UserProfileController($model);
$profile = $controller->handleRequest($conn, $user_id);
$view = new UserProfileView($profile);
$view->render();
?>

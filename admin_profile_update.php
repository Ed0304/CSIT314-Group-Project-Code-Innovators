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
        $stmt = $conn->prepare(
            "UPDATE profile SET first_name = ?, last_name = ?, gender = ?, about = ?, profile_image = ?, status_id = ? WHERE user_id = ?"
        );

        $stmt->bind_param(
            "sssbsi",
            $data['first_name'],
            $data['last_name'],
            $data['gender'],
            $data['about'],
            $data['profile_image'],
            $data['status_id'],
            $data['user_id']
        );
        return $stmt->execute();
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
        <body>
        <h1>Update User Profile</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= $this->profile['user_id']; ?>" />
            First Name: <input type="text" name="first_name" value="<?= $this->profile['first_name']; ?>" required /><br/>
            Last Name: <input type="text" name="last_name" value="<?= $this->profile['last_name']; ?>" required /><br/>
            Gender:
            <input type="radio" name="gender" value="M" <?= $this->profile['gender'] == 'M' ? 'checked' : ''; ?>> Male
            <input type="radio" name="gender" value="F" <?= $this->profile['gender'] == 'F' ? 'checked' : ''; ?>> Female<br/>
            About: <textarea name="about" required><?= $this->profile['about']; ?></textarea><br/>
            Profile Image: <input type="file" name="profile_image" /><br/>
            Status:
            <select name="status_id">
                <option value="1" <?= $this->profile['status_id'] == 1 ? 'selected' : ''; ?>>Active</option>
                <option value="2" <?= $this->profile['status_id'] == 2 ? 'selected' : ''; ?>>Suspended</option>
            </select><br/>
            <button type="submit">Update Profile</button>
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

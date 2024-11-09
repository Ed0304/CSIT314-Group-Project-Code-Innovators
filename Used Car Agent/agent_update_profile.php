<?php
require '../connectDatabase.php';

// ENTITY LAYER: Represents User Profile
class UserAccount {
    public function getProfileDetails($conn, $profile_id) {
        $stmt = $conn->prepare("SELECT p.*, u.phone_num, u.email FROM profile p JOIN users u ON p.user_id = u.user_id WHERE p.profile_id = ?");
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfile($conn, $data) {
        // Prepare the SQL statement for profile update
        $stmt = $conn->prepare(
            "UPDATE profile p 
             JOIN users u ON p.user_id = u.user_id 
             SET p.first_name = ?, p.last_name = ?, p.gender = ?, p.about = ?, p.profile_image = ?, u.phone_num = ?, u.email = ? 
             WHERE p.profile_id = ?"
        );
    
        // Handle the image upload
        if ($data['profile_image'] !== null) {
            // Bind parameters including the BLOB
            $stmt->bind_param(
                "ssssbsii",
                $data['first_name'],
                $data['last_name'],
                $data['gender'],
                $data['about'],
                $data['profile_image'], // BLOB data
                $data['phone_num'], // Phone number
                $data['email'], // Email
                $data['profile_id'] // Changed to profile_id
            );
        } else {
            // Prepare statement excluding the BLOB if no new image
            $stmt = $conn->prepare(
                "UPDATE profile p 
                 JOIN users u ON p.user_id = u.user_id 
                 SET p.first_name = ?, p.last_name = ?, p.gender = ?, p.about = ?, u.phone_num = ?, u.email = ? 
                 WHERE p.profile_id = ?"
            );
    
            // Bind parameters excluding the BLOB
            $stmt->bind_param(
                "ssssisi",
                $data['first_name'],
                $data['last_name'],
                $data['gender'],
                $data['about'], // Include about
                $data['phone_num'], // Phone number
                $data['email'], // Email
                $data['profile_id'] // Changed to profile_id
            );
        }
    
        // Execute the statement
        if (!$stmt->execute()) {
            // Log the error for debugging
            error_log("Profile update failed: " . $stmt->error);
            return false;
        }
    
        // Optionally check for affected rows
        if ($stmt->affected_rows === 0) {
            error_log("No rows were updated. Profile ID: " . $data['profile_id']);
        }
    
        return true;
    }
}

// CONTROL LAYER: Handles profile updates
class UpdateAccountInformationController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest($conn, $profile_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['phone_num'] = $_POST['phone_num']; // Capture phone number
            $data['email'] = $_POST['email']; // Capture email

            if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $data['profile_image'] = file_get_contents($_FILES['profile_image']['tmp_name']);
            } else {
                $data['profile_image'] = null;
            }
            $this->model->updateProfile($conn, $data);
            header("Location: agent_manage_profile.php");
            exit();
        }
        return $this->model->getProfileDetails($conn, $profile_id);
    }
}

// VIEW LAYER: Displays the form
class UpdateAccountInformationPage {
    private $profile;

    public function __construct($profile) {
        $this->profile = $profile;
    }

    public function render() {
        ?>
        <html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #ffffff;
            padding: 20px;
            width: 60%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        h1 {
            font-size: 32px;
            margin: 0 0 20px;
            text-align: center;
            color: #333;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        .form-group label {
            font-size: 18px;
            color: #555;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="file"],
        .form-group input[type="number"],
        .form-group textarea {
            font-size: 16px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }
        .form-group input[type="radio"] {
            margin-right: 5px;
        }
        .form-group textarea {
            resize: vertical;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .button-group button {
            background-color: #28a745;
            color: #fff;
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button-group button:hover {
            background-color: #218838;
        }
        .button-group .secondary-button {
            background-color: #6c757d;
            margin-left: 10px;
        }
        .button-group .secondary-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Update User Profile</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="profile_id" value="<?= htmlspecialchars($this->profile['profile_id']); ?>" />
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($this->profile['first_name']); ?>" required />
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($this->profile['last_name']); ?>" required />
            </div>
            <div class="form-group">
                <label>Gender:</label>
                <label><input type="radio" name="gender" value="M" <?= $this->profile['gender'] == 'M' ? 'checked' : ''; ?>> Male</label>
                <label><input type="radio" name="gender" value="F" <?= $this->profile['gender'] == 'F' ? 'checked' : ''; ?>> Female</label>
            </div>
            <div class="form-group">
                <label for="about">About:</label>
                <textarea id="about" name="about" required><?= htmlspecialchars($this->profile['about']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="phone_num">Phone Number:</label>
                <input type="text" id="phone_num" name="phone_num" value="<?= htmlspecialchars($this->profile['phone_num']); ?>" required />
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($this->profile['email']); ?>" required />
            </div>
            <div class="form-group">
                <label for="profile_image">Profile Image:</label>
                <input type="file" id="profile_image" name="profile_image" />
            </div>
            <div class="button-group">
                <button type="submit">Update Profile</button>
                <a href="agent_manage_profile.php">
                    <button type="button" class="secondary-button">Return to Profile Info</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>

        <?php
    }
}

$profile_id = $_GET['profile_id']; // Changed to profile_id
$model = new UserAccount();
$controller = new UpdateAccountInformationController($model);
$profile = $controller->handleRequest($conn, $profile_id); // Changed to profile_id
$view = new UpdateAccountInformationPage($profile);
$view->render();
?>

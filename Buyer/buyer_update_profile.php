<?php
require '../connectDatabase.php';

// ENTITY LAYER: Handles data structure and database interaction for User Account and Profile
class UserAccount {
    private $conn;
    public $user_id;
    public $username;
    public $password;
    public $email;
    public $phone_num;
    public $status_id;
    public $profile_image;
    public $first_name;    // Profile table field
    public $last_name;     // Profile table field
    public $about;         // Profile table field

    public function __construct($userData = null) {
        $database = new Database();
        $this->conn = $database->getConnection();

        if ($userData) {
            $this->user_id = $userData['user_id'];
            $this->username = $userData['username'];
            $this->password = $userData['password'];
            $this->email = $userData['email'];
            $this->phone_num = $userData['phone_num'];
            $this->status_id = $userData['status_id'];
            $this->profile_image = $userData['profile_image'];
            $this->first_name = $userData['first_name'];
            $this->last_name = $userData['last_name'];
            $this->about = $userData['about'];
        }
    }

    public function getUserDetails($username) {
        $stmt = $this->conn->prepare("
            SELECT u.*, p.first_name, p.last_name, p.about 
            FROM users u
            LEFT JOIN profile p ON u.user_id = p.user_id
            WHERE u.username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();

        if ($userData) {
            $this->user_id = $userData['user_id'];
            $this->username = $userData['username'];
            $this->password = $userData['password'];
            $this->email = $userData['email'];
            $this->phone_num = $userData['phone_num'];
            $this->status_id = $userData['status_id'];
            $this->profile_image = null;
            $this->first_name = $userData['first_name'];
            $this->last_name = $userData['last_name'];
            $this->about = $userData['about'];
            return $this;
        }
        return null;
    }

    public function updateBuyerAccountInformation($userAccount) {
        // Update users table
        $stmt1 = $this->conn->prepare(
            "UPDATE users SET password = ?, email = ?, phone_num = ? WHERE user_id = ?"
        );
        $stmt1->bind_param(
            "sssi",
            $userAccount['password'],
            $userAccount['email'],
            $userAccount['phone_num'],
            $userAccount['user_id']
        );
        $success1 = $stmt1->execute();
        $stmt1->close();
    
        // Update profile table - Conditional profile image update
        if ($userAccount['profile_image'] !== null) {
            // Profile image is provided, include it in the update
            $stmt2 = $this->conn->prepare(
                "UPDATE profile SET first_name = ?, last_name = ?, about = ?, profile_image = ? WHERE user_id = ?"
            );
            $stmt2->bind_param(
                "ssssi",
                $userAccount['first_name'],
                $userAccount['last_name'],
                $userAccount['about'],
                $userAccount['profile_image'],
                $userAccount['user_id']
            );
        } else {
            // No profile image provided, exclude it from the update
            $stmt2 = $this->conn->prepare(
                "UPDATE profile SET first_name = ?, last_name = ?, about = ? WHERE user_id = ?"
            );
            $stmt2->bind_param(
                "sssi",
                $userAccount['first_name'],
                $userAccount['last_name'],
                $userAccount['about'],
                $userAccount['user_id']
            );
        }
        
        $success2 = $stmt2->execute();
        $stmt2->close();
    
        return $success1 && $success2;
    }
    

    public function closeConnection() {
        $this->conn->close();
    }
}

// CONTROL LAYER: Manages account updates and data retrieval
class UpdateBuyerAccountInformationController {
    private $useraccount;

    public function __construct() {
        $this->useraccount = new UserAccount();
    }

    public function getUserAccount($username) {
        return $this->useraccount->getUserDetails($username);
    }

    public function updateBuyerAccountInformation($userAccount) {
        return $this->useraccount->updateBuyerAccountInformation($userAccount);
    }
}

// BOUNDARY LAYER: Renders the form and handles form submission
class UpdateBuyerAccountInformationPage {
    private $controller;

    public function __construct() {
        $this->controller = new UpdateBuyerAccountInformationController();
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Assign POST data to userAccount array
            $userAccount = $_POST;
    
            // Handle profile image upload if a file is provided
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                // If a new file is uploaded, convert it to base64
                $imageData = file_get_contents($_FILES['profile_image']['tmp_name']);
                $userAccount['profile_image'] = $imageData;
            } else {
                // If no file uploaded, retrieve existing profile image from the database
                $existingUser = $this->controller->getUserAccount($userAccount['username']);
                $userAccount['profile_image'] = $existingUser->profile_image;
            }
    
            // Proceed with the update
            $updateSuccess = $this->controller->updateBuyerAccountInformation($userAccount);
            
    
            if ($updateSuccess) {
                header("Location: buyer_manage_profile.php");
                exit();
            } else {
                echo "Error updating user account.";
            }
        }
    }
    
    
    
    

    public function updateBuyerAccountInformationUI() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return;
        }

        if (!isset($_GET['username'])) {
            header("Location: buyer_update_profile.php");
            exit();
        }

        $username = $_GET['username'];
        $userAccount = $this->controller->getUserAccount($username);
        

        if (!$userAccount) {
            die("User not found.");
        }
        ?>
        <html>
        <head>
            <style>
                .form-body { font-size: 24px; text-align: center; }
                h1 { font-size: 48px; text-align: center; }
                table { font-size: 24px; margin: 0 auto; border-collapse: collapse; }
                td { padding: 10px; }
            </style>
        </head>
        <body>
        <h1>Update User Account</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($userAccount->user_id); ?>" />
            <table>
                <tr>
                    <td><label for="first_name">First Name:</label></td>
                    <td><input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($userAccount->first_name); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="last_name">Last Name:</label></td>
                    <td><input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($userAccount->last_name); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="username">Username:</label></td>
                    <td><input type="text" id="username" name="username" value="<?= htmlspecialchars($userAccount->username); ?>" disabled /></td>
                </tr>
                <tr>
                    <td><label for="password">Password:</label></td>
                    <td><input type="password" id="password" name="password" value="<?= htmlspecialchars($userAccount->password); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="email">Email:</label></td>
                    <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($userAccount->email); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="phone_num">Phone:</label></td>
                    <td><input type="text" id="phone_num" name="phone_num" value="<?= htmlspecialchars($userAccount->phone_num); ?>" required /></td>
                </tr>
                <tr>
                    <td><label for="profile_image">Profile Picture:</label></td>
                    <td><input type="file" id="profile_image" name="profile_image" /></td>
                </tr>
                <tr>
                    <td><label for="about">About:</label></td>
                    <td><textarea id="about" name="about"><?= htmlspecialchars($userAccount->about); ?></textarea></td>
                </tr>
                <tr>
                    <td><button type="submit">Update Account</button></td>
                    <td><button type="button" onclick="window.location.href='buyer_manage_profile.php'">Return to dashboard</button></td>
                </tr>
            </table>
        </form>
        </body>
        </html>
        <?php
    }
}

$page = new UpdateBuyerAccountInformationPage();
$page->handleFormSubmission();
$page->updateBuyerAccountInformationUI();
?>

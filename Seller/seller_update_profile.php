<?php
require '../connectDatabase.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    public function updateSellerAccountInformation($userAccount) {
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
        if (!$success1) {
            echo "Error in users table update: " . $stmt1->error; // Log SQL error here
        }
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
        if (!$success2) {
            echo "Error in profile table update: " . $stmt2->error; // Log SQL error here
        }

        $stmt2->close();
    
        return $success1 && $success2;
    }
    

    public function closeConnection() {
        $this->conn->close();
    }
}

// CONTROL LAYER: Manages account updates and data retrieval
class UpdateSellerAccountInformationController {
    private $useraccount;

    public function __construct() {
        $this->useraccount = new UserAccount();
    }

    public function getUserAccount($username) {
        return $this->useraccount->getUserDetails($username);
    }

    public function updateSellerAccountInformation($userAccount) {
        return $this->useraccount->updateSellerAccountInformation($userAccount);
    }
}

// BOUNDARY LAYER: Renders the form and handles form submission
class UpdateSellerAccountInformationPage {
    private $controller;

    public function __construct() {
        $this->controller = new UpdateSellerAccountInformationController();
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Assign POST data to userAccount array
            var_dump($_POST);
            $userAccount = $_POST;
            var_dump($userAccount);

    
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
            $updateSuccess = $this->controller->updateSellerAccountInformation($userAccount);
            
    
            if ($updateSuccess) {
                header("Location: seller_manage_profile.php");
                exit();
            } else {
                echo "Error updating user account.";
            }
        }
    }
    
    public function updateSellerAccountInformationUI() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return;
        }

        if (!isset($_GET['username'])) {
            header("Location: seller_update_profile.php");
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
                    /* Reusing the provided CSS for consistency */
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f8f9fa;
                    }

                    header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 20px;
                        background-color: #343a40;
                        color: #ffffff;
                    }

                    header h1 {
                        margin: 0;
                        font-size: 1.5em;
                    }

                    header a {
                        text-decoration: none;
                        color: #ffffff;
                        background-color: #007bff;
                        padding: 8px 16px;
                        border-radius: 4px;
                        font-size: 0.9em;
                    }

                    header a:hover {
                        background-color: #0056b3;
                    }

                    h1 {
                        font-size: 48px;
                        text-align: center;
                        margin-top: 20px;
                        color: #343a40;
                    }

                    table {
                        width: 60%;
                        margin: 20px auto;
                        border-collapse: collapse;
                        background-color: white;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }

                    table,
                    th,
                    td {
                        border: 1px solid #dee2e6;
                    }

                    th,
                    td {
                        padding: 12px;
                        text-align: center;
                        color: #343a40;
                    }

                    th {
                        background-color: #6c757d;
                        color: #ffffff;
                        font-weight: bold;
                    }

                    tr:nth-child(even) {
                        background-color: #f1f1f1;
                    }

                    .form-body {
                        font-size: 24px;
                        text-align: center;
                    }

                    input[type="text"],
                    input[type="email"],
                    input[type="tel"],
                    textarea,
                    input[type="file"] {
                        width: 100%;
                        padding: 12px;
                        border: 2px solid #ced4da;
                        border-radius: 5px;
                        margin: 8px 0;
                        font-size: 16px;
                        box-sizing: border-box;
                        background-color: #f1f1f1;
                        transition: all 0.3s ease-in-out;
                    }

                    input[type="text"]:focus,
                    input[type="email"]:focus,
                    input[type="tel"]:focus,
                    textarea:focus,
                    input[type="file"]:focus {
                        border-color: #007bff;
                        outline: none;
                        background-color: #fff;
                    }

                    button {
                        background-color: #007bff;
                        color: white;
                        padding: 10px 20px;
                        border-radius: 5px;
                        border: none;
                        cursor: pointer;
                        font-size: 16px;
                        width: 20%;
                        margin-top: 20px; /* Adjusted for spacing */
                        transition: background-color 0.3s ease;
                    }

                    button:hover {
                        background-color: #0056b3;
                    }

                    label {
                        font-size: 18px;
                        color: #343a40;
                        font-weight: bold;
                        display: block;
                        margin-bottom: 8px;
                    }

                    textarea {
                        resize: vertical;
                    }

                    input[type="file"] {
                        padding: 8px;
                        cursor: pointer;
                    }

                    input[type="file"]:hover {
                        background-color: #f1f1f1;
                    }

                    input[type="file"]::-webkit-file-upload-button {
                        background-color: #007bff;
                        color: white;
                        border: none;
                        padding: 8px 16px;
                        border-radius: 4px;
                    }

                    input[type="file"]::-webkit-file-upload-button:hover {
                        background-color: #0056b3;
                    }

                    /* Center the submit button */
                    .submit-btn-container {
                        text-align: center;
                        margin-top: 20px; /* Adds spacing above the button */
                    }
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
                            <td><label for="email">Email:</label></td>
                            <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($userAccount->email); ?>" required /></td>
                        </tr>
                        <tr>
                            <td><label for="phone_num">Phone Number:</label></td>
                            <td><input type="tel" id="phone_num" name="phone_num" value="<?= htmlspecialchars($userAccount->phone_num); ?>" required /></td>
                        </tr>
                        <tr>
                            <td><label for="about">About:</label></td>
                            <td><textarea id="about" name="about" rows="4" required><?= htmlspecialchars($userAccount->about); ?></textarea></td>
                        </tr>
                        <tr>
                            <td><label for="profile_image">Profile Image:</label></td>
                            <td><input type="file" id="profile_image" name="profile_image" /></td>
                        </tr>
                    </table>
                    <!-- Submit button outside the table -->
                    <div class="submit-btn-container">
                        <button type="submit">Update Account</button>
                    </div>
                </form>
            </body>
        </html>

        <?php
    }
}

// Controller & Page handling
$page = new UpdateSellerAccountInformationPage();
$page->handleFormSubmission();
$page->updateSellerAccountInformationUI();
?>
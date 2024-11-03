<?php
require "connectDatabase.php";

// ENTITY LAYER: Represents the UserProfile data structure
class UserProfile {
    public $user_id;
    public $first_name;
    public $last_name;
    public $gender;
    public $about;
    public $profile_image;

    public function __construct($user_id, $first_name, $last_name, $gender, $about, $profile_image = null) {
        $this->user_id = $user_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->gender = $gender;
        $this->about = $about;
        $this->profile_image = $profile_image;
    }
}

// CONTROL LAYER: Handles business logic and database interactions for user profiles
class ProfileController {
    private $conn;

    // Constructor to initialize the connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Handle form submission for profile creation
    public function handleProfileCreation($formData, $fileData) {
        // Check if keys exist before accessing them
        $username = isset($formData['username']) ? $formData['username'] : null;
        $first_name = isset($formData['first_name']) ? $formData['first_name'] : null;
        $last_name = isset($formData['last_name']) ? $formData['last_name'] : null;
        $gender = isset($formData['gender']) ? $formData['gender'] : null;
        $about = isset($formData['about']) ? $formData['about'] : null;

        // Ensure all required fields are present
        if (is_null($username) || is_null($first_name) || is_null($last_name) || is_null($gender) || is_null($about)) {
            return; // Do nothing if fields are missing
        }

        // Check if the username exists in the database
        $user_id = $this->checkUsernameExists($username);
        if (!$user_id) {
            return; // Do nothing if the username does not match
        }

        // Handle the profile image if provided
        $profile_image = null;
        if (isset($fileData['profile_image']) && $fileData['profile_image']['error'] == 0) {
            $profile_image = file_get_contents($fileData['profile_image']['tmp_name']);
        }

        // Create a new UserProfile entity
        $userProfile = new UserProfile($user_id, $first_name, $last_name, $gender, $about, $profile_image);

        // Insert the profile into the database
        $this->createProfile($userProfile);
    }

    // CONTROL LAYER: Checks if a username exists in the database
    private function checkUsernameExists($username) {
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ? $user['user_id'] : null;
    }

    // CONTROL LAYER: Inserts the UserProfile entity data into the database
    private function createProfile($userProfile) {
        $stmt = $this->conn->prepare("INSERT INTO profile (user_id, first_name, last_name, gender, about, profile_image, status_id) VALUES (?, ?, ?, ?, ?, ?, 1)");

        if ($userProfile->profile_image) {
            // Profile image is provided
            $stmt->bind_param("issssb", 
                $userProfile->user_id, 
                $userProfile->first_name, 
                $userProfile->last_name, 
                $userProfile->gender, 
                $userProfile->about, 
                $userProfile->profile_image // bind the actual profile image
            );
        } else {
            // Profile image is not provided
            $null = NULL; // Use NULL for the profile image
            $stmt->bind_param("issssi", 
                $userProfile->user_id, 
                $userProfile->first_name, 
                $userProfile->last_name, 
                $userProfile->gender, 
                $userProfile->about, 
                $null // bind NULL for profile_image
            );
        }

        $stmt->execute();
        $stmt->close();
    }
}

// BOUNDARY LAYER: Handles user interface tasks for profile creation
class ProfileCreationView {
    private $message;

    public function __construct($message = "") {
        $this->message = $message;
    }

    // Render the profile creation form
    public function render() {
        ?>
        <html>
        <head>
            <title>Profile Creation Page</title>
            <style>
                .form-body { text-align: center; }
                .invisible-table {
                    border-collapse: collapse;
                    width: 50%;
                    margin: auto;
                }
                .invisible-table td { border: none; padding: 10px; }
            </style>
        </head>
        <body>
            <div style="background-color: red" class="header">
                <h1 style="text-align:center">Profile Creation</h1>
                <h2 style="text-align:center">Please fill in the following details</h2>
                <h3 style="text-align:center">All fields are mandatory</h3>
            </div>

            <!-- Display success or error messages -->
            <!-- Removed message display to eliminate errors -->
            <?php /* if ($this->message): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; */ ?>

            <!-- Form for profile creation -->
            <form class="form-body" method="POST" action="" enctype="multipart/form-data">
                <h4>Note: Username should match with one of the data in the accounts list!</h4>
                <table class="invisible-table">
                    <tr>
                        <td><label style="font-size: 24px">Profile Picture:</label></td>
                        <td><input type="file" name="profile_image" style="font-size: 24px"/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Username:</label></td>
                        <td><input type="text" name="username" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">First Name:</label></td>
                        <td><input type="text" name="first_name" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Last Name:</label></td>
                        <td><input type="text" name="last_name" style="font-size: 24px" required/></td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">Gender:</label></td>
                        <td>
                            <select name="gender" style="font-size: 24px" required>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label style="font-size: 24px">About:</label></td>
                        <td><textarea name="about" style="font-size: 24px" rows="4" cols="50"></textarea></td>
                    </tr>
                </table>
                <br/>
                <button type="submit" style="font-size: 24px">Create Profile</button>
            </form>
            <br/>
            <form action="admin_manage_user_profiles.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Return to profiles list</button>
            </form>
        </body>
        </html>
        <?php
    }
}

// MAIN APPLICATION LOGIC: Connects BCE components and processes form submissions
$database = new Database();
$conn = $database->getConnection();

// Initialize the control layer with the database connection
$controller = new ProfileController($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller->handleProfileCreation($_POST, $_FILES);
}

// Initialize and render the boundary layer
$view = new ProfileCreationView();
$view->render();

// Close the database connection
$database->closeConnection();
?>

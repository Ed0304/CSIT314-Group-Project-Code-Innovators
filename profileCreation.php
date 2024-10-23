<?php
// ENTITY LAYER: Handles data-related tasks (database interactions)
class UserProfile {
    private $conn;

    // Constructor to initialize the database connection
    public function __construct($servername, $username, $password, $dbname) {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // Check for a connection error
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Check if the username exists in the users table
    public function checkUsernameExists($username) {
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Insert a new profile into the profile table, with or without image
    public function createProfile($user_id, $first_name, $last_name, $gender, $about, $profile_image = null) {
        if ($profile_image) {
            $stmt = $this->conn->prepare("INSERT INTO profile (user_id, first_name, last_name, gender, about, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
            $null = NULL;
            $stmt->bind_param("issssb", $user_id, $first_name, $last_name, $gender, $about, $null);
            $stmt->send_long_data(5, $profile_image);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO profile (user_id, first_name, last_name, gender, about, profile_image) VALUES (?, ?, ?, ?, ?, NULL)");
            $stmt->bind_param("issss", $user_id, $first_name, $last_name, $gender, $about);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Close the database connection
    public function close() {
        $this->conn->close();
    }
}

// CONTROL LAYER: Handles the logic and mediates between boundary and entity layers
class ProfileController {
    private $userProfileModel;

    // Constructor to initialize the UserProfile entity model
    public function __construct($userProfileModel) {
        $this->userProfileModel = $userProfileModel;
    }

    // Handle form submission for profile creation
    public function handleProfileCreation($formData, $fileData) {
        $username = $formData['username'];
        $first_name = $formData['first_name'];
        $last_name = $formData['last_name'];
        $gender = $formData['gender'];
        $about = $formData['about'];

        // Check if the username exists
        $user = $this->userProfileModel->checkUsernameExists($username);
        if (!$user) {
            return "Error: Username must match an existing account.";
        }

        $user_id = $user['user_id'];

        // Check if the file data contains a profile image
        if (isset($fileData['profile_image']) && $fileData['profile_image']['error'] == 0) {
            $image = $fileData['profile_image']['tmp_name'];
            $image_data = file_get_contents($image); // Read binary image data
            $result = $this->userProfileModel->createProfile($user_id, $first_name, $last_name, $gender, $about, $image_data);
        } else {
            // Insert without profile image
            $result = $this->userProfileModel->createProfile($user_id, $first_name, $last_name, $gender, $about);
        }

        return $result ? "New profile created successfully." : "Error: Failed to create profile.";
    }
}

// BOUNDARY LAYER: Manages the user interface (display form and messages)
class ProfileCreationView {
    private $message;

    // Constructor to initialize any message to display
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
            <?php if ($this->message): ?>
                <p style="text-align:center; font-size: 20px; color: red;"><?php echo htmlspecialchars($this->message); ?></p>
            <?php endif; ?>

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

// MAIN LOGIC: Connects the BCE components

// Database configuration
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "csit314";

// Entity layer: Initialize UserProfile model with the database connection
$userProfileModel = new UserProfile($servername, $dbUsername, $dbPassword, $dbname);

// Control layer: Initialize ProfileController with the entity model
$controller = new ProfileController($userProfileModel);

// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $controller->handleProfileCreation($_POST, $_FILES);
}

// Boundary layer: Initialize ProfileCreationView with any message and render the form
$view = new ProfileCreationView($message);
$view->render();

// Close the database connection
$userProfileModel->close();
?>

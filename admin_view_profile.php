<?php
// ENTITY LAYER: Handles data-related tasks (database interactions)
class UserProfile {
    private $conn;

    // Constructor to initialize the database connection
    public function __construct($servername, $username, $password, $dbname) {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Fetch user profile based on username
    public function getUserProfile($username) {
        $stmt = $this->conn->prepare("SELECT p.first_name, p.last_name, p.gender, p.about, p.profile_image, u.email, u.phone_num, r.role_name 
            FROM profile p
            JOIN users u ON u.user_id = p.user_id
            JOIN role r ON u.role_id = r.role_id
            WHERE u.username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
        $stmt->close();

        return $profile;
    }

    // Close the connection
    public function closeConnection() {
        $this->conn->close();
    }
}

// CONTROL LAYER: Handles logic and mediates between boundary and entity layers
class UserProfileController {
    private $userProfileModel;

    // Constructor to initialize the UserProfile model
    public function __construct($userProfileModel) {
        $this->userProfileModel = $userProfileModel;
    }

    // Handle fetching and returning user profile data
    public function fetchUserProfile($username) {
        return $this->userProfileModel->getUserProfile($username);
    }
}

// BOUNDARY LAYER: Manages the user interface
class UserProfileView {
    private $userProfile;

    // Constructor to initialize the profile data
    public function __construct($userProfile = null) {
        $this->userProfile = $userProfile;
    }

    // Render the profile information page
    public function renderProfilePage() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Information</title>
            <style>
                #infoTable th, td {
                    font-size: 24px;
                    text-align: center;
                }
                #infoTable {
                    margin: auto;
                }
                .button {
                    font-size: 24px;
                    padding: 10px 20px;
                    margin: 5px;
                }
                img.profile-image {
                    width: 150px;
                    height: 150px;
                    object-fit: cover;
                }
            </style>
        </head>
        <body>
            <h1 style="text-align: center">Profile Information</h1>
            <table id="infoTable">
                <?php if ($this->userProfile): ?>
                    <tr>
                        <td><strong>Profile Picture</strong></td>
                        <td colspan="2">
                            <?php if (!empty($this->userProfile['profile_image'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($this->userProfile['profile_image']); ?>" class="profile-image" alt="Profile Picture">
                            <?php else: ?>
                                <img src="default-profile.jpg" class="profile-image" alt="Default Profile Picture">
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Full Name</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->userProfile['first_name'] . ' ' . $this->userProfile['last_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->userProfile['role_name']); ?></td>
                    </tr>   
                    <tr>
                        <td><strong>Email</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->userProfile['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Phone Number</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->userProfile['phone_num']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gender</strong></td>
                        <td colspan="2">
                        <?php
                            if ($this->userProfile['gender'] == 'M') {
                                echo 'Male';
                            } elseif ($this->userProfile['gender'] == 'F') {
                                 echo 'Female';
                            } else {
                                echo htmlspecialchars($this->userProfile['gender']); // Default value if not 'M' or 'F'
                            }
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>About</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->userProfile['about']); ?></td>
                    </tr>
                    <tr>
                    <td>
                        <form action="admin_manage_user_profiles.php" class="form-body">
                            <button type="submit" value="Return" style="font-size: 24px">Return to profiles list</button>
                        </form>
                    </td>
                    <td>
                        <form action="admin_update_user_profile.php" class="form-body">
                            <button type="submit" value="Return" style="font-size: 24px">Update account profile</button>
                        </form>
                    </td>
                    <td>
                        <form action="admin_suspend_user_profile.php" class="form-body">
                            <button type="submit" value="Return" style="font-size: 24px">Suspend this profile</button>
                        </form>
                    </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Profile not found.</td>
                    </tr>
                <?php endif; ?>
            </table>
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

// Control layer: Initialize UserProfileController with the entity model
$controller = new UserProfileController($userProfileModel);

// Check if the username is provided
if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $userProfile = $controller->fetchUserProfile($username);
} else {
    $userProfile = null;
}

// Boundary layer: Initialize UserProfileView with the fetched profile data
$view = new UserProfileView($userProfile);
$view->renderProfilePage();

// Close the database connection
$userProfileModel->closeConnection();
?>

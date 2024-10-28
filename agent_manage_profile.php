<?php
session_start();

// ENTITY LAYER: Handles data-related tasks (database interactions)
class UserProfile {
    private $pdo;

    // Constructor for establishing a database connection
    public function __construct($host, $db, $user, $pass) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    // Method to retrieve user profiles, filtered by username if provided
    public function getProfileByUsername($username) {
        $query = "SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_name, u.phone_num, p.profile_image 
                  FROM profile p 
                  JOIN users u ON p.user_id = u.user_id 
                  JOIN role r ON r.role_id = u.role_id 
                  WHERE u.username = :username"; // Filter by exact match on username
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// CONTROL LAYER: Handles business logic and mediates between the boundary and entity layers
class ProfileController {
    private $userProfileModel;

    public function __construct($userProfileModel) {
        $this->userProfileModel = $userProfileModel;
    }

    // Fetch the profile data by username
    public function getProfile($username) {
        return $this->userProfileModel->getProfileByUsername($username);
    }
}

// BOUNDARY LAYER: Responsible for interacting with the user (display)
class ProfileView {
    private $profileData;

    public function __construct($profileData) {
        $this->profileData = $profileData;
    }

    // Render the profile page
    public function render() {
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
                <?php if ($this->profileData): ?>
                    <tr>
                        <td><strong>Profile Picture</strong></td>
                        <td colspan="2">
                            <?php if (!empty($this->profileData['profile_image'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($this->profileData['profile_image']); ?>" class="profile-image" alt="Profile Picture">
                            <?php else: ?>
                                <img src="default-profile.jpg" class="profile-image" alt="Default Profile Picture">
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Full Name</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData['first_name'] . ' ' . $this->profileData['last_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData['role_name']); ?></td>
                    </tr>   
                    <tr>
                        <td><strong>Email</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Phone Number</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData['phone_num']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gender</strong></td>
                        <td colspan="2">
                        <?php
                            if ($this->profileData['gender'] == 'M') {
                                echo 'Male';
                            } elseif ($this->profileData['gender'] == 'F') {
                                 echo 'Female';
                            } else {
                                echo htmlspecialchars($this->profileData['gender']);
                            }
                        ?>
                    </td>
                    </tr>
                    <tr>
                        <td><strong>About</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData['about']); ?></td>
                    </tr>
                    <tr>
                        <td>
                            <form action="agent_dashboard.php" class="form-body">
                                <button type="submit" value="Return" style="font-size: 24px">Return to main dashboard</button>
                            </form>
                        </td>
                        <td>
                            <form action="agent_update_profile.php" class="form-body">
                                <button type="submit" value="Return" style="font-size: 24px">Update account profile</button>
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

// MAIN LOGIC: Orchestrates the BCE components

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Create entity layer (UserProfile) with database connection
$host = 'localhost'; // Update with actual values
$db = 'csit314';
$dbUser = 'root';
$dbPass = '';
$userProfileModel = new UserProfile($host, $db, $dbUser, $dbPass);

// Create control layer (ProfileController) and retrieve user profile
$controller = new ProfileController($userProfileModel);
$profileData = $controller->getProfile($username);

// Create boundary layer (ProfileView) and render profile information
$view = new ProfileView($profileData);
$view->render();
?>

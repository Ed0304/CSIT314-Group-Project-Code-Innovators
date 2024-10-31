<?php
session_start();

class UserProfile {
    public function getProfileByUsername($pdo, $username) {
        $stmt = $pdo->prepare("SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_name, u.phone_num, p.profile_image, s.status_name, p.profile_id
                    FROM profile p
                    JOIN users u ON p.user_id = u.user_id
                    JOIN role r ON r.role_id = u.role_id
                    JOIN status s ON s.status_id = p.status_id
                    WHERE u.username = :username");
        $stmt->bindParam(':username', $username); // Using named parameter correctly
        $stmt->execute();

        // Fetch the profile data
        return $stmt->fetch(PDO::FETCH_ASSOC); // Return the fetched data
    }
}

// CONTROL LAYER: Passes the connection to the entity layer when necessary
class ProfileController {
    private $userProfileModel;
    private $pdo;

    public function __construct($pdo) {
        $this->userProfileModel = new UserProfile();
        $this->pdo = $pdo;
    }

    public function getProfile($username) {
        return $this->userProfileModel->getProfileByUsername($this->pdo, $username);
    }
}

// BOUNDARY LAYER: Responsible for rendering user information
class ProfileView {
    private $profileData;

    public function __construct($profileData) {
        $this->profileData = $profileData;
    }

    public function render() {
        ?>
        <html>
            <head>
                <title>Profile Information</title>
            </head>
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
                            <td><strong>Status</strong></td>
                            <td colspan="2"><?php echo htmlspecialchars($this->profileData['status_name']); ?></td>
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
                                <form action="admin_manage_user_profiles.php" class="form-body">
                                    <button type="submit" class="button">Return profiles list</button>
                                </form>
                            </td>
                            <td>
                                <form action="admin_profile_update.php" class="form-body">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($this->profileData['user_id']); ?>">
                                    <button type="submit" class="button">Update Profile</button>
                                </form>
                            </td>
                            <td>
                                <form action="admin_suspend_profile.php" class="form-body">
                                    <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($this->profileData['profile_id']); ?>">
                                    <button type="submit" class="button">Suspend Profile</button>
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

// MAIN LOGIC: Coordinates the application
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

try {
    // Establish database connection
    $pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Use GET parameter to fetch the username
$username = isset($_GET['username']) ? $_GET['username'] : '';

if ($username) {
    // Controller instance creation
    $profileController = new ProfileController($pdo);
    $profileData = $profileController->getProfile($username);

    // Render the view with retrieved profile data
    $profileView = new ProfileView($profileData);
    $profileView->render();
} else {
    echo "No username provided.";
}
?>
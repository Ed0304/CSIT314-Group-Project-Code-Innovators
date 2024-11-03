<?php
session_start();

class UserProfile {
    // Fetch profile information using profile_id
    public function getProfileByProfileId($pdo, $profile_id) {
        $stmt = $pdo->prepare("SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_id, r.role_name, u.phone_num, p.profile_image, s.status_name, p.profile_id
                    FROM profile p
                    JOIN users u ON p.user_id = u.user_id
                    JOIN role r ON r.role_id = u.role_id
                    JOIN status s ON s.status_id = p.status_id
                    WHERE p.profile_id = :profile_id");
        $stmt->bindParam(':profile_id', $profile_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    // Fetch profile by profile_id
    public function getProfileById($profile_id) {
        return $this->userProfileModel->getProfileByProfileId($this->pdo, $profile_id);
    }

    // Fetch profiles by role_id
    public function getProfilesByRole($role_id) {
        $stmt = $this->pdo->prepare("SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_name, u.phone_num, p.profile_image, s.status_name, p.profile_id
                    FROM profile p
                    JOIN users u ON p.user_id = u.user_id
                    JOIN role r ON r.role_id = u.role_id
                    JOIN status s ON s.status_id = p.status_id
                    WHERE r.role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                                <form action="admin_view_profile.php" class="form-body">
                                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->profileData['role_id']); ?>">
                                    <button type="submit" class="button">Return profiles list</button>
                                </form>
                            </td>
                            <td>
                                <form action="admin_update_profile.php" class="form-body">
                                    <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($this->profileData['profile_id']); ?>">
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

    public function renderProfilesList($profiles) {
        ?>
        <html>
            <head>
                <title>Profiles List</title>
            </head>
            <body>
                <h1>Profiles List</h1>
                <table border="1">
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($profiles as $profile): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($profile['username']); ?></td>
                            <td><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($profile['email']); ?></td>
                            <td>
                                <form action="admin_view_profile.php" method="GET">
                                    <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($profile['profile_id']); ?>">
                                    <button type="submit">View Profile</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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

// Use GET parameter to fetch the profile_id or role_id
$profile_id = isset($_GET['profile_id']) ? $_GET['profile_id'] : '';
$role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';

$profileController = new ProfileController($pdo);

if ($profile_id) {
    // Fetch a specific user's profile using profile_id
    $profileData = $profileController->getProfileById($profile_id);

    // Render the view with retrieved profile data
    $profileView = new ProfileView($profileData);
    $profileView->render();
} elseif ($role_id) {
    // Fetch profiles by role_id
    $profiles = $profileController->getProfilesByRole($role_id);
    
    // Render the list of profiles
    $profileView = new ProfileView([]);
    $profileView->renderProfilesList($profiles);
} else {
    echo "No profile_id or role_id provided.";
}
?>

<?php
session_start();

// Entity Layer: UserProfile class for interacting with the database
class UserProfile {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Fetch profile information using profile_id
    public function getProfileByProfileId($profile_id) {
        $stmt = $this->pdo->prepare("SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_id, r.role_name, u.phone_num, p.profile_image, s.status_name, p.profile_id
                    FROM profile p
                    JOIN users u ON p.user_id = u.user_id
                    JOIN role r ON r.role_id = u.role_id
                    JOIN status s ON s.status_id = p.status_id
                    WHERE p.profile_id = :profile_id");
        $stmt->bindParam(':profile_id', $profile_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch profiles by role_id
    public function getProfilesByRoleId($role_id) {
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

// Control Layer: ViewProfileController class for managing data flow between boundary and entity layers
class ViewProfileController {
    private $userProfileModel;

    public function __construct($userProfileModel) {
        $this->userProfileModel = $userProfileModel;
    }

    // Fetch profile by profile_id
    public function getProfileById($profile_id) {
        return $this->userProfileModel->getProfileByProfileId($profile_id);
    }

    // Fetch profiles by role_id
    public function getProfilesByRole($role_id) {
        return $this->userProfileModel->getProfilesByRoleId($role_id);
    }
}

// Boundary Layer: ViewProfileBoundary class for handling form display and user interaction
class ViewProfileBoundary {
    private $profileController;

    public function __construct($profileController) {
        $this->profileController = $profileController;
    }

    public function display() {
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        $profile_id = $_GET['profile_id'] ?? '';
        $role_id = $_GET['role_id'] ?? '';

        if ($profile_id) {
            $profileData = $this->profileController->getProfileById($profile_id);
            $this->renderProfile($profileData);
        } elseif ($role_id) {
            $profiles = $this->profileController->getProfilesByRole($role_id);
            $this->renderProfilesList($profiles);
        } else {
            echo "No profile_id or role_id provided.";
        }
    }

    private function renderProfile($profileData) {
        if ($profileData) {
            ?>
            <html>
                <head>
                    <title>Profile Information</title>
                </head>
                <body>
                    <h2>Profile Information</h2>
                    <p><strong>Username:</strong> <?= htmlspecialchars($profileData['username']); ?></p>
                    <p><strong>First Name:</strong> <?= htmlspecialchars($profileData['first_name']); ?></p>
                    <p><strong>Last Name:</strong> <?= htmlspecialchars($profileData['last_name']); ?></p>
                    <p><strong>About:</strong> <?= htmlspecialchars($profileData['about']); ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($profileData['gender']); ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($profileData['email']); ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($profileData['phone_num']); ?></p>
                    <p><strong>Role:</strong> <?= htmlspecialchars($profileData['role_name']); ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($profileData['status_name']); ?></p>
                    <p><img src="data:image/jpeg;base64,<?= base64_encode($profileData['profile_image']); ?>" alt="Profile Image" /></p>
                </body>
            </html>
            <?php
        } else {
            echo "<p>No profile data available.</p>";
        }
    }

    private function renderProfilesList($profiles) {
        ?>
        <html>
            <head>
                <title>Profiles List</title>
            </head>
            <body>
                <h2>Profiles List</h2>
                <?php if ($profiles): ?>
                    <ul>
                        <?php foreach ($profiles as $profile): ?>
                            <li>
                                <p><strong>Username:</strong> <?= htmlspecialchars($profile['username']); ?></p>
                                <p><strong>First Name:</strong> <?= htmlspecialchars($profile['first_name']); ?></p>
                                <p><strong>Last Name:</strong> <?= htmlspecialchars($profile['last_name']); ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']); ?></p>
                                <p><strong>Role:</strong> <?= htmlspecialchars($profile['role_name']); ?></p>
                                <p><img src="data:image/jpeg;base64,<?= base64_encode($profile['profile_image']); ?>" alt="Profile Image" /></p>
                                <hr>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No profiles found for this role.</p>
                <?php endif; ?>
            </body>
        </html>
        <?php
    }
}

// Global Layer: Initializing the components
$profileEntity = new UserProfile();
$profileController = new ViewProfileController($profileEntity);
$profileView = new ViewProfileBoundary($profileController);
$profileView->display();
?>

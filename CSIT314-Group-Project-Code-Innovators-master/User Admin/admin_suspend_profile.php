<?php
session_start();
require '../connectDatabase.php';

// BOUNDARY LAYER: Responsible for rendering user information
class ProfileView {
    private $profileData;

    public function __construct($profileData) {
        $this->profileData = $profileData;
    }

    public function render() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <style>
            #infoTable th, td {
                font-size: 24px;
                text-align: center;
            }
            #infoTable {
                margin: auto;
            }
            .profile-image {
                width: 100px;
                height: 100px;
                border-radius: 50%;
            }
        </style>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Suspend Confirmation</title>
        </head>
        <body>
            <h1 style="text-align: center">Suspend this profile?</h1>
            <table id="infoTable">
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
                    <form action="" method="POST" class="form-body">
                        <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($this->profileData['profile_id']); ?>">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData['username']); ?>">
                        <input type="hidden" name="action" value="suspend">
                        <button type="submit" style="font-size: 24px">Suspend</button>
                    </form>
                </td>
                <td>
                    <form action="" method="POST" class="form-body">
                        <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($this->profileData['profile_id']); ?>">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData['username']); ?>">
                        <input type="hidden" name="action" value="remove_suspend">
                        <button type="submit" style="font-size: 24px">Remove Suspension</button>
                    </form>
                </td>
                <td>
                <a href="profileDetails.php?profile_id=<?= htmlspecialchars($this->profileData['profile_id']); ?>">
                    <button type="button" class="secondary-button" style="font-size: 24px">Return to Profile Info</button>
                </a>
                </td>
            </tr>
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

// Ensure user_id is in session
$user_id = $_SESSION['user_id'] ?? null; // Assuming user_id is stored in the session

// Use GET parameter to fetch the profile_id
$profile_id = isset($_GET['profile_id']) ? $_GET['profile_id'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $profile_id = $_POST['profile_id'];
    $username = $_POST['username'];

    // Create an instance of AccountController and handle actions
    $accountController = new AccountController();
    
    if ($_POST['action'] === 'suspend') {
        $accountController->setSuspend($profile_id, $username);

        $_SESSION['success_message'] = "Profile and account suspended successfully.";
        // Redirect to profile details page
        header("Location: profileDetails.php?profile_id=" . urlencode($profile_id));
        exit();
    } elseif ($_POST['action'] === 'remove_suspend') {
        $accountController->removeSuspend($profile_id, $username);

        $_SESSION['remove_suspend_message'] = "Profile and account suspension removed.";
        // Redirect to profile details page
        header("Location: profileDetails.php?profile_id=" . urlencode($profile_id));
        exit();
    }
}

if ($profile_id) {
    // Create an instance of AccountController and fetch profile data
    $accountController = new AccountController();
    $profileData = $accountController->getProfile($profile_id, $user_id);

    // Render the view with retrieved profile data
    $profileView = new ProfileView($profileData);
    $profileView->render();
} else {
    echo "No profile provided.";
}

// CONTROL LAYER: Serves as an intermediary between view and entity
class AccountController {
    private $userAccountModel;

    public function __construct() {
        $this->userAccountModel = new UserAccount();
    }

    public function getProfile($profile_id, $user_id) {
        return $this->userAccountModel->getProfileByUserId($profile_id, $user_id);
    }

    public function setSuspend($profile_id, $username) {
        $this->userAccountModel->suspend($profile_id);
        $this->userAccountModel->suspendAccount($username);
    }

    public function removeSuspend($profile_id, $username) {
        $this->userAccountModel->removeSuspend($profile_id);
        $this->userAccountModel->removeSuspendAccount($username);
    }
}

// ENTITY: Handles all logic for user data and database interactions
class UserAccount {
    private $pdo;

    public function __construct() {
        try {
            // Establish database connection within the entity
            $this->pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getProfileByUserId($profile_id, $user_id) {
        // You can also include user_id in your query if necessary
        $stmt = $this->pdo->prepare("SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.profile_id, p.user_id, r.role_name, u.phone_num, p.profile_image, s.status_name
                    FROM profile p
                    JOIN users u ON p.user_id = u.user_id
                    JOIN role r ON r.role_id = u.role_id
                    JOIN status s ON s.status_id = p.status_id
                    WHERE p.profile_id = :profile_id");

        $stmt->bindParam(':profile_id', $profile_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Suspend user function (status_id = 1 is active, status_id = 2 is suspended)
    public function suspend($profile_id) {
        $stmt = $this->pdo->prepare("UPDATE profile SET status_id = 2 WHERE profile_id = :profile_id");
        $stmt->bindParam(':profile_id', $profile_id);
        return $stmt->execute();
    }

    // Remove suspension function
    public function removeSuspend($profile_id) {
        $stmt = $this->pdo->prepare("UPDATE profile SET status_id = 1 WHERE profile_id = :profile_id");
        $stmt->bindParam(':profile_id', $profile_id);
        return $stmt->execute();
    }

    // Suspend account function
    public function suspendAccount($username) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 2 WHERE username = :username");
        $stmt->bindParam(':username', $username);
        return $stmt->execute();
    }

    // Remove suspension from account function
    public function removeSuspendAccount($username) {
        $stmt = $this->pdo->prepare("UPDATE users SET status_id = 1 WHERE username = :username");
        $stmt->bindParam(':username', $username);
        return $stmt->execute();
    }
}

?>

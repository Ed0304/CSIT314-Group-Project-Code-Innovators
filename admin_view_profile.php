<?php
<<<<<<< HEAD
require "connectDatabase.php";
session_start(); // Start the session at the beginning

// ENTITY LAYER: Handles data-related tasks, but does not handle connection
class UserProfile {
    public function __construct() {}

    public function getProfileByUsername($dbConnection, $username) {
        $query = "SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_name, u.phone_num, p.profile_image
                  FROM profile p
                  JOIN users u ON p.user_id = u.user_id
                  JOIN role r ON r.role_id = u.role_id
                  WHERE u.username = ?";
        $stmt = $dbConnection->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

// CONTROL LAYER: Passes the connection to the entity layer when necessary
class ProfileController {
    private $userProfileModel;
    private $dbConnection;

    public function __construct($userProfileModel, $dbConnection) {
        $this->userProfileModel = $userProfileModel;
        $this->dbConnection = $dbConnection;
    }

    public function getProfile($username) {
        return $this->userProfileModel->getProfileByUsername($this->dbConnection, $username);
=======
session_start();

class UserProfile {
    public function getProfileByUsername($pdo, $username) {
        $stmt = $pdo->prepare("SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_name, u.phone_num, p.profile_image
                    FROM profile p
                    JOIN users u ON p.user_id = u.user_id
                    JOIN role r ON r.role_id = u.role_id
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
>>>>>>> 9b98d70d2919599e70c0ca1d7d288d5026c22c6e
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
<<<<<<< HEAD
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Information</title>
            <style>
=======
        <html>
            <head>
                <title>Profile Information</title>
            </head>
        <style>
>>>>>>> 9b98d70d2919599e70c0ca1d7d288d5026c22c6e
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
<<<<<<< HEAD
            </style>
        </head>
=======
        </style>
>>>>>>> 9b98d70d2919599e70c0ca1d7d288d5026c22c6e
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
                            <form action="admin_manage_user_profiles.php" class="form-body">
                                <button type="submit" value="Return" style="font-size: 24px">Return profiles list</button>
                            </form>
                        </td>
                        <td>
                            <form action="agent_update_profile.php" class="form-body">
<<<<<<< HEAD
                                <button type="submit" value="Return" style="font-size: 24px">Update account profile</button>
=======
                                <button type="submit" value="Return" style="font-size: 24px">Update Profile profile</button>
>>>>>>> 9b98d70d2919599e70c0ca1d7d288d5026c22c6e
                            </form>
                        </td>
                        <td>
                            <form action="agent_suspend_profile.php" class="form-body">
<<<<<<< HEAD
                                <button type="submit" value="Return" style="font-size: 24px">Suspend account profile</button>
=======
                                <button type="submit" value="Return" style="font-size: 24px">Suspend Profile profile</button>
>>>>>>> 9b98d70d2919599e70c0ca1d7d288d5026c22c6e
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

<<<<<<< HEAD
$username = $_SESSION['username'];

$database = new Database('localhost', 'csit314', 'root', ''); // Update with actual credentials
$mysqli = $database->getConnection();

$userProfileModel = new UserProfile();
$controller = new ProfileController($userProfileModel, $mysqli);
$profileData = $controller->getProfile($username);

$view = new ProfileView($profileData);
$view->render();

$database->closeConnection();
?>
=======
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



>>>>>>> 9b98d70d2919599e70c0ca1d7d288d5026c22c6e

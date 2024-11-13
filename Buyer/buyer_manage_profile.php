<?php
require "../connectDatabase.php";
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Redirect to the update profile page if the update action is requested
if (isset($_POST['profile_id'])) {
    $profile_id = $_POST['profile_id'];
    header("Location: buyer_update_profile.php?profile_id=" . urlencode($profile_id) . "&username=" . urlencode($_POST['username'])); 
    exit();
}

$username = $_SESSION['username']; // Use the username from session

// ENTITY LAYER: Represents and fetches user profile data from the database
class UserAccount {
    public $username;
    public $first_name;
    public $last_name;
    public $about;
    public $gender;
    public $email;
    public $user_id;
    public $role_name;
    public $phone_num;
    public $profile_image;
    public $profile_id;

    public function __construct($data) {
        $this->username = $data['username'];
        $this->first_name = $data['first_name'];
        $this->last_name = $data['last_name'];
        $this->about = $data['about'];
        $this->gender = $data['gender'];
        $this->email = $data['email'];
        $this->user_id = $data['user_id'];
        $this->role_name = $data['role_name'];
        $this->phone_num = $data['phone_num'];
        $this->profile_image = $data['profile_image'];
        $this->profile_id = $data['profile_id'];
    }

    // Fetches profile data directly from the database
    public static function getProfileByUsername($username) {
        
            $pdo = new PDO('mysql:host=localhost;dbname=csit314', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "SELECT u.username, p.first_name, p.last_name, p.about, p.gender, u.email, p.user_id, r.role_name, u.phone_num, p.profile_image, p.profile_id 
                      FROM profile p 
                      JOIN users u ON p.user_id = u.user_id 
                      JOIN role r ON r.role_id = u.role_id 
                      WHERE u.username = :username";
            
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? new self($data) : null;
        
    }
}

// CONTROL LAYER: Handles business logic and manages the entity layer
class ViewAgentAccountController {
    // Fetches the profile as a UserAccount object
    public function getProfile($username) {
        return UserAccount::getProfileByUsername($username);
    }
}

// BOUNDARY LAYER: Responsible for rendering the user interface
class ViewAgentAccountPage {
    private $profileData;

    public function __construct($profileData) {
        $this->profileData = $profileData;
    }

    // Renders the profile page
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
            <h1 style="text-align: center">Account Information</h1>
            <table id="infoTable">
                <?php if ($this->profileData): ?>
                    <tr>
                        <td><strong>Account Picture</strong></td>
                        <td colspan="2">
                            <?php if (!empty($this->profileData->profile_image)): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($this->profileData->profile_image); ?>" class="profile-image" alt="Profile Picture">
                            <?php else: ?>
                                <img src="../default-profile.jpg" class="profile-image" alt="Default Profile Picture">
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Full Name</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData->first_name . ' ' . $this->profileData->last_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData->role_name); ?></td>
                    </tr>   
                    <tr>
                        <td><strong>Email</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData->email); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Phone Number</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData->phone_num); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gender</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData->gender == 'M' ? 'Male' : ($this->profileData->gender == 'F' ? 'Female' : $this->profileData->gender)); ?></td>
                    </tr>
                    <tr>
                        <td><strong>About</strong></td>
                        <td colspan="2"><?php echo htmlspecialchars($this->profileData->about); ?></td>
                    </tr>
                    <tr>
                        <td>
                            <form action="buyer_dashboard.php">
                                <button type="submit" class="button">Return to main dashboard</button>
                            </form>
                        </td>
                        <td>
                            <form action="" method="POST">
                                <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($this->profileData->profile_id); ?>">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData->username); ?>">
                                <button type="submit" name="update" class="button">Update account profile</button>
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

// MAIN LOGIC: Sets up components and renders the view
$accountController = new ViewAgentAccountController();
$profileData = $accountController->getProfile($username);

// Render the view with retrieved profile data
$userAccount = new ViewAgentAccountPage($profileData);
$userAccount->render();
?>

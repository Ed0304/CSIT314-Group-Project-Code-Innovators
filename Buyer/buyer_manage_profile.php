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
        
            $pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');
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
class viewBuyerAccountController {
    // Fetches the profile as a UserAccount object
    public function getProfile($username) {
        return UserAccount::getProfileByUsername($username);
    }
}

// BOUNDARY LAYER: Responsible for rendering the user interface
class viewBuyerAccountPage {
    private $profileData;

    public function __construct($profileData) {
        $this->profileData = $profileData;
    }

    // Renders the profile page
    public function viewBuyerAccountUI() {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Information</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    background-color: #f4f7fa;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }

                header {
                    background-color: #343a40;
                    color: white;
                    padding: 15px 0;
                    text-align: center;
                }

                h1 {
                    font-size: 2em;
                    margin: 0;
                }

                table {
                    width: 80%;
                    margin: 40px auto;
                    border-collapse: collapse;
                    background-color: white;
                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                    overflow: hidden;
                }

                th, td {
                    padding: 15px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }

                th {
                    background-color: #f1f1f1;
                    color: #333;
                }

                tr:hover {
                    background-color: #f9f9f9;
                }

                td img.profile-image {
                    border-radius: 50%;
                    object-fit: cover;
                    width: 120px;
                    height: 120px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .button {
                    background-color: #007bff;
                    color: white;
                    padding: 12px 24px;
                    border: none;
                    border-radius: 5px;
                    text-align: center;
                    font-size: 1.1em;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }

                .button:hover {
                    background-color: #0056b3;
                }

                .button:active {
                    background-color: #004085;
                }

                .button-container {
                    display: flex;
                    justify-content: space-around;
                    padding: 20px;
                }

                .button-container form {
                    margin: 0;
                }
            </style>
        </head>
        <body>
            <header>
                <h1>Account Information</h1>
            </header>

            <table>
                <?php if ($this->profileData): ?>
                    <tr>
                        <td><strong>Profile Picture</strong></td>
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
                        <td colspan="2"><?php echo nl2br(htmlspecialchars($this->profileData->about)); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div class="button-container">
                                <form action="buyer_dashboard.php">
                                    <button type="submit" class="button">Return to Dashboard</button>
                                </form>
                                <form action="" method="POST">
                                    <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($this->profileData->profile_id); ?>">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($this->profileData->username); ?>">
                                    <button type="submit" name="update" class="button">Update Profile</button>
                                </form>
                            </div>
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
$accountController = new viewBuyerAccountController();
$profileData = $accountController->getProfile($username);

// Render the view with retrieved profile data
$userAccount = new viewBuyerAccountPage($profileData);
$userAccount->viewBuyerAccountUI();
?>

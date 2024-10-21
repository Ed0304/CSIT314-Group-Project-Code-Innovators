<?php
// Entity: Database connection and user data retrieval
class UserProfile {
    private $pdo;

    public function __construct($host, $db, $user, $pass) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    // Fetch user information from the database
    public function getUserProfile($username) {
        $stmt = $this->pdo->prepare("SELECT p.first_name, p.last_name, r.role_name, u.email, u.phone_num, p.gender
            FROM profile p
            JOIN users u ON u.user_id = p.user_id
            JOIN role r ON u.role_id = r.role_id
            WHERE u.username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Control: Fetching User Profile
$username = isset($_GET['username']) ? $_GET['username'] : ''; // Get the username from the query string
$userProfileModel = new UserProfile('localhost', 'csit314', 'root', ''); // Create an instance of UserProfile
$userProfile = $userProfileModel->getUserProfile($username); // Fetch the user profile
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
    </style>
</head>
<body>
    <h1 style="text-align: center">Profile Information</h1>
    <table id="infoTable">
        <?php if ($userProfile): ?>
            <tr>
                <td><strong>Full Name</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['first_name']); ?> <?php echo htmlspecialchars($userProfile['last_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Role</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['role_name']); ?></td>
            </tr>   
            <tr>
                <td><strong>Email</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['email']); ?></td>
            </tr>
            <tr>
                <td><strong>Phone Number</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['phone_num']); ?></td>
            </tr>
            <tr>
                <td><strong>Gender</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['gender']); ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="3" style="color: red; text-align: center;">User profile not found.</td>
            </tr>
        <?php endif; ?>
        <tr>
            <td colspan="3"><br/></td>
        </tr>
        <tr>
            <td>
                <form action="admin_manage_user_profiles.php" method="post">
                    <button type="submit" class="button">Return to profile list</button>
                </form>
            </td>
            <td>
                <form action="admin_update_user_profiles.php" method="post">
                    <button type="submit" class="button">Update profile information</button>
                </form>
            </td>
            <td>
                <form action="admin_suspend_user_profiles.php" method="post">
                    <button type="submit" class="button">Suspend this profile</button>
                </form>
            </td>
        </tr>
    </table>
</body>
</html>

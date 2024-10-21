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
        $stmt = $this->pdo->prepare("SELECT u.username,u.password ,r.role_name, u.email, u.phone_num
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            WHERE u.username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Behavior: Fetch the user's profile information
$username = isset($_GET['username']) ? $_GET['username'] : ''; // Use GET instead of POST
$userProfileModel = new UserProfile('localhost', 'csit314', 'root', '');
$userProfile = $userProfileModel->getUserProfile($username);
?>

<!DOCTYPE HTML>
<html lang="en">
<style>
    #infoTable th,td{
        font-size: 24px;
        text-align: center;
    }
    #infoTable{
        margin: auto;
    }
</style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Information</title>
</head>
<body>
    <h1 style="text-align: center"> Account Information </h1>
    <table id = infoTable>
        <tr>
            <td><strong>Username</strong></td>
            <td colspan = 2><?php echo htmlspecialchars($userProfile['username']); ?></td>
        </tr>
        <tr>
            <td><strong>Password</strong></td>
            <td colspan = 2><?php echo htmlspecialchars($userProfile['password']); ?></td>
        <tr>
            <td><strong>Role</strong></td>
            <td colspan = 2><?php echo htmlspecialchars($userProfile['role_name']); ?></td>
        </tr>   
        <tr>
            <td><strong>Email</strong></td>
            <td colspan = 2><?php echo htmlspecialchars($userProfile['email']); ?></td>
        </tr>
        <tr>
            <td><strong>Phone Number</strong></td>
            <td colspan = 2><?php echo htmlspecialchars($userProfile['phone_num']); ?></td>
        </tr>
        <tr>
        <!--Empty table row, just to give spacing with acc info and buttons-->
            <td><br/></td>
            <td><br/></td>
        <tr>
        <tr>
            <td>
                <form action="admin_manage_user_acc.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Return to accounts list</button>
            </form>
            </td>
            
            <td>
                <form action="admin_update_user_acc.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Update account information</button>
            </form>
            </td>
            <td>
                <form action="admin_suspend_user_acc.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Suspend this account</button>
            </form>
            </td>
        </tr>
    </table>
</body>
</html>

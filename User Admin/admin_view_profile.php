<?php
require '../connectDatabase.php';
session_start();

// BOUNDARY LAYER: HTML View for managing user accounts
class ViewUserProfilePage
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function handleRequest()
    {
        $role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
        $users = $this->controller->getUsersByRole($role_id);
        $about = $this->controller->getAbout();
        $this->ViewUserProfileUI($users, $about, $role_id);
    }

    public function ViewUserProfileUI($users, $about, $role_id)
    {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>User Profile</title>
            <style>
                #main-table {
                    border-collapse: collapse;
                    width: 100%;
                }
                #main-table, #main-table th, #main-table td {
                    border: 1px solid black;
                }
                #main-table th, #main-table td {
                    padding: 10px;
                    font-size: 24px;
                    text-align: center;
                }
                .button-font {
                    font-size: 24px;
                }
            </style>
        </head>
        <body>
            <h1 style="text-align:center">Users in this role</h1>
            <br/>
            <table id="main-table">
                <tr>
                    <th>UserID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Role description</th>
                </tr>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['status_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['role_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['role_description'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <br/>
            <form method="post" action="admin_manage_user_profiles.php" style="text-align:center">
                <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role_id); ?>">
                <input type="submit" value="Return" style="font-size: 24px">
            </form>

        </body>
        </html>
        <?php
    }
}

// CONTROL LAYER: Manages data retrieval and updates based on Boundary's requests
class ViewUserProfileController
{
    private $userProfile;

    public function __construct($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    public function getUsersByRole($role_id)
    {
        return $this->userProfile->getUsersByRole($role_id);
    }

    public function getAbout()
    {
        return $this->userProfile->getAbout();
    }
}

// ENTITY LAYER: UserProfile handles all database interactions and data logic
class UserProfile {
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli('localhost', 'root', '', 'csit314');
        if ($this->mysqli->connect_error) {
            die("Database connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function getUsersByRole($role_id = '') {
        $query = "
            SELECT u.user_id, u.username, s.status_name, r.role_name, r.role_description
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON u.status_id = s.status_id";
        
        if (!empty($role_id)) {
            $query .= " WHERE u.role_id = ?";
        }

        $stmt = $this->mysqli->prepare($query);

        if (!empty($role_id)) {
            $stmt->bind_param('i', $role_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        
        return $users;
    }

    public function getAbout() {
        $query = "SELECT about FROM profile LIMIT 1"; // Assume one general 'about' section if applicable
        $result = $this->mysqli->query($query);
        return $result->fetch_assoc()['about'] ?? ''; // Return 'about' or empty string if not found
    }
}

// MAIN LOGIC: Initialize components and delegate request handling to the view
$userProfile = new UserProfile();
$userController = new ViewUserProfileController($userProfile); 
$userView = new ViewUserProfilePage($userController);
$userView->handleRequest();
?>

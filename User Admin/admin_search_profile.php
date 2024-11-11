<?php
require '../connectDatabase.php';
session_start();

// BOUNDARY LAYER: HTML View for managing user accounts
class SearchUserProfilePage
{
    private $controller;

    // Just for displaying purposes, no direct call to/from the database
    private $users = []; 
    private $about = ''; 
    private $role_id = ''; 

    private $searchTerm = '';

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function handleSearchUserProfileRequest()
    {
        $this->role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
        $this->searchTerm = isset($_POST['searchTerm']) ? $_POST['searchTerm'] : ''; // Get search term from POST
        $this->users = $this->controller->SearchUserProfiles($this->role_id, $this->searchTerm);
        $this->about = $this->controller->getAbout();
        $this->SearchUserProfileUI();  
    }

    public function SearchUserProfileUI()
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
            <!-- Added search bar and hidden field to pass role_id -->
            <form method="post" action="" style="text-align:center">
                <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->role_id); ?>">
                <input type="text" name="searchTerm" value="<?php echo htmlspecialchars($this->searchTerm); ?>" placeholder="Search by username" style="font-size: 24px">
                <input type="submit" name="searchUser" value="Search" style="font-size: 24px">
            </form>
            <br/>
            <table id="main-table">
                <tr>
                    <th>UserID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Role description</th>
                </tr>
                <?php if (!empty($this->users)): ?>
                    <?php foreach ($this->users as $user): ?>
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
                <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->role_id); ?>">
                <input type="submit" value="Return" style="font-size: 24px">
            </form>

        </body>
        </html>
        <?php
    }
}

// CONTROL LAYER: Manages data retrieval and updates based on Boundary's requests
class SearchUserProfileController
{
    private $userProfile;

    public function __construct($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    public function SearchUserProfiles($role_id, $searchTerm)
    {
        return $this->userProfile->SearchUserProfiles($role_id, $searchTerm);
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

    public function SearchUserProfiles($role_id = '', $searchTerm = '') {
        $query = "
            SELECT u.user_id, u.username, s.status_name, r.role_name, r.role_description
            FROM users u
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON u.status_id = s.status_id";
        
        $params = [];
        $types = '';
        
        if (!empty($role_id)) {
            $query .= " WHERE u.role_id = ?";
            $types .= 'i';
            $params[] = $role_id;
        }

        if (!empty($searchTerm)) {
            $query .= !empty($role_id) ? " AND" : " WHERE";
            $query .= " u.username LIKE ?";
            $types .= 's';
            $params[] = '%' . $searchTerm . '%';
        }

        $stmt = $this->mysqli->prepare($query);

        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        
        return $users;
    }

    public function getAbout() {
        $query = "SELECT about FROM profile LIMIT 1";
        $result = $this->mysqli->query($query);
        return $result->fetch_assoc()['about'] ?? '';
    }
}

// MAIN LOGIC: Initialize components and delegate request handling to the view
$userProfile = new UserProfile();
$userController = new SearchUserProfileController($userProfile); 
$userView = new SearchUserProfilePage($userController);
$userView->handleSearchUserProfileRequest();
?>

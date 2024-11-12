<?php
require '../connectDatabase.php';
session_start();

// BOUNDARY LAYER: HTML View for managing user accounts
class UserProfilePage {
    private $controller;

    public function __construct($controller) {
        $this->controller = $controller;
    }

    public function handleRequest() {
        $action = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        
        if (isset($action['createProfile'])) {
            header("Location: ProfileCreation.php");
            exit();
        }

        if (isset($action['viewProfile'])) {
            $username = $action['username'];
            $role_id = $action['role_id'];
            header("Location: admin_view_profile.php?username=" . urlencode($username) . "&role_id=" . urlencode($role_id));
            exit();
        }

        if (isset($action['updateProfile'])) {
            $role_id = $action['role_id'];
            header("Location: admin_update_profile.php?role_id=" . urlencode($role_id));
            exit();
        }

        if (isset($action['suspendProfile'])) {
            $username = $action['username'];
            $role_id = $action['role_id'];
            header("Location: admin_suspend_user_profiles.php?username=" . urlencode($username) . "&role_id=" . urlencode($role_id));
            exit();
        }

        // Render the profile management view
        $this->ManageUserProfileUI();
    }
    
    public function ManageUserProfileUI() {
        $profiles = $this->controller->getProfiles();
        $roles = $this->controller->getRoles();
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Manage User Profiles</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f9;
                    margin: 0;
                    padding: 0;
                }
                h1 {
                    text-align: center;
                    color: #333;
                    margin-top: 30px;
                }
                #main-table {
                    border-collapse: collapse;
                    width: 80%;
                    margin: 20px auto;
                    background-color: #fff;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }
                #main-table, 
                #main-table th, 
                #main-table td {
                    border: 1px solid #ddd;
                }
                #main-table th, 
                #main-table td {
                    padding: 12px 15px;
                    font-size: 18px;
                    text-align: center;
                }
                #main-table th {
                    background-color: #4CAF50;
                    color: white;
                }
                #main-table tr:hover {
                    background-color: #f1f1f1;
                }
                .select-label {
                    font-size: 20px;
                    margin-right: 10px;
                    color: #333;
                }
                #search {
                    font-size: 18px;
                    padding: 8px;
                }
                .button-font {
                    font-size: 16px;
                    padding: 10px 20px;
                    background-color: #2196F3; /* Blue button color */
                    color: white;
                    border: none;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }
                .button-font:hover {
                    background-color: #1976D2;
                }
                form {
                    display: inline-block;
                    margin-bottom: 15px;
                }
                .return-button {
                    font-size: 20px;
                    padding: 10px 20px;
                    background-color: #4CAF50; /* Green button color */
                    color: white;
                    border: none;
                    cursor: pointer;
                }
                .return-button:hover {
                    background-color: #45a049;
                }
            </style>
        </head>
        <body>
            <h1>Manage User Profiles</h1>

            <form method="get" action="admin_search_profile.php" style="text-align:center">
                <label for="role" class="select-label">Filter based on role:</label>
                <select id="role_id" name="role_id" class="select-label">
                    <option value="">All profiles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role['role_id']); ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button-font" id="filterProfile">Filter</button>
            </form>

            <br/><br/>
            <form method="post" action="profileCreation.php" style="text-align:center">
                <button type="submit" class="button-font">Create Profile</button>
            </form>

            <br/><br/>
            <table id="main-table">
                <tr>
                    <th>Profile</th>
                    <th>Status</th>
                    <th>Number of Accounts</th>
                    <th>Actions</th>
                </tr>
                <?php if (!empty($profiles)): ?>
                    <?php foreach ($profiles as $profile): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($profile['role_name']); ?></td>
                            <td><?php echo htmlspecialchars($profile['status_name']); ?></td>
                            <td><?php echo htmlspecialchars($profile['account_count']); ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($profile['username']); ?>">
                                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($profile['role_id']); ?>">
                                    <button type="submit" class="button-font" id="viewProfile" name="viewProfile">View</button>
                                </form>
                                <form method="post" action="">
                                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($profile['role_id']); ?>">
                                    <button type="submit" class="button-font" id="updateProfile" name="updateProfile">Update</button>
                                </form>
                                <form method="post" action="">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($profile['username']); ?>">
                                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($profile['role_id']); ?>">
                                    <button type="submit" class="button-font" id="suspendProfile" name="suspendProfile">Suspend</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No profiles found.</td>
                    </tr>
                <?php endif; ?>
            </table>

            <form method="post" action="admin_dashboard.php" style="text-align:center; display: flex; justify-content: center; margin-top: 20px;">
                <input type="submit" value="Return" class="return-button">
            </form>
        </body>
        </html>
        <?php
    }
}

// CONTROL LAYER: Manages data retrieval and updates based on Boundary's requests
class UserProfileDashboardController {
    private $userProfile;

    // Controller constructor takes the UserProfile model as a dependency
    public function __construct($userProfile) {
        $this->userProfile = $userProfile;
    }

    // Get all profiles, passing optional role name
    public function getProfiles($role_name = '') {
        return $this->userProfile->getAllProfiles($role_name);
    }

    // Get all roles
    public function getRoles() {
        return $this->userProfile->getAllRoles();
    }
}

// ENTITY LAYER: UserProfile handles all database interactions and data logic
class UserProfile {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    // Fetch all profiles, optionally filtered by role name
    public function getAllProfiles($role_name = '') {
        $query = "SELECT u.username, r.role_id, r.role_name, 
                         IFNULL(s.status_name, 'No Status') AS status_name, 
                         COUNT(u.user_id) AS account_count
                  FROM role r
                  LEFT JOIN users u ON r.role_id = u.role_id
                  LEFT JOIN status s ON s.status_id = u.status_id";
    
        if (!empty($role_name)) {
            $query .= " WHERE r.role_name = ?";
        }
    
        $query .= " GROUP BY r.role_id, s.status_name";
    
        $stmt = $this->mysqli->prepare($query);
    
        if (!empty($role_name)) {
            $stmt->bind_param('s', $role_name);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
    
        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }
    
        $stmt->close();
        return $profiles;
    }

    // Fetch all roles
    public function getAllRoles() {
        $query = "SELECT role_id, role_name FROM role";
        $result = $this->mysqli->query($query);
        $roles = [];
    
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
    
        return $roles;
    }
}

// MAIN LOGIC: Initialize components and handle the request
$database = new Database();
$mysqli = $database->getConnection();

$userProfileEntity = new UserProfile($mysqli);
$userController = new UserProfileDashboardController($userProfileEntity);
$userProfileView = new UserProfilePage($userController);
$userProfileView->handleRequest();

$database->closeConnection();
?>

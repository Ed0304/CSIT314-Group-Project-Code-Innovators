<?php
require '../connectDatabase.php';

// BOUNDARY LAYER: HTML View for managing user Profiles
class UserProfileView {
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
        $this->render();
    }
    
    public function render() {
        $profiles = $this->controller->getProfiles();
        $roles = $this->controller->getRoles();
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Manage UserProfile Profiles</title>
            <style>
                #main-table {
                    border-collapse: collapse;
                    width: 100%;
                }
                #main-table, 
                #main-table th, 
                #main-table td {
                    border: 1px solid black;
                }
                #main-table th, 
                #main-table td {
                    padding: 10px;
                    font-size: 20px;
                    text-align: center;
                }
                .select-label {
                    font-size: 24px;
                }
                #search {
                    font-size: 20px;
                }
                .button-font {
                    font-size: 18px;
                }
            </style>
        </head>
        <body>
            <h1 style="text-align:center">Manage user profiles here...</h1>

            <form method="get" action="admin_search_profile.php">
                <label for="role" class="select-label">Filter based on role:</label>
                <select id="role_id" name="role_id" class="select-label">
                    <option value="">All profiles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role['role_id']); ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="select-label" id="filterProfile">Filter</button>
            </form>

            <br/><br/>
            <form method="post" action="profileCreation.php" style="text-align:left">
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

            <form method="post" action="admin_dashboard.php" style="text-align:center">
                <br/>
                <input type="submit" value="Return" style="font-size: 24px">
            </form>
        </body>
        </html>
        <?php
    }
}

// CONTROL LAYER: Handle profile data management and pass data to the Boundary layer
class UserProfileController {
    private $userModel;

    public function __construct($mysqli) {
        $this->userModel = new UserProfile($mysqli);
    }

    public function getProfiles($role_name = '') {
        return $this->userModel->getAllProfiles($role_name);
    }

    public function getRoles() {
        return $this->userModel->getAllRoles();
    }
}

// ENTITY LAYER: Handles all database interactions for user data
class UserProfile {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

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

$userController = new UserProfileController($mysqli);
$userProfileView = new UserProfileView($userController);
$userProfileView->handleRequest();

$database->closeConnection();
?>

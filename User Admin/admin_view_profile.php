<?php
require 'connectDatabase.php';

// BOUNDARY LAYER: HTML View for managing user accounts
class UserAccountView
{
    private $users;
    private $about;

    public function __construct($users, $about)
    {
        $this->users = $users;
        $this->about = $about;
    }

    public function render()
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
                .search-bar {
                    margin-bottom: 24px;
                }
            </style>
        </head>
        <body>
            <h1 style="text-align:center">Users in this role</h1>

            <!-- Search Bar -->
            <form method="post" action="" class="search-bar" style="text-align:center">
                <input type="text" name="search_query" placeholder="Search by username" class="button-font">
                <button type="submit" class="button-font" name="searchUser">Search</button>
            </form>

            <!-- Create Profile Button -->
            <form method="post" action="profileCreation.php" style="text-align:center">
                <button type="submit" class="button-font">Create Profile</button>
            </form>

            <table id="main-table">
                <tr>
                    <th>UserID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <?php if (!empty($this->users)): ?>
                    <?php foreach ($this->users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['status_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['role_name'] ?? 'N/A'); ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($user['profile_id']); ?>">
                                    <button type="submit" class="button-font" name="viewAccount">View</button>
                                    <button type="submit" class="button-font" name="updateAccount">Update</button>
                                    <button type="submit" class="button-font" name="suspendAccount">Suspend</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
            </table>

            <form method="post" action="admin_manage_user_profiles.php" style="text-align:center">
                <br />
                <input type="submit" value="Return" style="font-size: 24px">
            </form>
        </body>
        </html>
        <?php
    }
}

// CONTROL LAYER: Handle form submissions and orchestrate data flow
class UserAccountController
{
    private $userProfile;

    public function __construct($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    public function handleRequest()
    {
        $action = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : null;
        $role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';

        // Handle search action
        if ($action && isset($action['searchUser'])) {
            $search_query = $action['search_query'] ?? '';
            $users = $this->userProfile->searchUsers($search_query, $role_id);
        } else {
            // Fetch data from the entity
            $users = $this->userProfile->getUsersByRole($role_id);
        }
        
        // Fetch 'about' information
        $about = $this->userProfile->getAbout();

        // Instantiate view with retrieved data
        $view = new UserAccountView($users, $about);

        // Process actions without direct interaction between boundary and entity layers
        if ($action) {
            $profile_id = $action['profile_id'] ?? ''; // Get profile_id from form input
            if (isset($action['viewAccount'])) {
                header("Location: profileDetails.php?profile_id=" . urlencode($profile_id));
                exit();
            } elseif (isset($action['updateAccount'])) {
                header("Location: admin_update_profile.php?profile_id=" . urlencode($profile_id));
                exit();
            } elseif (isset($action['suspendAccount'])) {
                header("Location: admin_suspend_profile.php?profile_id=" . urlencode($profile_id));
                exit();
            }
        }

        // Render the view if no action is taken
        $view->render();
    }
}

// ENTITY LAYER: UserProfile to handle all logic for user data and database interactions
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
            SELECT u.user_id, u.username, p.profile_id, s.status_name, r.role_name
            FROM users u
            JOIN profile p ON u.user_id = p.user_id
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

    public function searchUsers($search_query, $role_id = '') {
        $query = "
            SELECT u.user_id, u.username, p.profile_id, s.status_name, r.role_name
            FROM users u
            JOIN profile p ON u.user_id = p.user_id
            JOIN role r ON u.role_id = r.role_id
            JOIN status s ON u.status_id = s.status_id
            WHERE u.username LIKE ?";

        if (!empty($role_id)) {
            $query .= " AND u.role_id = ?";
        }

        $stmt = $this->mysqli->prepare($query);
        $search_term = "%" . $search_query . "%";
        
        if (!empty($role_id)) {
            $stmt->bind_param('si', $search_term, $role_id);
        } else {
            $stmt->bind_param('s', $search_term);
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

// MAIN LOGIC: Initialize components and handle the request
$role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
$userProfile = new UserProfile();
$userController = new UserAccountController($userProfile); 
$userController->handleRequest();
?>

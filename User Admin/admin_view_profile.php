<?php
require '../connectDatabase.php';
session_start();

// BOUNDARY LAYER: HTML View for managing user accounts
class UserAccountView
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function handleRequest()
    {
        $role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST;
            $search_query = $action['search_query'] ?? '';

            if (isset($action['searchUser'])) {
                $users = $this->controller->searchUsers($search_query, $role_id);
            } elseif (isset($action['viewAccount'])) {
                $profile_id = $action['profile_id'] ?? '';
                header("Location: profileDetails.php?profile_id=" . urlencode($profile_id));
                exit();
            } elseif (isset($action['updateAccount'])) {
                $profile_id = $action['profile_id'] ?? '';
                header("Location: admin_update_profile.php?profile_id=" . urlencode($profile_id));
                exit();
            } elseif (isset($action['suspendAccount'])) {
                $profile_id = $action['profile_id'] ?? '';
                header("Location: admin_suspend_profile.php?profile_id=" . urlencode($profile_id));
                exit();
            }
        } else {
            $users = $this->controller->getUsersByRole($role_id);
        }

        $about = $this->controller->getAbout();
        $this->render($users, $about);
    }

    public function render($users, $about)
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

            <form method="post" action="admin_manage_user_profiles.php" style="text-align:center">
                <br />
                <input type="submit" value="Return" style="font-size: 24px">
            </form>
        </body>
        </html>
        <?php
    }
}

// CONTROL LAYER: Manages data retrieval and updates based on Boundary's requests
class UserAccountController
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

    public function searchUsers($search_query, $role_id)
    {
        return $this->userProfile->searchUsers($search_query, $role_id);
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

    public function searchUsers($search_query, $role_id = '') {
        $query = "
            SELECT u.user_id, u.username, s.status_name, r.role_name
            FROM users u
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

// MAIN LOGIC: Initialize components and delegate request handling to the view
$userProfile = new UserProfile();
$userController = new UserAccountController($userProfile); 
$userView = new UserAccountView($userController);
$userView->handleRequest();
?>

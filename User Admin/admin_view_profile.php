<?php
require '../connectDatabase.php';
session_start();

// BOUNDARY LAYER: HTML View for managing user accounts
class ViewUserProfilePage
{
    private $controller;
    private $users; // Store fetched users
    private $about; // Store about information
    private $role_id; // Store role_id for UI use

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function handleRequest()
    {
        $this->role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
        $this->users = $this->controller->getUsersByProfile($this->role_id);
        $this->about = $this->controller->getAbout();
        $this->ViewUserProfileUI();
    }

    public function ViewUserProfileUI()
    {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>User Profile</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }

                h1 {
                    text-align: center;
                    margin-top: 30px;
                    color: #2c3e50;
                }

                #main-table {
                    border-collapse: collapse;
                    width: 80%;
                    margin: 20px auto;
                    background-color: #ffffff;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }

                #main-table, #main-table th, #main-table td {
                    border: 1px solid #ddd;
                }

                #main-table th, #main-table td {
                    padding: 15px;
                    text-align: center;
                    font-size: 18px;
                }

                #main-table th {
                    background-color: #4CAF50;
                    color: white;
                }

                #main-table tr:nth-child(even) {
                    background-color: #f2f2f2;
                }

                .button-font {
                    font-size: 24px;
                    padding: 10px 20px;
                    background-color: #3498db;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }

                .button-font:hover {
                    background-color: #2980b9;
                }

                form {
                    text-align: center;
                    margin-top: 20px;
                }

                .return-button {
                    background-color: #2ecc71;
                }

                .return-button:hover {
                    background-color: #27ae60;
                }

                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
                }

                .no-data {
                    text-align: center;
                    color: #e74c3c;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Users in this Role</h1>
                <table id="main-table">
                    <tr>
                        <th>UserID</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Role Description</th>
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
                            <td colspan="5" class="no-data">No users found for this role.</td>
                        </tr>
                    <?php endif; ?>
                </table>

                <form method="post" action="admin_manage_user_profiles.php">
                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->role_id); ?>">
                    <input type="submit" value="Return" class="button-font return-button">
                </form>
            </div>
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

    public function getUsersByProfile($role_id)
    {
        return $this->userProfile->getUsersByProfile($role_id);
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
        $this->mysqli = new mysqli('mariadb', 'root', '', 'csit314');
        if ($this->mysqli->connect_error) {
            die("Database connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function getUsersByProfile($role_id = '') {
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
        $userProfile = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        
        return $userProfile;
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

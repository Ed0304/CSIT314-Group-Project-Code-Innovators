<?php
require '../connectDatabase.php';
session_start();

// BOUNDARY LAYER: HTML View for managing user accounts
class SearchUserAccountsBasedOnProfilePage
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

    public function handleSearchUserAccountsBasedOnProfileRequest()
    {
        $this->role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
        $this->searchTerm = isset($_POST['searchTerm']) ? $_POST['searchTerm'] : ''; // Get search term from POST
        $this->users = $this->controller->searchUserAccountsBasedOnProfile($this->role_id, $this->searchTerm);
        $this->about = $this->controller->getAbout();
        $this->SearchUserAccountsBasedOnProfileUI();  
    }

    public function SearchUserAccountsBasedOnProfileUI()
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
                    width: 100%;
                    margin: 20px auto;
                    background-color: #fff;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }
                #main-table, #main-table th, #main-table td {
                    border: 1px solid #ddd;
                }
                #main-table th, #main-table td {
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
                .button-font {
                    font-size: 20px;
                    padding: 12px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    transition: background-color 0.3s ease;
                }
                .button-blue {
                    background-color: #007BFF;
                    color: white;
                    border: none;
                }
                .button-blue:hover {
                    background-color: #0056b3;
                }
                .button-green {
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                }
                .button-green:hover {
                    background-color: #45a049;
                }
                form {
                    display: inline-block;
                    margin-bottom: 15px;
                }
                .search-container {
                    text-align: center;
                    margin-top: 20px;
                }
                input[type="text"] {
                    font-size: 24px;
                    padding: 10px;
                    width: 300px;
                    margin-right: 10px;
                    border-radius: 5px;
                    border: 1px solid #ccc;
                }
                input[type="submit"] {
                    font-size: 24px;
                    padding: 12px 20px;
                    background-color: #007BFF;
                    color: white;
                    border: none;
                    cursor: pointer;
                    border-radius: 5px;
                    transition: background-color 0.3s ease;
                }
                input[type="submit"]:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <h1>Users in this role</h1>
            <!-- Added search bar and hidden field to pass role_id -->
            <div class="search-container">
                <form method="post" action="">
                    <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($this->role_id); ?>">
                    <input type="text" name="searchTerm" value="<?php echo htmlspecialchars($this->searchTerm); ?>" placeholder="Search by username">
                    <input type="submit" name="searchUser" value="Search" class="button-font button-blue">
                </form>
            </div>
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
                <input type="submit" value="Return" class="button-font button-green">
            </form>
        </body>
        </html>
        <?php
    }
}

// CONTROL LAYER: Manages data retrieval and updates based on Boundary's requests
class SearchUserAccountsBasedOnProfileController
{
    private $userProfile;

    public function __construct($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    public function searchUserAccountsBasedOnProfile($role_id, $searchTerm)
    {
        return $this->userProfile->searchUserAccountsBasedOnProfile($role_id, $searchTerm);
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

    public function searchUserAccountsBasedOnProfile($role_id = '', $searchTerm = '') {
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
$userController = new SearchUserAccountsBasedOnProfileController($userProfile); 
$userView = new SearchUserAccountsBasedOnProfilePage($userController);
$userView->handleSearchUserAccountsBasedOnProfileRequest();
?>

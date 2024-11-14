<?php
// Connect to the database
require '../connectDatabase.php';

// ENTITY LAYER: Represents user data and interacts with the database
class UserAccount
{
    public $username;
    public $role_name;
    public $status_name;

    private static $database;

    public function __construct($username, $role_name, $status_name)
    {
        $this->username = htmlspecialchars($username);
        $this->role_name = htmlspecialchars($role_name);
        $this->status_name = htmlspecialchars($status_name);
    }

    public static function setDatabase($database)
    {
        self::$database = $database;
    }

    public static function fetchUsers()
    {
        $query = "SELECT u.username, r.role_name, s.status_name
                  FROM users u
                  JOIN role r ON u.role_id = r.role_id
                  JOIN status s ON u.status_id = s.status_id";
        $result = self::$database->getConnection()->query($query);

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new self($row['username'], $row['role_name'], $row['status_name']);
        }
        return $users;
    }

    public static function searchUserAccount($role, $username)
    {
        $query = "SELECT u.username, r.role_name, s.status_name
                  FROM users u
                  JOIN role r ON u.role_id = r.role_id
                  JOIN status s ON u.status_id = s.status_id
                  WHERE 1=1";

        if (!empty($role)) {
            $query .= " AND r.role_name = '" . self::$database->getConnection()->real_escape_string($role) . "'";
        }
        if (!empty($username)) {
            $query .= " AND u.username LIKE '%" . self::$database->getConnection()->real_escape_string($username) . "%'";
        }

        $result = self::$database->getConnection()->query($query);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new self($row['username'], $row['role_name'], $row['status_name']);
        }
        return $users;
    }
    public static function fetchRoles()
    {
        // Define the database connection
        $connection = new mysqli("mariadb", "root", "", "csit314");

        // Check connection
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        $roles = [];
        $sql = "SELECT DISTINCT role_name FROM role";
        
        if ($result = $connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row['role_name'];
            }
            $result->free();
        }

        $connection->close();
        return $roles;
    }

}


// CONTROLLER LAYER: Manage data flow between Boundary and Entity layers
class SearchUserAccountController
{
    private $users;

    public function __construct()
    {
        $this->users = [];
    }

    public function getUsers()
    {
        $this->users = UserAccount::fetchUsers();
        return $this->users;
    }

    public function searchUserAccounts($role, $username)
    {
        $this->users = UserAccount::searchUserAccount($role, $username);
        return $this->users;
    }
    public function getRoles()
    {
        return UserAccount::fetchRoles();
    }
}


// BOUNDARY LAYER: HTML View for managing user accounts and handling user interactions
class SearchUserAccountPage
{
    private $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function SearchUserAccountUI() //Search functionality is implemented inside this function.
    {
        // Fetch users based on search or default (all users)
        $users = $this->controller->getUsers();  // Initially fetch all users
        $roles = $this->controller->getRoles();
        // If the search button is clicked, fetch the filtered users
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['searchButton'])) {
            $role = $_POST['role'];
            $username = $_POST['search'];
            $users = $this->controller->searchUserAccounts($role, $username);
        }

        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Manage User Accounts</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                h1 {
                    text-align: center;
                    color: #333;
                    padding: 20px;
                }

                table {
                    border-collapse: collapse;
                    width: 80%;
                    margin: 20px auto;
                    background-color: #fff;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 12px;
                    text-align: center;
                }
                th {
                    background-color: #4CAF50;
                    color: white;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }

                form {
                    margin: 20px 0;
                    text-align: center;
                }

                input[type="text"], select {
                    padding: 10px;
                    font-size: 18px;
                    margin: 5px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    width: 250px;
                }

                button[type="submit"], input[type="submit"] {
                    padding: 12px 20px;
                    font-size: 18px;
                    background-color: #007BFF;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    margin-top: 10px;
                }

                button[type="submit"]:hover, input[type="submit"]:hover {
                    background-color: #0056b3;
                }

                .button-font {
                    font-size: 16px;
                    padding: 8px 16px;
                }

                .select-label {
                    font-size: 20px;
                    margin-right: 10px;
                }

                .return-btn {
                    background-color: #4CAF50;
                    color: white;
                    font-size: 20px;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                    display: block;
                    width: 200px;
                    margin-left: auto;
                    margin-right: auto;
                    text-align: center;
                }

                .return-btn:hover {
                    background-color: #45a049;
                }
            </style>
        </head>
        <body>
            <h1>Manage User Accounts</h1>
            <form method="POST">
                 <label for="role" class="select-label">Filter by Role:</label>
            <select id="role" name="role">
                <option value="">All roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo htmlspecialchars($role); ?>"><?php echo htmlspecialchars($role); ?></option>
                <?php endforeach; ?>
            </select>
                <input type="text" id="search" name="search" placeholder="Enter username" />
                <button type="submit" name="searchButton">Search</button>
            </form>

            <form method="post" action="accountCreation.php" style="text-align:center;">
                <button type="submit" name="createAccount">Create New User Account</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user->username; ?></td>
                                <td><?php echo $user->role_name; ?></td>
                                <td><?php echo $user->status_name; ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="username" value="<?php echo $user->username; ?>">
                                        <button type="submit" name="viewAccount" class="button-font">View</button>
                                        <button type="submit" name="updateAccount" class="button-font">Update</button>
                                        <button type="submit" name="suspendAccount" class="button-font">Suspend</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="admin_dashboard.php" class="return-btn">Return</a>
        </body>
        </html>
        <?php
    }

    public function handleUserInteractions()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['createAccount'])) {
                header("Location: accountCreation.php");
                exit();
            } elseif (isset($_POST['viewAccount'])) {
                $username = $_POST['username'];
                header("Location: admin_view_account.php?username=" . urlencode($username));
                exit();
            } elseif (isset($_POST['updateAccount'])) {
                $username = $_POST['username'];
                header("Location: admin_update_user_acc.php?username=" . urlencode($username));
                exit();
            } elseif (isset($_POST['suspendAccount'])) {
                $username = $_POST['username'];
                header("Location: admin_suspend_user_acc.php?username=" . urlencode($username));
                exit();
            }
        }
    }
}

// Main execution
$database = new Database();
UserAccount::setDatabase($database);
$controller = new SearchUserAccountController();
$page = new SearchUserAccountPage($controller);
$page->handleUserInteractions();
$page->SearchUserAccountUI();
?>

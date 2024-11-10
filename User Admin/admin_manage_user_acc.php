<?php
require '../connectDatabase.php';

// ENTITY LAYER: Represents user data and interacts with the database
class UserAccount
{
    public $username;
    public $role_name;
    public $status_name;

    public function __construct($username, $role_name, $status_name)
    {
        $this->username = htmlspecialchars($username);
        $this->role_name = htmlspecialchars($role_name);
        $this->status_name = htmlspecialchars($status_name);
    }

    public static function fetchUsers($mysqli)
    {
        $query = "SELECT u.username, r.role_name, s.status_name
                  FROM users u
                  JOIN role r ON u.role_id = r.role_id
                  JOIN status s ON u.status_id = s.status_id";
        $result = $mysqli->query($query);

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new self($row['username'], $row['role_name'], $row['status_name']);
        }
        return $users;
    }

    public static function searchUserAccount($mysqli, $role, $username)
    {
        $query = "SELECT u.username, r.role_name, s.status_name
                  FROM users u
                  JOIN role r ON u.role_id = r.role_id
                  JOIN status s ON u.status_id = s.status_id
                  WHERE 1=1";

        if (!empty($role)) {
            $query .= " AND r.role_name = '" . $mysqli->real_escape_string($role) . "'";
        }
        if (!empty($username)) {
            $query .= " AND u.username LIKE '%" . $mysqli->real_escape_string($username) . "%'";
        }

        $result = $mysqli->query($query);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new self($row['username'], $row['role_name'], $row['status_name']);
        }
        return $users;
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

    public function render()
    {
        $users = $this->controller->getUsers();
        ?>
        <!DOCTYPE HTML>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Manage User Accounts</title>
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
            <h1 style="text-align:center">Manage user accounts here...</h1>
            <form method="POST">
                <label for="role" class="select-label">Filter based on:</label>
                <select id="role" name="role" class="select-label">
                    <option value="">All roles</option>
                    <option value="used car agent">Used Car Agent</option>
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                </select>
                <input type="text" id="search" name="search" placeholder="Enter username" />
                <button type="submit" name="searchButton" id="searchButton">Search</button>
                <br /><br />
            </form>

            <form method="post" action="accountCreation.php">
                <button type="submit" name="createAccount" class="select-label" id="createAccount">Create new user account</button>
            </form>
            <br /><br />

            <table id="main-table">
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user->username; ?></td>
                            <td><?php echo $user->role_name; ?></td>
                            <td><?php echo $user->status_name; ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="username" value="<?php echo $user->username; ?>">
                                    <button type="submit" class="button-font" name="viewAccount">View</button>
                                    <button type="submit" class="button-font" name="updateAccount">Update</button>
                                    <button type="submit" class="button-font" name="suspendAccount">Suspend</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No users found.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <form method="post" action="admin_dashboard.php" style="text-align:center">
                <br />
                <input type="submit" value="Return" style="font-size: 24px">
            </form>
        </body>
        </html>
        <?php
    }

    public function handleUserInteractions()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['searchButton'])) {
                $role = $_POST['role'];
                $username = $_POST['search'];
                $this->controller->searchUsers($role, $username);
            } elseif (isset($_POST['createAccount'])) {
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

// CONTROLLER LAYER: Manage data flow between Boundary and Entity layers
class SearchUserAccountController
{
    private $mysqli;
    private $users;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
        $this->users = [];
    }

    public function getUsers()
    {
        $this->users = UserAccount::fetchUsers($this->mysqli);
        return $this->users;
    }

    public function searchUsers($role, $username)
    {
        $this->users = UserAccount::searchUserAccount($this->mysqli, $role, $username);
        return $this->users;
    }
}

// MAIN LOGIC: Initialize components
$database = new Database();
$mysqli = $database->getConnection();

$controller = new SearchUserAccountController($mysqli);
$boundary = new SearchUserAccountPage($controller);

$boundary->handleUserInteractions();
$boundary->render();

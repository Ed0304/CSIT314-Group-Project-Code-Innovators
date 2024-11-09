<?php
require '../connectDatabase.php';

// ENTITY LAYER: Represents user data without direct database interactions
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
}

// BOUNDARY LAYER: HTML View for managing user accounts
class SearchUserAccountPage
{
    private $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function render()
    {
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
            <form method="POST" action="admin_manage_user_acc.php">
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

            <form method="post" action="">
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
                <?php if (!empty($this->users)): ?>
                    <?php foreach ($this->users as $user): ?>
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
}

// CONTROL LAYER: Handle form submissions and orchestrate data flow
class SearchUserAccountController
{
    private $mysqli;
    private $view;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    // Fetch users from the database and create User objects
    private function getUsers()
    {
        $query = "SELECT u.username, r.role_name, s.status_name
                  FROM users u
                  JOIN role r ON u.role_id = r.role_id
                  JOIN status s ON u.status_id = s.status_id";
        $result = $this->mysqli->query($query);

        $users = []; // Initialize an array to store User objects
        while ($row = $result->fetch_assoc()) { // Fetch rows one by one
            $users[] = new UserAccount($row['username'], $row['role_name'], $row['status_name']); // Create User object
        }
        return $users; // Return the array of User objects
    }

    // SEARCH USER ACCOUNT: Search for users based on role and username
    private function searchUserAccount($role, $username)
    {
        $query = "SELECT u.username, r.role_name, s.status_name
                  FROM users u
                  JOIN role r ON u.role_id = r.role_id
                  JOIN status s ON u.status_id = s.status_id
                  WHERE 1=1";

        if (!empty($role)) {
            $query .= " AND r.role_name = '" . $this->mysqli->real_escape_string($role) . "'";
        }

        if (!empty($username)) {
            $query .= " AND u.username LIKE '%" . $this->mysqli->real_escape_string($username) . "%'";
        }

        $result = $this->mysqli->query($query);

        $users = []; // Initialize an array to store User objects
        while ($row = $result->fetch_assoc()) { // Fetch rows one by one
            $users[] = new UserAccount($row['username'], $row['role_name'], $row['status_name']); // Create User object
        }
        return $users; // Return the array of User objects
    }

    public function handleRequest()
    {
        $action = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : null;

        // Check if search action is triggered
        if (isset($action['searchButton'])) {
            $role = $action['role'];
            $username = $action['search'];
            $users = $this->searchUserAccount($role, $username);
        } else {
            // Fetch users and initialize the view
            $users = $this->getUsers();
        }

        $this->view = new SearchUserAccountPage($users); // Initialize the view with the UserAccount objects

        if (isset($action['createAccount'])) {
            header("Location: accountCreation.php");
            exit();
        }

        if (isset($action['viewAccount'])) {
            $username = $action['username'];
            header("Location: admin_view_account.php?username=" . urlencode($username));
            exit();
        }

        if (isset($action['updateAccount'])) {
            $username = $action['username'];
            header("Location: admin_update_user_acc.php?username=" . urlencode($username)); 
            exit();
        }

        if (isset($action['suspendAccount'])) {
            $username = $action['username'];
            header("Location: admin_suspend_user_acc.php?username=" . urlencode($username));
            exit();
        }

        // Render the view if no action is taken
        $this->view->render();
    }
}

// MAIN LOGIC: Initialize components and handle the request
$database = new Database();
$mysqli = $database->getConnection(); // Get the database connection

$userController = new SearchUserAccountController($mysqli); // Instantiate the controller
$userController->handleRequest();

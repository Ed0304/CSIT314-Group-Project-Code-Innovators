<?php
require 'connectDatabase.php';

// ENTITY LAYER: Represents user data without direct database interactions
class User
{
    // This class remains empty, representing the user entity.
}

// BOUNDARY LAYER: HTML View for managing user accounts
class UserAccountView
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
            <!-- Form for filtering users based on role and username -->
                <form method="POST" action="admin_manage_user_acc.php">
                    <label for="role" class="select-label">Filter based on:</label>
                    <select id="role" name="role" class="select-label">
                        <option value="" class="select-label">All roles</option>
                        <option value="used car agent" class="select-label">Used Car Agent</option>
                        <option value="buyer" class="select-label">Buyer</option>
                        <option value="seller" class="select-label">Seller</option>
                    </select>
                    <input type="text" id="search" name="search" placeholder="Enter username" />
                    <button type="submit" name="searchButton" id="searchButton">Search</button>
                    <br /><br />
                </form>
                <!-- Form ends here-->

                <form method="post" action="">
                    <button type="submit" name="createAccount" class="select-label" id="createAccount">Create new user
                        account</button>
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
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['status_name']); ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                        <button type="submit" class="button-font" name="viewAccount">View</button>
                                        <button type="submit" class="button-font" name="updateAccount">Update</button>
                                        <button type="submit" class="button-font" name="suspendAccount">Suspend</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No users found.</td>
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
class UserAccountController
{
    private $mysqli;
    private $view;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    // Fetch users from the database
    private function getUsers()
    {
        $query = "SELECT u.username, r.role_name, s.status_name
                  FROM users u
                  JOIN role r ON u.role_id = r.role_id
                  JOIN status s ON u.status_id = s.status_id";
        $result = $this->mysqli->query($query);

        $users = []; // Initialize an array to store users
        while ($row = $result->fetch_assoc()) { // Fetch rows one by one
            $users[] = $row; // Add each row to the users array
        }
        return $users; // Return the array of users
    }

    // SEARCHUSERACCOUNT: Search for users based on role and username
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

        $users = []; // Initialize an array to store users
        while ($row = $result->fetch_assoc()) { // Fetch rows one by one
            $users[] = $row; // Add each row to the users array
        }
        return $users; // Return the array of users
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

        $this->view = new UserAccountView($users); // Initialize the view with the users

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

$userController = new UserAccountController($mysqli); // Instantiate the controller
$userController->handleRequest();
?>
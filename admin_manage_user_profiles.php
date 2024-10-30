<?php
require 'connectDatabase.php';

// ENTITY LAYER: Represents user data without direct database interactions
class User
{
    // This class remains empty, representing the user entity.
}

// BOUNDARY LAYER: HTML View for managing user Profiles
class UserProfileView
{
    private $profiles;

    public function __construct($profiles)
    {
        $this->profiles = $profiles;
    }

    public function render()
    {
        ?>
        <!DOCTYPE HTML>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Manage User Profiles</title>
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
            <<!-- Form for filtering users based on role and username -->
                <form method="POST" action="admin_manage_user_profiles.php">
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
                    <button type="submit" name="createProfile" class="select-label" id="createProfile">Create new user
                        profile</button>
                </form>
                <br /><br />

                <table id="main-table">
                    <tr>
                        <th>Username</th>
                        <th>Full name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php if (!empty($this->profiles)): ?>
                        <?php foreach ($this->profiles as $profile): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($profile['username']); ?></td>
                                <td><?php echo htmlspecialchars($profile['first_name']); ?>
                                    <?php echo htmlspecialchars($profile['last_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($profile['role_name']); ?></td>
                                <td><?php echo htmlspecialchars($profile['status_name']); ?></td>
                                <td>
                                    <!-- Form for viewing profile -->
                                    <form method="post" action="">
                                        <input type="hidden" name="username"
                                            value="<?php echo htmlspecialchars($profile['username']); ?>">
                                        <button type="submit" class="button-font" id="viewProfile" name="viewProfile">View</button>
                                    </form>

                                    <!-- Form for updating profile -->
                                    <form method="post" action="">
                                        <input type="hidden" name="username"
                                            value="<?php echo htmlspecialchars($profile['username']); ?>">
                                        <button type="submit" class="button-font" id="updateProfile"
                                            name="updateProfile">Update</button>
                                    </form>

                                    <!-- Form for suspending profile -->
                                    <form method="post" action="">
                                        <input type="hidden" name="username"
                                            value="<?php echo htmlspecialchars($profile['username']); ?>">
                                        <button type="submit" class="button-font" id="suspendProfile"
                                            name="suspendProfile">Suspend</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No profiles found.</td>
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
class UserProfileController
{
    private $mysqli;
    private $view;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli; // Store the database connection
        $this->view = null;
    }

    // Fetch users from the database
    private function getUsers()
    {
        $query = "SELECT u.username, p.first_name, p.last_name, r.role_name, s.status_name
                  FROM profile p 
                  JOIN users u ON p.user_id = u.user_id 
                  JOIN role r ON r.role_id = u.role_id
                  JOIN status s ON s.status_id = p.status_id";
                  JOIN role r ON r.role_id = u.role_id
                  JOIN status s ON s.status_id = p.status_id";
        $result = $this->mysqli->query($query);

        if (!$result) {
            die("Query failed: " . $this->mysqli->error); // Display the error if the query fails
        }

        $profiles = []; // Initialize an array to store users
        while ($row = $result->fetch_assoc()) { // Fetch rows one by one
            $profiles[] = $row; // Add each row to the users array
        }
        return $profiles; // Return the array of users
    }

    // SEARCHUSERPROFILE: Search for users based on role and username
    private function searchUserProfile($role, $username)
    {
        $query = "SELECT u.username, p.first_name, p.last_name, r.role_name, s.status_name
              FROM profile p
              JOIN users u ON u.user_id = p.user_id
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
            $profiles = $this->searchUserProfile($role, $username);
        } else {
            // Fetch users and initialize the view
            $profiles = $this->getUsers();
        }

        $this->view = new UserProfileView($profiles); // Initialize the view with the users

        if (isset($action['createProfile'])) {
            header("Location: ProfileCreation.php");
            exit();
        }

        if (isset($action['viewProfile'])) {
            $username = $action['username'];
            header("Location: admin_view_Profile.php?username=" . urlencode($username));
            exit();
        }

        if (isset($action['updateProfile'])) {
            // Placeholder for updating logic
            echo "Redirecting to update Profile page..."; // Replace with actual logic
            exit();
        }

        if (isset($action['suspendProfile'])) {
            // Placeholder for suspending logic
            echo "Redirecting to suspend Profile page..."; // Replace with actual logic
            exit();
        }

        // Render the view if no action is taken
        $this->view->render();
    }
}

// MAIN LOGIC: Initialize components and handle the request
$database = new Database();
$mysqli = $database->getConnection(); // Get the database connection

$userController = new UserProfileController($mysqli); // Instantiate the controller
$userController->handleRequest(); // Handle the request

// Close the database connection
$database->closeConnection();
?>
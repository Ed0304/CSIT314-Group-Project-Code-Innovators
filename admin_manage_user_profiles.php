<?php
// ENTITY: Database connection and user profile data retrieval
class UserProfile {
    private $pdo;

    // Constructor for establishing a database connection
    public function __construct($host, $db, $user, $pass) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    // Method to retrieve user profiles, optionally filtered by username
    public function getProfiles($search = null) {
        // Base query to fetch user profiles with role names
        $query = "SELECT  p.first_name, p.last_name, p.email, p.phone_num, r.role_name FROM profile p JOIN users u ON p.user_id = u.user_id JOIN role r ON r.role_id = u.role_id"; // Ensure that the profile table has role_id

        // If a search term is provided, append WHERE clause to filter results
        if ($search) {
            $search = '%' . $search . '%'; // Prepare the search term for LIKE query
            $query .= " WHERE p.first_name LIKE :search OR p.last_name LIKE :search OR p.email LIKE :search OR p.user_id LIKE :search";
        }

        // Prepare and execute the statement
        $stmt = $this->pdo->prepare($query);
        if ($search) {
            $stmt->bindParam(':search', $search);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// CONTEXT: Handle form submissions and prepare the view
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : null;

// Check if the form to create a new profile was submitted
if (isset($action['createProfile'])) {
    header("Location: profileCreation.php");
    exit();
}

// Instantiate UserProfile class to manage user profile data
$search = isset($action['search']) ? $action['search'] : null;
$userProfileModel = new UserProfile('localhost', 'csit314', 'root', '');

// Retrieve profiles based on search criteria
$profiles = $userProfileModel->getProfiles($search);
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Profiles</title>
    <style>
        #main-table {
            border-collapse: collapse; /* Merge cell borders */
            width: 100%; /* Optional: Set width of the table */
        }

        #main-table, 
        #main-table th, 
        #main-table td {
            border: 1px solid black; /* Set border for the table and its cells */
        }

        #main-table th, 
        #main-table td {
            padding: 10px; /* Add space inside cells */
            font-size: 20px; /* Set font size for text in cells */
            text-align: center; /* Align text to the center */
        }
        .select-label {
            font-size: 24px; /* Font size for select labels */
        }
        #search {
            font-size: 20px; /* Font size for search input */
        }
        .button-font{
            font-size: 18px;
        }
    </style>
</head>
<body>
    <h1 style="text-align:center">Manage user profiles here...</h1>

    <!-- Filter and search form -->
    <label for="role" class="select-label">Filter based on:</label>
    <select id="role" name="role" class="select-label">
        <option value="" class="select-label">All roles</option>
        <option value="agent" class="select-label">Used Car Agent</option>
        <option value="buyer" class="select-label">Buyer</option>
        <option value="seller" class="select-label">Seller</option>
    </select>

    <input type="text" name="search" id="search" placeholder="Enter username or email" />
    <button type="submit" name="searchBtn" id="search">Search</button>
    <br/><br/>
    
    <form method="post" action="">
        <!-- Button to create a new user profile -->
        <button type="submit" name="createProfile" class="select-label" id="createProfile">Create new user profile</button>
    </form>
    <br/><br/>

    <!-- TABLE: Displaying user profiles -->
    <table id="main-table">
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email Address</th>
            <th>Phone Num</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php if (!empty($profiles)): ?>
            <?php foreach ($profiles as $profile): ?>
                <tr>
                    <td><?php echo htmlspecialchars($profile['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($profile['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($profile['email']); ?></td>
                    <td><?php echo htmlspecialchars($profile['phone_num']); ?></td>
                    <td><?php echo htmlspecialchars($profile['role_name']); ?></td>
                    <td>
                        <button class="button-font">View</button>
                        <button class="button-font">Update</button>
                        <button class="button-font">Suspend</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No profiles found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Back to Dashboard button -->
    <form method="post" action="admin_dashboard.php" style="text-align:center">
        <br/>
        <input type="submit" value="Return" style="font-size: 24px">
    </form>
</body>
</html>

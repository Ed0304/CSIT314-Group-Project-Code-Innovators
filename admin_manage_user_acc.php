<?php
// Entity: Database connection and user data retrieval
class User {
    private $pdo;

    public function __construct($host, $db, $user, $pass) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function getUsers() {
        $stmt = $this->pdo->query("SELECT u.username, u.password, r.role_name 
            FROM users u
            JOIN role r ON u.role_id = r.role_id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Behavior: Handle form submissions
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : null;
if (isset($action['createAccount'])) {
    header("Location: accountCreation.php");
    exit();
}

// Context: Prepare the view
$userModel = new User('localhost', 'csit314', 'root', '');
$users = $userModel->getUsers();
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
        .button-font{
            font-size: 18px;
        }
    </style>
</head>
<body>
    <h1 style="text-align:center">Manage user accounts here...</h1>
    
    <label for="role" class="select-label">Filter based on:</label>
    <select id="role" name="role" class="select-label">
        <option value="" class="select-label">All roles</option>
        <option value="agent" class="select-label">Used Car Agent</option>
        <option value="buyer" class="select-label">Buyer</option>
        <option value="seller" class="select-label">Seller</option>
    </select>
    <input type="text" id="search" name="search" placeholder="Enter username"/> 
    <button type="submit" name="search" id="search">Search</button>
    <br/><br/>

    <form method="post" action="">
        <button type="submit" name="createAccount" class="select-label" id="createAccount">Create new user account</button>
    </form>
    <br/><br/>

    <table id="main-table">
        <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['password']); ?></td>
                    <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                    <td>
                        <button class="button-font">View</button>
                        <button class="button-font">Update</button>
                        <button class="button-font">Suspend</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No users found.</td>
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

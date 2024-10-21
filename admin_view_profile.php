<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "csit314";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user profile
if (isset($_GET['username'])) {
    $input_username = $_GET['username'];

    // Prepare and execute the SQL statement to fetch user profile
    $stmt = $conn->prepare("SELECT p.first_name, p.last_name, p.gender, p.about, p.profile_image, u.email, u.phone_num, r.role_name 
        FROM profile p
        JOIN users u ON u.user_id = p.user_id
        JOIN role r ON u.role_id = r.role_id
        WHERE u.username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userProfile = $result->fetch_assoc();
    } else {
        echo "Error: User profile not found.";
        $userProfile = null;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Information</title>
    <style>
        #infoTable th, td {
            font-size: 24px;
            text-align: center;
        }
        #infoTable {
            margin: auto;
        }
        .button {
            font-size: 24px;
            padding: 10px 20px;
            margin: 5px;
        }
        img.profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center">Profile Information</h1>
    <table id="infoTable">
        <?php if ($userProfile): ?>
            <tr>
                <td><strong>Profile Picture</strong></td>
                <td colspan="2">
                    <?php if (!empty($userProfile['profile_image'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($userProfile['profile_image']); ?>" class="profile-image" alt="Profile Picture">
                    <?php else: ?>
                        <img src="default-profile.jpg" class="profile-image" alt="Default Profile Picture">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Full Name</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['first_name'] . ' ' . $userProfile['last_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Role</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['role_name']); ?></td>
            </tr>   
            <tr>
                <td><strong>Email</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['email']); ?></td>
            </tr>
            <tr>
                <td><strong>Phone Number</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['phone_num']); ?></td>
            </tr>
            <tr>
                <td><strong>Gender</strong></td>
                <td colspan="2">
                <?php
                    if ($userProfile['gender'] == 'M') {
                        echo 'Male';
                    } elseif ($userProfile['gender'] == 'F') {
                         echo 'Female';
                    } else {
                        echo htmlspecialchars($userProfile['gender']); // Default value if not 'M' or 'F'
                    }
                ?>
            </td>
            </tr>
            <tr>
                <td><strong>About</strong></td>
                <td colspan="2"><?php echo htmlspecialchars($userProfile['about']); ?></td>
            </tr>
            <tr>
            <td>
                <form action="admin_manage_user_profiles.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Return to profiles list</button>
            </form>
            </td>
            
            <td>
                <form action="admin_update_user_profile.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Update account profile</button>
            </form>
            </td>
            <td>
                <form action="admin_suspend_user_profile.php" class="form-body">
                <button type="submit" value="Return" style="font-size: 24px">Suspend this profile</button>
            </form>
            </td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="3">Profile not found.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

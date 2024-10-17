<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // Retrieve the username from the session

// Handle redirection based on button clicks
if (isset($_POST['userAcc'])) {
    header("Location: admin_manage_user_acc.php");
    exit();
}

if (isset($_POST['userProfile'])) {
    header("Location: admin_manage_user_profiles.php");
    exit();
}

if (isset($_POST['logout'])) {
    header("Location: logout.php");
    exit();
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<style>
    .header {
        text-align: center;
    }
    .headDiv {
        text-align: center;
        background-color: green;
        border-bottom: 2px solid black;
    }
    .formBody {
        text-align: center;
    }
    #logout, #userAcc, #userProfile {
        font-size: 18px;
    }
    .mainInterface {
        text-align: center;
        background-color: white;
        border: 1px solid black;
        padding: 10px;
    }
</style>
<body>
    <div class="headDiv">
        <h1 class="header">Welcome to the Admin Dashboard, <?php echo htmlspecialchars($username); ?>!</h1>
        <h2 class="header">What would you like to do for today?</h2>
    </div>

    <div class="mainInterface">
        <form method="post" class="formBody">
            <br/>
            <br/>
            <button type="submit" id="userAcc" name="userAcc">Manage user accounts</button>
            <br/>
            <br/>
            <button type="submit" id="userProfile" name="userProfile">Manage user profiles</button>
            <br/>
            <br/>
            <input type="submit" id="logout" value="Logout" name="logout">
            <br/>
            <br/>
        </form>
    </div>
</body>
</html>

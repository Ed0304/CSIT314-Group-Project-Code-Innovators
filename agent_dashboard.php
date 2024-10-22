<?php
session_start(); // Start the session to access session data

// Check if the username exists in the session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Handle logout
if (isset($_POST['logout'])) {
    // Destroy session and redirect to logout page
    session_destroy(); // Make sure to destroy the session
    header("Location: logout.php");
    exit();
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <title>Used Car Agent Dashboard</title>
</head>
<body>
    <h1> Hello, <?php echo htmlspecialchars($username); ?>! </h1> <!-- Display the username -->
    <h2> What would you like to do today? </h2>

    <!-- Other form code for listings here -->

    <!-- Logout form -->
    <form method="POST">
        <input type="submit" id="logout" value="Logout" name="logout">
    </form>
</body>
</html>

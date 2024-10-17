<?php
session_start(); // Start the session

$servername = "localhost"; // Database server name
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "csit314"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form input
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $role = htmlspecialchars($_POST['role']); // Retrieve the selected role

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if username exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_password);
        $stmt->fetch();

        // Verify password (plain-text comparison for testing)
        if ($password === $stored_password) {
            // Store the username in session
            $_SESSION['username'] = $username;

            // Redirect based on role
            if ($role === "admin") {
                header("Location: admin_dashboard.php"); // Redirect to admin dashboard
                exit();
            } else if ($role === "agent") {
                header("Location: agent_dashboard.php"); // Redirect to agent dashboard
                exit();
            } else if ($role === "buyer") {
                header("Location: buyer_dashboard.php"); // Redirect to buyer dashboard
                exit();
            } else if ($role === "seller") {
                header("Location: seller_dashboard.php"); // Redirect to seller dashboard
                exit();
            } else {
                echo "Invalid role selected.";
            }
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "Invalid username or password.";
    }
    
    $stmt->close();
} 

$conn->close();
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <link rel="stylesheet" href="login.css"/>
    <title>CSIT314-PROJECT</title>
</head>
<body>
    <div class="website-title">
        <br/><h1>CSIT314-GROUP PROJECT</h1>
        <h2>Made by: Code Innovators!</h2>
    </div>
    <form action="" method="POST">
        <div class="form-body">
            <br/><br/>
            <label for="role" class="form-label">Login As:</label>
            <select id="role" name="role" class="form-label" required>
                <option value="admin" class="form-label">User Admin</option>
                <option value="agent" class="form-label">Used Car Agent</option>
                <option value="buyer" class="form-label">Buyer</option>
                <option value="seller" class="form-label">Seller</option>
            </select>
            <br/><br/>
            <label for="username" class="form-label">Username </label>
            <input type="text" id="username" name="username" class="form-label" required/>
            <br/><br/>
            <label for="password" class="form-label">Password </label>
            <input type="password" id="password" name="password" class="form-label" required/>
            <br/><br/>
            <button type="submit" class="form-label">Submit</button>
            <br/>    
        </div>
    </form>
    <div class="submit">
        <br/>
        <button onclick="hello_world()" style="display: block; margin: 0 auto; font-size: 24px;" title="See who are behind the scenes of this project!">Credits</button>
    </div>
</body>
<script src="login.js"></script>
</html>

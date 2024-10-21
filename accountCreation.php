<?php
// Database configuration
$servername = "localhost"; // Your database server (usually localhost)
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "csit314"; // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone_num = $_POST['phone_num'];
    $role_name = $_POST['role'];

    // Prepare and execute a statement to get the role ID
    $stmt = $conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
    $stmt->bind_param("s", $role_name);
    $stmt->execute();
    $stmt->bind_result($role_id);
    $stmt->fetch();
    $stmt->close();

    // Check if the role ID was found
    if (!$role_id) {
        die("Error: Role not found.");
    }

    // Prepare and bind the SQL statement to insert the new account
    $stmt = $conn->prepare("INSERT INTO users (username, password, role_id, email, phone_num) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $username, $password, $role_id, $email, $phone_num); 

    // Execute the statement
    if ($stmt->execute()) {
        echo "New account created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>

<html>
<head>
    <title> Account Creation Page </title>
    <style>
        .form-body {
            text-align: center;
        }
        .select-label {
            font-size: 24px;
        }
        /* Style for invisible table */
        .invisible-table {
            border-collapse: collapse;
            width: 0%; /* Set a width for the table */
            margin: auto; /* Center the table */
        }
        .invisible-table td {
            border: none; /* No border for invisibility */
            padding: 10px; /* Add some padding */
        }
    </style>
</head>
<body>
    <div style="background-color: red" class="header">
        <h1 style="text-align:center"> Account Creation </h1>
        <h2 style="text-align:center"> Please fill in the following details</h2>
        <h3 style="text-align:center"> All fields are mandatory </h3>
    </div>
    <form class="form-body" method="POST" action=""> <!-- Add method and action attributes -->
        <table class="invisible-table">
            <tr>
                <td><label style="font-size: 24px">Username:</label></td>
                <td><input type="text" name="username" style="font-size: 24px" required/></td> 
            </tr>
            <tr>
                <td><label style="font-size: 24px">Password:</label></td>
                <td><input type="password" name="password" style="font-size: 24px" required/></td> 
            </tr>
            <tr>
                <td><label for="role" class="select-label">Role:</label></td>
                <td>
                    <select id="role" name="role" class="select-label" required>
                        <option value="agent" class="select-label">Used Car Agent</option>
                        <option value="buyer" class="select-label">Buyer</option>
                        <option value="seller" class="select-label">Seller</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label style="font-size: 24px">Email:</label></td>
                <td><input type="text" name="email" style="font-size: 24px" required/></td> 
            </tr>
            <tr>
                <td><label style="font-size: 24px">Phone Number:</label></td>
                <td><input type="text" name="phone_num" style="font-size: 24px" required/></td> 
            </tr>
        </table>
        <br/>
        <button type="submit" style="font-size: 24px">Create New Account</button>
    </form>
    <br/>
    <hr/>
    <form action="admin_manage_user_acc.php" class="form-body">
        <button type="submit" value="Return" style="font-size: 24px">Return to accounts list</button>
    </form>
</body>
</html>

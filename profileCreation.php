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
    $input_username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Prepare and execute the SQL statement to check if the username exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $input_username); // "s" means the parameter is a string
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the username exists
    if ($result->num_rows == 0) {
        echo "Error: Username must match an existing account.";
    } else {
        // Fetch the user_id
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Prepare and bind the SQL statement for inserting the profile with user_id
        $stmt = $conn->prepare("INSERT INTO profile (user_id, first_name, last_name, email, phone_num) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $first_name, $last_name, $email, $phone_number); // "issss" means user_id is an integer, others are strings

        // Execute the statement
        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>
<html>
<head>
    <title>Profile Creation Page</title>
    <style>
        .form-body {
            text-align: center;
        }
        /* Style for invisible table */
        .invisible-table {
            border-collapse: collapse;
            width: 50%; /* Set a width for the table */
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
        <h1 style="text-align:center">Profile Creation</h1>
        <h2 style="text-align:center">Please fill in the following details</h2>
        <h3 style="text-align:center">All fields are mandatory</h3>
    </div>
    <form class="form-body" method="POST" action="">
        <h4>Note: Username should match with one of the data in the accounts list!</h4>
        <table class="invisible-table">
            <tr>
                <td><label style="font-size: 24px">Username:</label></td>
                <td><input type="text" name="username" style="font-size: 24px" required/></td>
            </tr>
            <tr>
                <td><label style="font-size: 24px">First Name:</label></td>
                <td><input type="text" name="first_name" style="font-size: 24px" required/></td>
            </tr>
            <tr>
                <td><label style="font-size: 24px">Last Name:</label></td>
                <td><input type="text" name="last_name" style="font-size: 24px" required/></td>
            </tr>
            <tr>
                <td><label style="font-size: 24px">Email:</label></td>
                <td><input type="email" name="email" style="font-size: 24px" required/></td>
            </tr>
            <tr>
                <td><label style="font-size: 24px">Phone Number:</label></td>
                <td><input type="text" name="phone_number" style="font-size: 24px" required/></td>
            </tr>
        </table>
        <br/>
        <button type="submit" style="font-size: 24px">Create Profile</button>
    </form>
    <br/>
    <form action="admin_manage_user_profiles.php" class="form-body">
        <button type="submit" value="Return" style="font-size: 24px">Return to profiles list</button>
    </form>
</body>
</html>

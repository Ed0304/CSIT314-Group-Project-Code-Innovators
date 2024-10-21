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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $input_username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $about = $_POST['about'];

    // Prepare and execute the SQL statement to check if the username exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "Error: Username must match an existing account.";
    } else {
        // Fetch the user_id
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Prepare the insert SQL statement
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            // Get the image data
            $image = $_FILES['profile_image']['tmp_name'];
            $image_data = file_get_contents($image); // Read the binary data of the image

            // Insert the profile data, including the image
            $stmt = $conn->prepare("INSERT INTO profile (user_id, first_name, last_name, gender, about, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
            $null = NULL;
            $stmt->bind_param("issssb", $user_id, $first_name, $last_name, $gender, $about, $null);
            
            // Use send_long_data() to send the image as a BLOB
            $stmt->send_long_data(5, $image_data);
        } else {
            // Insert the profile data without an image
            $stmt = $conn->prepare("INSERT INTO profile (user_id, first_name, last_name, gender, about, profile_image) VALUES (?, ?, ?, ?, ?, NULL)");
            $stmt->bind_param("issss", $user_id, $first_name, $last_name, $gender, $about);
        }

        // Execute the statement
        if ($stmt->execute()) {
            echo "New profile created successfully";
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
        .invisible-table {
            border-collapse: collapse;
            width: 50%;
            margin: auto;
        }
        .invisible-table td {
            border: none;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div style="background-color: red" class="header">
        <h1 style="text-align:center">Profile Creation</h1>
        <h2 style="text-align:center">Please fill in the following details</h2>
        <h3 style="text-align:center">All fields are mandatory</h3>
    </div>
    <form class="form-body" method="POST" action="" enctype="multipart/form-data">
        <h4>Note: Username should match with one of the data in the accounts list!</h4>
        <table class="invisible-table">
            <tr>
                <td><label style="font-size: 24px">Profile Picture:</label></td>
                <td><input type="file" name="profile_image" style="font-size: 24px"/></td>
            </tr>
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
                <td><label style="font-size: 24px">Gender:</label></td>
                <td>
                    <select name="gender" style="font-size: 24px" required>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label style="font-size: 24px">About:</label></td>
                <td><textarea name="about" style="font-size: 24px" rows="4" cols="50"></textarea></td>
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

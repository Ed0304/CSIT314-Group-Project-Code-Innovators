<?php
if (isset($_POST['createProfile'])) {
    header("Location: profileCreation.php");
    exit();
}
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
            font-size: 24px; /* Set font size for text in cells */
            text-align: center; /* Align text to the left */
        }
        .select-label{
            font-size: 24px;
        }
    </style>
</head>
<body>
    <h1 style="text-align:center">Manage user profiles here...</h1>
    <!-- Add your management functionality here -->
    <label for="role" class="select-label">Filer based on:</label>
    <select id="role" name="role" class="select-label">
        <option value="" class="select-label">All roles</option>
        <option value="agent" class="select-label">Used Car Agent</option>
        <option value="buyer" class="select-label">Buyer</option>
        <option value="seller" class="select-label">Seller</option>
    </select>
    <br/>
    <br/>
    <!-- Form for creating profile -->
    <form method="post" action="">
        <button type="submit" name="createProfile" class="select-label" id="createProfile">Create new user profile</button>
    </form>
    <br/>
    <br/>
    <table id="main-table">
        <tr>
            <th>Username</th>
            <th>Full Name</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <!-- Add rows here -->
    </table>
    <!-- Back to Dashboard button -->
    <form method="post" action="admin_dashboard.php" style="text-align:center">
        <br/>
        <input type="submit" value="Return" style="font-size: 24px">
    </form>
</body>
</html>

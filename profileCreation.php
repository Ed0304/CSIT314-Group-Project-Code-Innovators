<?php






?>
<html>
    <head>
        <title> Profile Creation Page </title>
    </head>
    <style>
        .form-body{
            text-align: center;
        }
    </style>
    <body>
        <div style="background-color: red" class="header">
        <h1 style="text-align:center"> Profile Creation </h1>
        <h2 style="text-align:center"> Please fill in the following details</h2>
        <h3 style="text-align:center"> All fields are mandatory </h3>
        </div>
        <form class ="form-body">
            <p>Note: username should match with one of the data in the accounts list!</p>
            <br/>
            <label style="font-size: 24px">username </label><input type="text" style="font-size: 24px" required/>
            <br/>
            <br/>
            <label style="font-size: 24px">real name </label><input type="text" style="font-size: 24px" required/>
            <br/>
            <button type = "submit" style="font-size: 24px">Create Profile</button>
        </form>
        <br/>
        <form action ="admin_manage_user_profiles.php" class ="form-body">
            <button type = "submit"  value="Return" style="font-size: 24px">Return to profiles list</button>
        </form>
    </body>
</html>
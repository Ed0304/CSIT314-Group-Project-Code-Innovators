<?php






?>
<html>
    <head>
        <title> Account Creation Page </title>
    </head>
    <style>
        .form-body{
            text-align: center;
        }
        .select-label{
            font-size: 24px;
        }
    </style>
    <body>
        <div style="background-color: red" class="header">
        <h1 style="text-align:center"> Account Creation </h1>
        <h2 style="text-align:center"> Please fill in the following details</h2>
        <h3 style="text-align:center"> All fields are mandatory </h3>
        </div>
        <form class ="form-body">
            <br/>
            <label style="font-size: 24px">username </label><input type="text" style="font-size: 24px" required/>
            <br/>
            <br/>
            <label style="font-size: 24px">password </label><input type="text" style="font-size: 24px" required/>
            <br/>
            <br/>
            <label for="role" class="select-label">Role:</label>
                <select id="role" name="role" class="select-label" required>
                    <option value="agent" class="select-label">Used Car Agent</option>
                    <option value="buyer" class="select-label">Buyer</option>
                    <option value="seller" class="select-label">Seller</option>
                </select>
            <br/>
            <br/>
            <button type = "submit" style="font-size: 24px">Create Account</button>
        </form>
        <br/>
        <hr/>
        <form action ="admin_manage_user_acc.php" class ="form-body">
            <button type = "submit"  value="Return" style="font-size: 24px">Return to accounts list</button>
        </form>
    </body>
</html>
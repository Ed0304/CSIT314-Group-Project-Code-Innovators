CSIT314-PROJECT 
Please extract all these files at C:/XAMMP/htdocs before running!
1. Connect to apache and mysql through XAMPP
2. type localhost, then type "login.php"
3. The login page should show up, try login and logout and play around with the buttons :)
4. Create account, then can create profile. You cannot create profile without an existing account
5. If you don't put any profile picture,the default image will be displayed.
Note: this project is incomplete (we will add more features soon)

Ensure that you have this setting on, other any images will NOT be displayed! (Edit the following property below on your C:\xampp\mysql\bin\my.ini)
[mysqld]
secure_file_priv = "C:/path/to/your/directory/"


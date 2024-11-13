<?php
// Database connection
require '../connectDatabase.php'; // Assuming connectDatabase.php is already set up

class UserAccount {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            $this->connectDatabase();
        }
    }

    // Connect to the database
    private function connectDatabase() {
        try {
            $this->pdo = new PDO('mysql:host=mariadb;dbname=csit314', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Get all roles
    public function getAllRoles() {
        $stmt = $this->pdo->prepare("SELECT role_name FROM role");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get the role_id based on the role_name
    public function getRoleId($role_name) {
        $stmt = $this->pdo->prepare("SELECT role_id FROM role WHERE role_name = ?");
        $stmt->bindParam(1, $role_name);
        $stmt->execute();
        $stmt->bindColumn(1, $role_id);
        $stmt->fetch(PDO::FETCH_ASSOC);
        return $role_id;
    }

    // Insert user data into users and profile tables
    public function createUserAccount($userAccount, $fileData) {
        $role_id = $this->getRoleId($userAccount->role);
        
        // Insert user into 'users' table
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, role_id, email, phone_num, status_id) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bindParam(1, $userAccount->username);
        $stmt->bindParam(2, $userAccount->password);  // Directly store the plain password
        $stmt->bindParam(3, $role_id);
        $stmt->bindParam(4, $userAccount->email);
        $stmt->bindParam(5, $userAccount->phone_num);
        $stmt->execute();
        $user_id = $this->pdo->lastInsertId(); // Get the inserted user's ID
    
        // Prepare profile image (if provided)
        $profile_image_data = null;
        if ($fileData['profile_image']['tmp_name']) {
            $profile_image_data = file_get_contents($fileData['profile_image']['tmp_name']);
        }
    
        // Insert profile data into 'profile' table
        $stmt = $this->pdo->prepare("INSERT INTO profile (user_id, first_name, last_name, about, gender, profile_image, status_id) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $userAccount->first_name);
        $stmt->bindParam(3, $userAccount->last_name);
        $stmt->bindParam(4, $userAccount->about);
        $stmt->bindParam(5, $userAccount->gender);
        $stmt->bindParam(6, $profile_image_data, PDO::PARAM_LOB);
        $stmt->execute();
    
        return true;
    }
}

class CreateUserAccountController {
    private $userAccount;

    public function __construct($userAccount) {
        $this->userAccount = $userAccount;
    }

    // Get all roles from the database
    public function getAllRoles() {
        return $this->userAccount->getAllRoles();
    }

    // Handle account creation by passing the form data to the entity layer
    public function createUserAccount($userAccount, $fileData) {
        return $this->userAccount->createUserAccount($userAccount, $fileData);
    }
}

class CreateUserAccountPage {
    private $message = "";
    private $roles = []; // Add an instance variable to store roles

    public function __construct($message = "") {
        $this->message = $message;
        
    }

    // Process the form submission
    public function processFormSubmission($controller) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $this->handleAccountCreation($_POST, $_FILES, $controller);
            $this->message = $message;
        }
        
        // Retrieve roles from the controller and store them in the instance variable
        $this->roles = $controller->getAllRoles();
    
        // Render the UI
        $this->createUserAccountUI();
    }

    // Handle the form submission logic and create the account
    public function handleAccountCreation($formData, $fileData, $controller) {
        // Ensure all required form data is set
        $formData = array_map('trim', $formData);  // Clean the form data by trimming whitespace
    
        if (empty($formData['username']) || empty($formData['password']) || empty($formData['role']) || 
            empty($formData['email']) || empty($formData['phone_num']) || empty($formData['first_name']) || 
            empty($formData['last_name']) || empty($formData['about']) || empty($formData['gender'])) {
            return "All fields are required except for Profile Image.";
        }
    
        // Create an object with the form data
        $userAccount = (object) [
            'username' => $formData['username'],
            'password' => $formData['password'],
            'role' => $formData['role'],
            'email' => $formData['email'],
            'phone_num' => $formData['phone_num'],
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'about' => $formData['about'],
            'gender' => $formData['gender'],
        ];
    
        // Call the controller's method to create the account
        $success = $controller->createUserAccount($userAccount, $fileData);
        
        // Return success or failure message
        return $success ? "Account created successfully!" : "Account creation failed.";
    }

    // Render the HTML form using stored roles
    public function createUserAccountUI() {
        ?>
        <html>
        <head>
            <title>Create Account</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f7f7f7;
                    padding: 50px;
                }

                h1, h2 {
                    text-align: center;
                    color: #333;
                }

                .form-container {
                    max-width: 500px;
                    margin: 0 auto;
                    background-color: #fff;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .form-group {
                    margin-bottom: 15px;
                }

                .form-group label {
                    font-size: 16px;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 5px;
                    display: block;
                }

                .form-group input, .form-group select, .form-group textarea {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    font-size: 14px;
                    box-sizing: border-box;
                }

                .form-group textarea {
                    height: 100px;
                }

                .form-group input[type="file"] {
                    border: none;
                }

                .form-group .gender-options input {
                    width: auto;
                    margin-right: 10px;
                }

                .btn {
                    background-color: #007BFF;
                    color: white;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 5px;
                    font-size: 16px;
                    cursor: pointer;
                    width: 100%;
                    margin-top: 20px;
                }

                .btn:hover {
                    background-color: #0056b3;
                }

                .btn-return {
                    background-color: #4CAF50;
                    margin-top: 10px;
                }

                .btn-return:hover {
                    background-color: #45a049;
                }

                .message {
                    text-align: center;
                    font-size: 18px;
                    color: red;
                    margin-bottom: 20px;
                }

                .form-header {
                    text-align: center;
                    margin-bottom: 30px;
                }
            </style>
        </head>
        <body>

            <div class="form-container">
                <div class="form-header">
                    <h1>Create Account</h1>
                    <h2>Please fill in the following details</h2>
                </div>

                <?php if ($this->message): ?>
                    <div class="message">
                        <?php echo htmlspecialchars($this->message); ?>
                    </div>
                <?php endif; ?>

                <form class="form-body" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" required />
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" required />
                    </div>
                    <div class="form-group">
                        <label>Role:</label>
                        <select name="role" required>
                            <?php foreach ($this->roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['role_name']); ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                            <?php endforeach; ?>
                        </select>

                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required />
                    </div>
                    <div class="form-group">
                        <label>Phone Number:</label>
                        <input type="text" name="phone_num" required />
                    </div>
                    <div class="form-group">
                        <label>First Name:</label>
                        <input type="text" name="first_name" required />
                    </div>
                    <div class="form-group">
                        <label>Last Name:</label>
                        <input type="text" name="last_name" required />
                    </div>
                    <div class="form-group">
                        <label>About:</label>
                        <textarea name="about" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Gender:</label>
                        <div class="gender-options">
                            <input type="radio" name="gender" value="Male" required /> Male
                            <input type="radio" name="gender" value="Female" required /> Female
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Profile Image:</label>
                        <input type="file" name="profile_image" />
                    </div>
                    <input type="submit" class="btn" value="Create Account" />
                </form>

                <!-- Return button -->
                <form method="post" action="admin_manage_user_acc.php">
                    <button type="submit" class="btn btn-return">Return</button>
                </form>
            </div>

        </body>
        </html>
        <?php
    }
}


// Instantiate objects and handle the form submission
$userAccount = new UserAccount();
$controller = new CreateUserAccountController($userAccount);
$page = new CreateUserAccountPage();

// Process the form submission
$page->processFormSubmission($controller);

?>

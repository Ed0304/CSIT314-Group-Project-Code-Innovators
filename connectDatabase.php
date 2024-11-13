<?php
class Database {
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $conn;

    // Constructor initializes the connection to the database and loads environment variables
    public function __construct() {
        $this->servername = getenv('DB_HOST') ?: "mariadb";
        $this->username = getenv('DB_USER') ?: "root";
        $this->password = getenv('DB_PASSWORD') ?: "";
        $this->dbname = getenv('DB_NAME') ?: "csit314";
        $this->conn = $this->connect();
    }

    // Private function to establish the connection
    private function connect() {
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Check connection and handle errors
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    // Return the active connection instance
    public function getConnection() {
        return $this->conn;
    }

    // Close the connection when done
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Initialize the Database class to establish a connection
$database = new Database();
$conn = $database->getConnection();
?>

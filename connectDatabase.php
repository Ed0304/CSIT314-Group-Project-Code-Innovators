<?php
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "csit314";
    private $conn;

    // Constructor initializes the connection to the database
    public function __construct() {
        $this->connect();
    }

    // Connect to the database using MySQLi
    private function connect() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Check connection and handle errors
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Return the database connection instance for use in other classes
    public function getConnection() {
        return $this->conn;
    }

    // Close the connection when done
    public function closeConnection() {
        $this->conn->close();
    }
}

// Initialize the Database class to establish a connection
$database = new Database();
$conn = $database->getConnection();
?>
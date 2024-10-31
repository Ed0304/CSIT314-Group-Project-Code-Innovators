<?php
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "csit314";
    private $conn;

    // Constructor initializes the connection to the database
    public function __construct() {
        $this->conn = $this->connect();
    }

    // Private function to establish the connection
    public function connect() {
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

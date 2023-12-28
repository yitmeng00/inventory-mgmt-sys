<?php

require_once __DIR__ . '../../vendor/autoload.php'; // Autoload Composer dependencies

use Dotenv\Dotenv;

class DBConnection
{
    private $serverName;
    private $userName;
    private $password;
    private $dbName;

    public function __construct()
    {
        // Load environment variables from .env file
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // Set class properties based on environment variables
        $this->serverName = $_ENV['DB_HOST'];
        $this->userName = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->dbName = $_ENV['DB_NAME'];
    }

    public function connect()
    {
        try {
            $conn = new mysqli($this->serverName, $this->userName, $this->password, $this->dbName);
            return $conn;
        } catch (Exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

?>

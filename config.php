<?php
// Step 1: Database Configuration
// Connect to MSSQL Server
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'atasodevadmin');
define('DB_PASSWORD', 'q9G"LCM.EtS8Z_Dr');
define('DB_DATABASE', 'internal_reward');

function connectDB() {
    try {
        $conn = new PDO("sqlsrv:Server=" . DB_SERVER . ";Database=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Error connecting to database: " . $e->getMessage());
    }
}


?>
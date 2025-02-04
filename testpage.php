<?php
 phpinfo();
function connectDB() {
    $dsn = "odbc:Driver={SQL Server};Server=localhost;Database=internal_reward;";
    $username = "atasodevadmin";
    $password = "q9G\"LCM.EtS8Z_Dr";

    try {
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
 ?>
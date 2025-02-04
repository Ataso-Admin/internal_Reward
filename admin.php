<?php
// Step 5: Admin User Management
require_once 'config.php';

function addUser($username, $password, $role) {
    if (!in_array($role, ['User', 'Manager', 'Admin'])) {
        throw new Exception("Invalid role.");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $conn = connectDB();
    $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
}
?>
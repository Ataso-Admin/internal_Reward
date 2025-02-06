<?php
require_once 'config.php';

try {
    $conn = connectDB();

    // Manager details to inject
    $username = 'manager2';
    $password = password_hash('securepassword123', PASSWORD_BCRYPT); // Replace with a secure password
    $role = 'Manager';

    // Check if the username already exists
    $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :username";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "Username '$username' already exists in the database.";
    } else {
        // Insert the new manager
        $insertQuery = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            echo "Manager '$username' has been successfully added.";
        } else {
            echo "Failed to add the manager.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

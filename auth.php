<?php
// Step 2: Authentication and Roles
session_start();

require_once 'config.php';


function authenticate($username, $password) {
    $conn = connectDB();
    $query = "SELECT id, username, role, password FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        return true;
    }
    return false;
}

function isManager() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Manager';
}

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Admin';
}

?>
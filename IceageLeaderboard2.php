<?php
// Step 1: Database Configuration
// Connect to MSSQL Server
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'atasodevadmin');
define('DB_PASSWORD', 'q9G"LCM.EtS8Z_Dr');
define('DB_DATABASE', 'internal_reward');

function connectDB() {
    $serverName = DB_SERVER;
    $connectionOptions = [
        "Database" => DB_DATABASE,
        "Uid" => DB_USERNAME,
        "PWD" => DB_PASSWORD
    ];

    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn === false) {
        die("Error connecting to database: " . print_r(sqlsrv_errors(), true));
    }
    return $conn;
}

// Step 2: Authentication and Roles
session_start();

function authenticate($username, $password) {
    $conn = connectDB();
    $query = "SELECT id, username, role, password FROM users WHERE username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
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

// Step 3: Leaderboard Display
function getLeaderboard() {
    $conn = connectDB();
    $query = "SELECT username, SUM(points) as total_points FROM points GROUP BY username ORDER BY total_points DESC";
    $stmt = sqlsrv_query($conn, $query);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $leaderboard = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $leaderboard[] = $row;
    }
    return $leaderboard;
}

// Step 4: Point Allocation by Managers
function allocatePoints($managerId, $recipientUsername, $points, $reason, $eventDate) {
    if ($points < 1 || $points > 10) {
        throw new Exception("Points must be between 1 and 10.");
    }

    $conn = connectDB();
    $query = "INSERT INTO points (manager_id, recipient_username, points, reason, event_date, allocation_time) VALUES (?, ?, ?, ?, ?, GETDATE())";
    $params = [$managerId, $recipientUsername, $points, $reason, $eventDate];
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Step 5: Admin User Management
function addUser($username, $password, $role) {
    if (!in_array($role, ['User', 'Manager', 'Admin'])) {
        throw new Exception("Invalid role.");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $conn = connectDB();
    $query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $params = [$username, $hashedPassword, $role];
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Step 6: Ice Age-Themed UI Integration
// Create HTML and CSS for an Ice Age-themed leaderboard display and forms.

?>

<!-- Ice Age Themed Leaderboard Page -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ice Age Leaderboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Ice Age Leaderboard</h1>
    </header>
    <main>
        <section id="leaderboard">
            <h2>Leaderboard</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Username</th>
                        <th>Total Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $leaderboard = getLeaderboard();
                    foreach ($leaderboard as $index => $user) {
                        echo "<tr>";
                        echo "<td>" . ($index + 1) . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['total_points']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>

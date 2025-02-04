<?php
require_once 'auth.php';
require_once 'admin.php';
require_once 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isManager()) {
    die("Access denied. Only managers can access this page.");
}

$conn = connectDB();
if (!$conn) {
    die("Database connection failed: " ) ;
}

$userQuery = "SELECT username FROM users WHERE role = 'User'";
$userStmt = $conn->prepare($userQuery);
$userStmt->execute();
$userList = $userStmt->fetchAll(PDO::FETCH_ASSOC);

$recordQuery = "SELECT recipient_username, points, reason, event_date, allocation_time FROM points";
$recordStmt = $conn->prepare($recordQuery);
$recordStmt->execute();
$records = $recordStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'allocate_points') {
        error_log("POST Data: " . print_r($_POST, true)); // Debugging

        try {
            // Validate all fields
            if (
                empty($_POST['recipient_username']) ||
                empty($_POST['points']) ||
                empty($_POST['reason']) ||
                empty($_POST['event_date'])
            ) {
                throw new Exception("All fields are required.");
            }

            // Insert data into the database
            $query = "
                INSERT INTO points (manager_id, recipient_username, points, reason, event_date, allocation_time)
                VALUES (:managerId, :recipientUsername, :points, :reason, :eventDate, :createdAt)
            ";
            $stmt = $conn->prepare($query);
            if (!isset($_SESSION['user']['id'])) {
                throw new Exception("User ID is not set in the session.");
            }
            $stmt->bindParam(':managerId', $_SESSION['user']['id'], PDO::PARAM_INT);
            $stmt->bindParam(':recipientUsername', $_POST['recipient_username'], PDO::PARAM_STR);
            $stmt->bindParam(':points', intval($_POST['points']), PDO::PARAM_INT);
            $stmt->bindParam(':reason', $_POST['reason'], PDO::PARAM_STR);
            $stmt->bindParam(':eventDate', $_POST['event_date'], PDO::PARAM_STR);
            $stmt->bindParam(':createdAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();

            // Fetch the updated records and refresh the content dynamically
            $recordQuery = "SELECT recipient_username, points, reason, event_date, allocation_time FROM points";
            $recordStmt = $conn->prepare($recordQuery);
            $recordStmt->execute();
            $records = $recordStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error occurred: " . $e->getMessage());
            $message = $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="styles2.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background-color: #f0f0f0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn {
            padding: 8px 15px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .allocation-row {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .allocation-row label {
            flex: 1 0 150px;
            font-size: 14px;
        }
        .allocation-row select,
        .allocation-row input,
        .allocation-row textarea {
            flex: 2 0 300px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .allocation-container {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <header>
        <h1>Manager Dashboard</h1>
    </header>
    <main>
        <?php if (isset($message)) { echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; } ?>

        <section id="allocate-points">
            <h2>Allocate Points</h2>
            <form method="POST">
                <input type="hidden" name="action" value="allocate_points">
                <div class="allocation-container">
                    <div class="allocation-row">
                        <label>Recipient Username:</label>
                        <select name="recipient_username" required>
                            <?php foreach ($userList as $user) { ?>
                                <option value="<?php echo htmlspecialchars($user['username']); ?>">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <label>Points (1-10):</label>
                        <input type="number" name="points" min="1" max="10" required>

                        <label>Reason:</label>
                        <textarea name="reason" required></textarea>

                        <label>Event Date:</label>
                        <input type="date" name="event_date" required>
                    </div>
                </div>
                <button type="submit" class="btn">Allocate Points</button>
            </form>
        </section>

        <section id="allocation-records">
            <h2>Allocation Records</h2>
            <table>
                <thead>
                    <tr>
                        <th>Recipient Username</th>
                        <th>Points</th>
                        <th>Reason</th>
                        <th>Event Date</th>
                        <th>Allocation Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['recipient_username']); ?></td>
                            <td><?php echo htmlspecialchars($record['points']); ?></td>
                            <td><?php echo htmlspecialchars($record['reason']); ?></td>
                            <td><?php echo htmlspecialchars($record['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($record['allocation_time']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>

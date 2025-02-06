<?php
require_once 'auth.php';
require_once 'admin.php';
require_once 'config.php';
//require_once 'super_admin.php';


// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isManager() && !isAdmin()) {
    die("Access denied. Only managers and admins can access this page.");
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
            $stmt->bindParam(':points', $_POST['points'], PDO::PARAM_INT);
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
    <title>Ataso & Innoverse Leaderboard</title>
    <link rel="stylesheet" href="styles2.css">
    <style>
                /* ===== Table / General Layout ===== */
                body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background: #f9f9f9;
        }
        header {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            color: white;
        }
        header h1 {
            margin: 0;
        }
        main {
            padding: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            background: #ffe;
            color: #333;
        }

        /* ===== Table Styling ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
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
            background-color: #fafafa;
        }
        tr:hover {
            background-color: #f1f1f1;
        }

         /* Container for all buttons */
         .button-container {
            display: flex;
            justify-content: space-between; /* Spread buttons across the width */
            align-items: center; /* Vertically align buttons */
            padding: 10px;
           /* background-color: #fff; /* Optional: Add background for the container */
           /*  border: 1px solid #ddd; /* Optional: Add a border */ 
        }

        /* Sub-container for left-aligned buttons */
        .left-buttons {
            display: flex;
            gap: 10px; /* Add space between buttons */
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
        .sortable {
            cursor: pointer;
        }

        /* ===== Allocation Form & Container ===== */
        .allocation-container {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #fff;
            margin-bottom: 20px;
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

        /* ===== Modal for Add User or Edit Record ===== */
        .modal {
            display: none;     /* Hidden by default */
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 8px;
            position: relative;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <h1>Ataso & Innoverse Leaderboard</h1>
    </header>
    <main>
        <?php if (!empty($message)) { echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; } ?>

                <!-- Display message if any -->
        <div class="button-container">
        <!-- Left buttons: Add New User and Add Manager -->
        <div class="left-buttons">
            <!-- Button to open the "Add User" modal -->
            <button id="addUserBtn" class="btn">Add New User</button>
            
            <!-- Display button only for Admin -->
                    <form action="Super_admin.php" method="GET">
                        <button type="submit" class="btn">Add Manager</button>
                    </form>
                        </div>

        <div>
            <!-- Display button only for Sign Out -->
            <form action="signout.php" method="POST">
                <button type="submit" class="btn">Sign Out</button>
            </form>
        </div>

        </div>
        
        <!-- Modal for adding a user -->
        <div id="addUserModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeAddUserModal">&times;</span>
                <h2>Add New User</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_user">
                    <label for="new_username">Username:</label>
                    <input type="text" id="new_username" name="new_username" required>
                    <br><br>
                    <label for="new_password">Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <br><br>
                    <button type="submit" class="btn">Add User</button>
                </form>
            </div>
        </div>

        <section id="allocate-points">
            <h2>Allocate Points</h2>
            <form method="POST" action="">
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
                        <th class="sortable" onclick="sortTable('event_date')">Event Date</th>
                        <th>Allocation Time</th>
                        <th>Action</th>
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
                            <td><button class="btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($record)); ?>)">Edit</button></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </main>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEditModal">×</span>
            <h2>Edit Record</h2>

            <form method="POST" action="">
                <input type="hidden" name="action" value="update_points">
                <!-- The ID of the record to update -->
                <input type="hidden" id="recordId" name="record_id">

                <div class="allocation-row">
                  <label for="editRecipient">Recipient Username:</label>
                    <input type="text" id="editRecipient" name="recipient_username" readonly>
                </div>

                <div class="allocation-row">
                    <label for="editPoints">Points (1-10):</label>
                    <input type="number" id="editPoints" name="points" min="1" max="10" required="">
                </div>

                <div class="allocation-row">
                    <label for="editReason">Reason:</label>
                    <textarea id="editReason" name="reason" rows="3" required=""></textarea>
                </div>

                <div class="allocation-row">
                    <label for="editEventDate">Event Date:</label>
                    <input type="date" id="editEventDate" name="event_date" required="">
                </div>

                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>
    </div>
    <script>
    // ============ Modal: Add User ============

    const addUserModal = document.getElementById('addUserModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const closeAddUserModal = document.getElementById('closeAddUserModal');

    addUserBtn.onclick = function () {
        addUserModal.style.display = 'block';
    };

    closeAddUserModal.onclick = function () {
        addUserModal.style.display = 'none';
    };

    window.onclick = function (event) {
        if (event.target == addUserModal) {
            addUserModal.style.display = 'none';
        }
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }
    };

    // ============ Modal: Edit Record ============
    const editModal = document.getElementById('editModal');
    const closeEditBtn = document.getElementById('closeEditModal');

    function openEditModal(record) {
            document.getElementById('recordId').value = record.id;
            document.getElementById('editRecipient').value = record.recipient_username;
            document.getElementById('editPoints').value = record.points;
            document.getElementById('editReason').value = record.reason;
            document.getElementById('editEventDate').value = record.event_date;
            document.getElementById('editModal').style.display = 'block';
        }
        document.getElementById('closeEditModal').onclick = function() {
            document.getElementById('editModal').style.display = 'none';
        };

    closeEditBtn.onclick = function () {
        editModal.style.display = 'none';
    };


        // ============ Sorting Table (Event Date) ============
        function sortTable(column) {
            const urlParams   = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort')  || 'event_date';
            const currentOrder= urlParams.get('order') || 'ASC';
            const newOrder    = (currentSort === column && currentOrder === 'ASC') ? 'DESC' : 'ASC';
            window.location.search = `sort=${column}&order=${newOrder}`;
        }

    </script>
</body>
</html>

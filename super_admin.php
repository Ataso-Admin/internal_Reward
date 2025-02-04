<?php
require_once 'auth.php';
require_once 'admin.php';

if (!isAdmin()) {
    header("Location: login.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        try {
            addUser($_POST['username'], $_POST['password'], $_POST['role']);
            $message = "User added successfully.";
        } catch (Exception $e) {
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
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
     .btn {
        padding: 8px 15px;
        color: #ffffff;
        background-color: #007bff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none; /* remove underline if using <a> */
        }

        .btn:hover {
        background-color: #0056b3;
        }
      body {
      margin: 0;
      padding: 0;
      background-color: #0f2439; 
      background-image: url("https://reward.ataso.io/leaderboard_background.png"); 
      background-repeat: no-repeat;
      background-position: center center;
      background-size: cover;
      background-attachment: fixed;
      font-family: Arial, sans-serif;
      color: #fff5ff;     
    }

    label {
      text-align: center;
      position: sticky;
      top: 0;                 /* Sticks at top of the container */
      z-index: 2;             /* Ensure header is above body rows */
      padding: 10px;
      font-weight: 500;
      text-align: left;
      text-shadow: 0 0 3px #00bcd4;
    }
    </style>

</head>
<body>
    <header>
        <h1>Super Admin Dashboard</h1>
    </header>
    
    <main>
        <?php if (isset($message)) { echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; } ?>
        <div>
        <form action="manager.php" method="GET">
        <i class="fa fa-arrow-left" ></i>   <button type="submit" class="btn">Back</button>
            </form>
        </div>

        <section id="add-user">
            <h2>Create New User</h2>
            <div class=thead>
                
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="User">User</option>
                    <option value="Manager">Manager</option>
                    <option value="Admin">Admin</option>
                </select>
                <button type="submit"  class="btn">Add User</button>
            </form>
        </section>

        </div>
    </main>
</body>
</html>
<?php 
require_once 'leaderboard2.php';
$leaderboard = getLeaderboard();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ataso & Innoverse Leaderboard</title>

    <!-- Optionally include Font Awesome for arrow icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Inlined CSS -->
 <style>
    /* ===== Global Styles ===== */
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

    header {
      text-align: center;
      padding: 20px;
      background: linear-gradient(#0d3349, #102e42);
      box-shadow: 0 0 8px rgba(0, 255, 255, 0.4);
    }

    header h1 {
      margin: 0;
      font-size: 2rem;
      color: #00f0ff;
      text-shadow: 0 0 5px #00f0ff;
    }

    main {
      width: 90%;
      max-width: 1000px;
      margin: 30px auto;
    }

    /* ===== Leaderboard Section ===== */
	#leaderboard {
	  margin: 20px auto; /* "auto" on the left and right centers horizontally */
	  width: 50%;
	  background: #0a1a2a;
	  padding: 20px;
	  border-radius: 8px;
	  box-shadow: 0 0 10px #00f0ff;
	}

    #leaderboard h2 {
      text-align: center;
      margin: 0 0 20px 0;
      font-size: 1.8rem;
      text-shadow: 0 0 5px #00f0ff;
    }

    /* ===== Scrolling Container for Table ===== */
    .table-container {
      /* Control the table’s visible height here */
      max-height: 500px;
      overflow-y: auto;
      /* Optionally add a border or shadow around the container 
         to show the scroll area. Up to you. */
    }

    /* ===== Table Styling ===== */
    table {
      width: 100%;                 /* Use full container width */
      border-collapse: separate;   /* or collapse if you prefer */
      border-spacing: 0;
      margin: 0 auto;
      background-color: rgba(15, 30, 47, 0.5);
      color: #ffffff;
      box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
    }

    thead {
      background: #102e42;
    }

    /* Sticky header cells */
    thead th {
		text-align: center;
      position: sticky;
      top: 0;        /* Sticks to the top of the container */
      z-index: 2;    /* Ensure it’s above the body rows */
      padding: 12px;
      font-weight: 600;
      text-align: left;
      text-shadow: 0 0 3px #00bcd4;
      border-bottom: 2px solid #00bcd4;
    }

    tbody td {
		text-align: center;
      padding: 12px;
      border-bottom: 1px solid #1b3b52;
      /* For large columns, auto will let them size by content */
    }

    /* Hover effect for rows */
    tbody tr:hover {
      background: #18344c;
    }

    /* Rank Column Emphasis */
    tbody td:first-child {
      font-weight: bold;
      color: #00f0ff;
      text-shadow: 0 0 3px #00f0ff;
    }

    /* Optional: Scrollbar customization (Chrome/Edge/Opera) */
    .table-container::-webkit-scrollbar {
      width: 8px;
    }
    .table-container::-webkit-scrollbar-track {
      background: #0f1e2f;
    }
    .table-container::-webkit-scrollbar-thumb {
      background: #00bcd4;
      border-radius: 4px;
    }
  </style>
</head>
<body>
    <header>
        <h1>Ataso & Innoverse Leaderboard</h1>
    </header>
    <main>
        <section id="leaderboard">
            <h2>Leaderboard</h2>
            <table>
                <thead>
                    <tr>
                    <th>Rank</th>
                    <th>User Name</th>
                    <th>Total Points</th>
                    <th>Rank Movement</th>
					</tr>
                </thead>
                <tbody>
                <?php foreach ($leaderboard as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['rank']) ?></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= htmlspecialchars($row['total_points']) ?></td>
          <td><?= $row['rank_icon'] /* already sanitized as icons */ ?></td>
        </tr>
      <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>

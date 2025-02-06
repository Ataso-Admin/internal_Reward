<?php 
require_once 'leaderboard.php';
$leaderboard = getLeaderboard();

$selectedSeason = $_GET['season'] ?? 'current'; // '1','2','3','4' or 'current'
$selectedYear   = $_GET['year']   ?? date('Y');

// Decide how to call getLeaderboard()
if ($selectedSeason === 'current') {
    // no arguments => defaults to current
    $leaderboard = getLeaderboard();
} else {
    $season = (int)$selectedSeason;
    $year   = (int)$selectedYear;
    $leaderboard = getLeaderboard($season, $year);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ataso & Innoverse Leaderboard</title>
  <!-- Optionally include Font Awesome for arrow icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    /* ===== Buttons ===== */
    .btn {
      padding: 8px 15px;
      color: #ffffff;
      background-color: #007bff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
    }
    .btn:hover {
      background-color: #0056b3;
    }
    
    /* ===== Global Styles ===== */
    body {
      margin: 0;
      padding: 0;
      background-color: #0f2439; 
      background-image: url("https://reward.ataso.io/resources/ZB wallapper landscape 2048 x 1152.png"); 
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
      margin: 20px auto; /* Center horizontally */
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
      max-height: 500px;    /* Adjust as needed */
      overflow-y: auto;     /* Adds vertical scrollbar within the table */
      margin: 0 auto;
      box-shadow: inset 0 0 10px rgba(0, 255, 255, 0.2);
    }

    /* ===== Table Styling ===== */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin: 0; 
      background-color: rgba(15, 30, 47, 0.5);
      color: #ffffff;
    }

    /* Sticky Header */
    thead {
      background: #102e42;
    }


    thead th {
      position: sticky;
      top: 0; /* Sticks at top of the container */
      z-index: 2;
      padding: 12px;
      font-weight: 600;
      text-align: left; /* or center, up to you */
      text-shadow: 0 0 3px #00bcd4;
      border-bottom: 2px solid #00bcd4;

      /* Solid background color */
      background-color: #102e42; /* Remove any rgba() or transparency */
    }

    /* Table Body Rows */
    tbody td {
      text-align: center;
      padding: 12px;
      border-bottom: 1px solid #1b3b52;
    }
    tbody tr:hover {
      background: #18344c;
    }

    /* Rank Column Emphasis */
    tbody td:first-child {
        font-weight: bold;
 /*       color: #00f0ff;  /* default neon color */
        text-shadow: 0 0 3px #00f0ff;
      }

      /* Rank 1 (Gold) */
      .rank-1 {
        color: gold;
        font-size: 2.8rem;  /* bigger text */
        text-shadow: 0 0 5px gold;
      }

      /* Rank 2 (Silver) */
      .rank-2 {
        color: silver;
        font-size: 2.3rem;
        text-shadow: 0 0 5px silver;
      }

      /* Rank 3 (Bronze) */
      .rank-3 {
        color: #cd7f32; /* bronze-ish color */
        font-size: 1.8rem;
        text-shadow: 0 0 5px #cd7f32;
      }

        /* Rank 4 and below (neon) */
        .rank-4 {
        color: #00f0ff; /* neon color */
        font-size: 1.25rem;
        text-shadow: 0 0 5px #00f0ff;
      }

    /* ===== Scrollbar customization (Chrome/Edge/Opera) ===== */
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
  <!-- 4) Put the Season Picker Form ABOVE the table -->
  <form method="GET" action="">
        <label for="season">Season:</label>
        <select name="season" id="season">
          <option value="current">Current Season</option>
          <option value="1">Season 1 (Jan-Mar)</option>
          <option value="2">Season 2 (Apr-Jun)</option>
          <option value="3">Season 3 (Jul-Sep)</option>
          <option value="4">Season 4 (Oct-Dec)</option>
        </select>

        <label for="year">Year:</label>
        <input type="number" name="year" value="<?php echo htmlspecialchars($selectedYear); ?>">

        <button type="submit">View</button>
      </form>

      <!-- Wrap a SINGLE table (thead + tbody) in the .table-container -->
      <div class="table-container">
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
              <?php
                // Decide the class based on rank
                $rankClass = '';
                if ($row['rank'] == 1) {
                  $rankClass = 'rank-1';
                } elseif ($row['rank'] == 2) {
                  $rankClass = 'rank-2';
                } elseif ($row['rank'] == 3) {
                  $rankClass = 'rank-3';
                } elseif ($row['rank'] > 3) {
                  $rankClass = 'rank-4';
                }
              ?>
              <tr>
                <!-- Apply the class to the rank cell -->
                <td class="<?= $rankClass ?>">
                  <?= htmlspecialchars($row['rank']) ?>
                </td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['total_points']) ?></td>
                <td><?= $row['rank_icon'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div><!-- end .table-container -->

    </section>
  </main>
</body>
</html>

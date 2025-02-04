<?php 
require_once 'leaderboard.php';
$leaderboard = getLeaderboard();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ataso & Innoverse Leaderboard</title>

    <!-- Inlined CSS -->
 <style>
    /* Basic Reset */
    html, body {
      margin: 0;
      padding: 0;
      overflow-x: hidden; /* Avoid horizontal scroll for parallax */
    }

    /* Container for layers */
    .parallax-container {
      position: relative;
      height: 100vh; /* Full viewport height */
      perspective: 1px; /* Allows parallax effect when scrolling */
      overflow-x: hidden;
      overflow-y: auto;
    }

    /* Each layer can be scrolled at different speeds */
    .parallax-layer {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      min-height: 100vh;
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
      transform-origin: center;
    }

    /* Example: neon grid "floor" */
    .layer-floor {
      transform: translateZ(-1px) scale(2); /* Moves slower when scrolling */
      background-image: url('YOUR_NEON_GRID_FLOOR.png');
    }

    /* Example: city skyline silhouette */
    .layer-city {
      transform: translateZ(-0.5px) scale(1.5);
      background-image: url('YOUR_CITY_SKYLINE.png');
      /* A subtle animation to “scroll” horizontally, if desired */
      animation: cityScroll 30s linear infinite;
    }

    /* Main content layer (where your leaderboard would live) */
    .layer-content {
      position: relative; /* Don’t transform this layer, so it scrolls normally */
      background: transparent;
      min-height: 100vh;
      z-index: 9999; /* On top */
    }

    /* Example animation to move the city horizontally */
    @keyframes cityScroll {
      0%   { background-position: 0 center; }
      100% { background-position: 2000px center; }
    }
  </style>
</head>
<body>
  <div class="parallax-container">
    <div class="parallax-layer layer-floor"></div>
    <div class="parallax-layer layer-city"></div>

    <!-- Your main content (leaderboard, etc.) goes here -->
    <div class="parallax-layer layer-content">
      
    <header>
        <h1 style="color: #fff; text-align: center;">Ataso & Innoverse Leaderboard</h1>
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
                    // Loop through leaderboard data
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

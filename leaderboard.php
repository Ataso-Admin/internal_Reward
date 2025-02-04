<?php
require_once 'config.php';


/**
 * Return 1..4 based on the current month (1..12).
 * e.g. months 1..3 => Q1 => returns 1
 *      months 4..6 => Q2 => returns 2, etc.
 */
function getCurrentSeason(): int
{
    $month = (int) date('n'); // 1..12
    return (int)(($month - 1) / 3) + 1; // Q1=1, Q2=2, Q3=3, Q4=4
}

/**
 * Given a season (1..4) and year (e.g., 2025),
 * returns [startDate, endDate] in 'YYYY-MM-DD' format.
 * Q1 => Jan 1 - Mar 31, Q2 => Apr 1 - Jun 30, etc.
 */
function getSeasonDateRange(int $season, int $year): array
{
    switch ($season) {
        case 1:
            return ["$year-01-01", "$year-03-31"];
        case 2:
            return ["$year-04-01", "$year-06-30"];
        case 3:
            return ["$year-07-01", "$year-09-30"];
        default:
            return ["$year-10-01", "$year-12-31"];
    }
}



/**
 * getLeaderboard()
 * Calculates total_points per user from the points table,
 * updates users.current_points, sorts by total_points DESC,
 * then updates ranks (previous_rank/current_rank),
 * and returns the final $leaderboard array including rank change info.
 */
function getLeaderboard(?int $season = null, ?int $year = null): array
{
    $conn = connectDB();

    // 1) Determine 3-month window (start & end in 'YYYY-MM-DD' format)
    if ($season === null || $year === null) {
        $season = getCurrentSeason();
        $year   = (int) date('Y');
    }
    list($startDate, $endDate) = getSeasonDateRange($season, $year);

    // Fix start/end times to entire days
    $startStamp = strtotime($startDate . ' 00:00:00');
    $endStamp   = strtotime($endDate   . ' 23:59:59');

    // 2) Fetch all rows from users + points (no date filter in SQL)
    $sql = "
        SELECT 
            u.id AS user_id,
            u.username,
            p.points,
            p.event_date
        FROM users u
        LEFT JOIN points p ON p.recipient_username = u.username
        WHERE u.role NOT IN ('Admin', 'SuperAdmin')
        ORDER BY u.id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) Build an array: userId => sumOfPointsInDateRange
    $totals = [];
    foreach ($allRows as $row) {
        $userId   = $row['user_id'];
        $username = $row['username'];

        // Initialize if not exist
        if (!isset($totals[$userId])) {
            $totals[$userId] = [
                'user_id'  => $userId,     // store userId
                'username' => $username,
                'sum'      => 0
            ];
        }

        // If the row has points AND an event date
        if (!empty($row['points']) && !empty($row['event_date'])) {
            // 4) Normalize & parse "Aug 26 2024 5:08PM"
            $rawDate = trim($row['event_date']);
            // If you suspect extra spaces, you can unify them:
            // $rawDate = preg_replace('/\s+/', ' ', $rawDate);

            // This pattern typically works: "M j Y g:ia" => e.g. "Aug 26 2024 5:08PM"
            // If you have two spaces or a slightly different format, adjust as needed
            $format = 'Y-m-d';
            $dt = DateTime::createFromFormat($format, $rawDate);

            if ($dt !== false) {
                $phpTime = $dt->getTimestamp();
                // 5) Check if it’s between $startStamp and $endStamp
                if ($phpTime >= $startStamp && $phpTime <= $endStamp) {
                    $totals[$userId]['sum'] += (int) $row['points'];
                }
            } 
            // else { you could log or handle parse failures }
        }
    }

    // 6) Sort $totals by descending sum
    usort($totals, function($a, $b) {
        return $b['sum'] <=> $a['sum'];
    });

    // 7) Update each user's current_points and build final leaderboard
    $leaderboard = [];
    foreach ($totals as $index => $item) {
        $rank        = $index + 1;
        $userId      = $item['user_id'];
        $username    = $item['username'];
        $points      = $item['sum'];

        // Update DB
        $updSql = "UPDATE users SET current_points = :points WHERE id = :uid";
        $updStmt = $conn->prepare($updSql);
        $updStmt->execute([
            ':points' => $points,
            ':uid'    => $userId
        ]);

        // Rank movement (arrow icon)
        $changeIcon = updateUserRank($conn, $userId, $rank);

        $leaderboard[] = [
            'id'           => $userId,
            'username'     => $username,
            'total_points' => $points,
            'rank'         => $rank,
            'rank_icon'    => $changeIcon
        ];
    }

    return $leaderboard;
}

/**

*/

/**
 * updateUserRank()
 *  - Fetches the user's old current_rank from `users`.
 *  - Sets previous_rank = current_rank, current_rank = $newRank.
 *  - Returns an HTML icon string (up/down/minus) based on rank movement.
 */
function updateUserRank(PDO $conn, int $userId, int $newRank): string
{
    // 1) Get the old rank and last movement from the DB
    $sql = "SELECT current_rank, last_rank_movement FROM users WHERE id = :userId";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':userId' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // User not found; no arrow or handle as needed
        return '';
    }

    $oldRank         = (int) $row['current_rank'];
    $oldMovement     = $row['last_rank_movement'] ?? 'none';

    // 2) Determine rank delta: oldRank - newRank
    //    +ve => user rank improved (2 -> 1)
    //    -ve => user rank got worse (1 -> 2)
    //    0   => no movement
    $rankDelta = $oldRank - $newRank;

    // We'll store movement as text: 'up', 'down', or 'none'
    // Then convert that text into an actual arrow icon
    $newMovement = 'none';

    if ($rankDelta > 0) {
        $newMovement = 'up';
    } elseif ($rankDelta < 0) {
        $newMovement = 'down';
    } else {
        // No movement => reuse the old movement
        if (strcasecmp($oldMovement, 'oldmovement') === 0) { // Case-insensitive comparison
            $newMovement = '-'; // Show as '-'
        } else {
            $newMovement = $oldMovement; // Reuse the old movement if not 'oldmovement'
        }
    }
    
    // Convert the $newMovement string to an arrow icon
    $arrow = movementToArrow($newMovement);

    // 3) Update ranks only if the rank changed
    //    But always update last_rank_movement
    if ($rankDelta !== 0) {
        // rank changed => update previous_rank/current_rank
        $sqlUpdate = "
            UPDATE users
            SET 
                previous_rank       = :oldRank,
                current_rank        = :newRank,
                last_rank_movement  = :move
            WHERE id = :userId
        ";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':oldRank' => $oldRank,
            ':newRank' => $newRank,
            ':move'    => $newMovement,
            ':userId'  => $userId
        ]);
    } else {
        // rankDelta = 0 => update only the last_rank_movement
        // because the user didn't move, but we want to keep the old arrow
        $sqlUpdate = "
            UPDATE users
            SET last_rank_movement = :move
            WHERE id = :userId
        ";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':move'   => $newMovement,
            ':userId' => $userId
        ]);
    }

    // 4) Return the arrow (based on newMovement)
    return $arrow;
}

/**
 * movementToArrow()
 *  Converts a movement string ('up', 'down', 'none') into HTML <i> icons.
 */
function movementToArrow(string $movement): string
{
    switch ($movement) {
        case 'up':
            return '<i class="fa fa-arrow-up" style="color: lime;"></i>';
        case 'down':
            return '<i class="fa fa-arrow-down" style="color: #ff3333;"></i>';
        default:
            // 'none' or any other => no change => reuse last
            // If you prefer a minus icon, you can do so:
             return '<i class="fa fa-minus" style="color: #ccc;"></i>';
          //  return '';
    }
}



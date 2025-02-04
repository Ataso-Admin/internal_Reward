<?php
require_once 'config.php';

/**
 * getLeaderboard()
 * Calculates total_points per user from the points table,
 * updates users.current_points, sorts by total_points DESC,
 * then updates ranks (previous_rank/current_rank),
 * and returns the final $leaderboard array including rank change info.
 */
function getLeaderboard(): array
{
    $conn = connectDB();

    // 1) Build an array of [username, total_points] by summing from `points`
    $sql = "
        SELECT 
            u.id,
            u.username,
            COALESCE(SUM(p.points), 0) AS total_points
        FROM users u
        LEFT JOIN points p ON p.recipient_username = u.username
        GROUP BY u.id, u.username
        ORDER BY total_points DESC;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) Update each user's current_points in the `users` table
    foreach ($data as $row) {
        $userId      = $row['id'];
        $totalPoints = (int) $row['total_points'];

        $updSql = "
            UPDATE users
            SET current_points = :points
            WHERE id = :userId
        ";
        $updStmt = $conn->prepare($updSql);
        $updStmt->bindParam(':points', $totalPoints, PDO::PARAM_INT);
        $updStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $updStmt->execute();
    }

    // 3) Now that we have a sorted list, assign rank and update rank columns
    //    We'll also build the final $leaderboard array with "rank_change"
    $leaderboard = [];
    foreach ($data as $index => $row) {
        $newRank     = $index + 1;   // e.g. 1 for first place
        $userId      = $row['id'];
        $username    = $row['username'];
        $totalPoints = (int) $row['total_points'];

        // Calculate the arrow icon based on rank movement
        $changeIcon = updateUserRank($conn, $userId, $newRank);

        $leaderboard[] = [
            'id'           => $userId,
            'username'     => $username,
            'total_points' => $totalPoints,
            'rank'         => $newRank,
            'rank_icon'    => $changeIcon,  // up/down/no-change icon
        ];
    }

    return $leaderboard;
}

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
        $newMovement = $oldMovement;
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
            // return '<i class="fa fa-minus" style="color: #ccc;"></i>';
            return '';
    }
}


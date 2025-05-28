<?php
session_start();
require '../db/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $user_query->fetch_assoc();

// Check if student is currently using a PC
$current_pc_query = $conn->query("SELECT pcs.pc_number, rooms.room_number, usage_logs.login_time 
                                  FROM usage_logs 
                                  JOIN pcs ON usage_logs.pc_id = pcs.pc_id 
                                  JOIN rooms ON pcs.room_id = rooms.room_id 
                                  WHERE usage_logs.user_id = $user_id AND usage_logs.logout_time IS NULL");
$current_pc = $current_pc_query->fetch_assoc();

// Get recent announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 5");

// Get total active PCs
$active_pcs = $conn->query("SELECT COUNT(*) as count FROM pcs WHERE is_active = 1")->fetch_assoc()['count'];

// Get available PCs per room
$room_stats = $conn->query("SELECT r.room_number, 
                                   COUNT(p.pc_id) as total_pcs,
                                   SUM(CASE WHEN p.is_active = 1 AND p.used_by IS NULL THEN 1 ELSE 0 END) as available_pcs,
                                   SUM(CASE WHEN p.used_by IS NOT NULL THEN 1 ELSE 0 END) as occupied_pcs
                            FROM rooms r 
                            LEFT JOIN pcs p ON r.room_id = p.room_id 
                            GROUP BY r.room_id, r.room_number 
                            ORDER BY r.room_number");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
</head>

<body>
    <h1>Welcome, <?= htmlspecialchars($user['full_name']) ?></h1>
    <p><strong>Course:</strong> <?= htmlspecialchars($user['course']) ?> |
        <strong>Year:</strong> <?= htmlspecialchars($user['year']) ?> |
        <strong>Section:</strong> <?= htmlspecialchars($user['section']) ?>
    </p>

    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="select_pc.php">Select PC</a></li>
            <li><a href="report_issue.php">Report Issue</a></li>
            <li><a href="announcements.php">Announcements</a></li>
            <li><a href="../login.php">Logout</a></li>
        </ul>
    </nav>

    <?php if ($current_pc): ?>
        <div style="background: #e8f5e8; padding: 10px; border: 1px solid #4CAF50; margin: 10px 0;">
            <h3>Currently Using PC</h3>
            <p><strong>Room:</strong> <?= htmlspecialchars($current_pc['room_number']) ?> |
                <strong>PC:</strong> <?= htmlspecialchars($current_pc['pc_number']) ?> |
                <strong>Login Time:</strong> <?= htmlspecialchars($current_pc['login_time']) ?>
            </p>
            <a href="logout_pc.php" onclick="return confirm('Are you sure you want to logout from the PC?')">Logout from PC</a>
        </div>
    <?php endif; ?>

    <h2>Computer Lab Status</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Room</th>
                <th>Total PCs</th>
                <th>Available</th>
                <th>Occupied</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($room = $room_stats->fetch_assoc()): ?>
                <tr>
                    <td>Room <?= htmlspecialchars($room['room_number']) ?></td>
                    <td><?= $room['total_pcs'] ?></td>
                    <td><?= $room['available_pcs'] ?></td>
                    <td><?= $room['occupied_pcs'] ?></td>
                    <td>
                        <?php if ($room['available_pcs'] > 0 && !$current_pc): ?>
                            <a href="select_pc.php?room=<?= $room['room_number'] ?>">Select PC</a>
                        <?php elseif ($current_pc): ?>
                            Currently Using PC
                        <?php else: ?>
                            No Available PCs
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Recent Announcements</h2>
    <?php if ($announcements->num_rows > 0): ?>
        <ul>
            <?php while ($announcement = $announcements->fetch_assoc()): ?>
                <li>
                    <strong><?= htmlspecialchars($announcement['title']) ?></strong>
                    <em>(<?= $announcement['date_posted'] ?>)</em><br>
                    <?= nl2br(htmlspecialchars($announcement['message'])) ?>
                </li>
            <?php endwhile; ?>
        </ul>
        <a href="announcements.php">View All Announcements</a>
    <?php else: ?>
        <p>No announcements available.</p>
    <?php endif; ?>

</body>

</html>
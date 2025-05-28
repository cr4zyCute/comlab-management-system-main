<?php
session_start();
require '../db/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

// Get all announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Announcements - Student</title>
</head>
<body>
<h1>Announcements</h1>

<nav>
<ul>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="select_pc.php">Select PC</a></li>
    <li><a href="report_issue.php">Report Issue</a></li>
    <li><a href="announcements.php">Announcements</a></li>
    <li><a href="../login.php">Logout</a></li>
</ul>
</nav>

<h2>All Announcements</h2>
<?php if ($announcements->num_rows > 0): ?>
    <?php while($announcement = $announcements->fetch_assoc()): ?>
    <div style="border: 1px solid #ccc; padding: 15px; margin: 10px 0; background: #f9f9f9;">
        <h3><?= htmlspecialchars($announcement['title']) ?></h3>
        <p style="color: #666; font-size: 0.9em;">
            Posted on: <?= date('F j, Y g:i A', strtotime($announcement['date_posted'])) ?>
        </p>
        <div style="margin-top: 10px;">
            <?= nl2br(htmlspecialchars($announcement['message'])) ?>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
<p>No announcements available at this time.</p>
<?php endif; ?>

<a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
<?php
session_start();
require '../db/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if student is already using a PC
$current_pc_check = $conn->query("SELECT * FROM usage_logs WHERE user_id = $user_id AND logout_time IS NULL");
if ($current_pc_check->num_rows > 0) {
    header("Location: dashboard.php");
    exit;
}

// Handle PC selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_pc'])) {
    $pc_id = intval($_POST['pc_id']);
    
    // Verify PC is available
    $pc_check = $conn->query("SELECT * FROM pcs WHERE pc_id = $pc_id AND is_active = 1 AND used_by IS NULL");
    if ($pc_check->num_rows > 0) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update PC to be used by student
            $stmt = $conn->prepare("UPDATE pcs SET used_by = ? WHERE pc_id = ?");
            $stmt->bind_param("ii", $user_id, $pc_id);
            $stmt->execute();
            
            // Log the usage
            $stmt2 = $conn->prepare("INSERT INTO usage_logs (user_id, pc_id, login_time) VALUES (?, ?, NOW())");
            $stmt2->bind_param("ii", $user_id, $pc_id);
            $stmt2->execute();
            
            $conn->commit();
            header("Location: dashboard.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to select PC. Please try again.";
        }
    } else {
        $error = "PC is no longer available.";
    }
}

// Get room filter
$selected_room = isset($_GET['room']) ? $_GET['room'] : '';

// Get available PCs
$where_clause = "WHERE pcs.is_active = 1 AND pcs.used_by IS NULL";
if ($selected_room) {
    $where_clause .= " AND rooms.room_number = '" . $conn->real_escape_string($selected_room) . "'";
}

$pcs_query = "SELECT pcs.pc_id, pcs.pc_number, rooms.room_number, rooms.room_id
              FROM pcs 
              JOIN rooms ON pcs.room_id = rooms.room_id 
              $where_clause
              ORDER BY rooms.room_number, pcs.pc_number";
$available_pcs = $conn->query($pcs_query);

// Get all rooms for filter
$rooms = $conn->query("SELECT DISTINCT room_number FROM rooms ORDER BY room_number");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Select PC - Student</title>
</head>
<body>
<h1>Select a PC</h1>

<nav>
<ul>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="select_pc.php">Select PC</a></li>
    <li><a href="report_issue.php">Report Issue</a></li>
    <li><a href="announcements.php">Announcements</a></li>
    <li><a href="../login.php">Logout</a></li>
</ul>
</nav>

<?php if (isset($error)): ?>
<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; margin: 10px 0; color: #c62828;">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<h2>Filter by Room</h2>
<form method="get">
    <select name="room" onchange="this.form.submit()">
        <option value="">All Rooms</option>
        <?php while($room = $rooms->fetch_assoc()): ?>
            <option value="<?= $room['room_number'] ?>" <?= $selected_room == $room['room_number'] ? 'selected' : '' ?>>
                Room <?= htmlspecialchars($room['room_number']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<h2>Available PCs</h2>
<?php if ($available_pcs->num_rows > 0): ?>
<form method="post">
    <table border="1" cellpadding="5" cellspacing="0">
    <thead>
    <tr>
        <th>Select</th>
        <th>Room</th>
        <th>PC Number</th>
    </tr>
    </thead>
    <tbody>
    <?php while($pc = $available_pcs->fetch_assoc()): ?>
    <tr>
        <td>
            <input type="radio" name="pc_id" value="<?= $pc['pc_id'] ?>" required>
        </td>
        <td>Room <?= htmlspecialchars($pc['room_number']) ?></td>
        <td>PC <?= htmlspecialchars($pc['pc_number']) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
    </table>
    <br>
    <button type="submit" name="select_pc" onclick="return confirm('Are you sure you want to use this PC?')">
        Use Selected PC
    </button>
</form>
<?php else: ?>
<p>No PCs are currently available. Please try again later.</p>
<?php endif; ?>

<a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
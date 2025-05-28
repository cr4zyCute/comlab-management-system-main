<?php
session_start();
require '../db/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Add equipment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_equipment'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $room_id = $_POST['room_id'];

    $stmt = $conn->prepare("INSERT INTO equipment (name, description, room_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $desc, $room_id);
    $stmt->execute();
    header("Location: manage_equipment.php");
    exit;
}

// Delete equipment
if (isset($_POST['delete_equipment'])) {
    $id = $_POST['equipment_id'];
    $stmt = $conn->prepare("DELETE FROM equipment WHERE equipment_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_equipment.php");
    exit;
}

$equipments = $conn->query("SELECT equipment.*, rooms.room_number FROM equipment LEFT JOIN rooms ON equipment.room_id = rooms.room_id ORDER BY equipment.equipment_id DESC");
$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Equipment - Admin</title>
</head>
<body>
<h1>Manage Equipment</h1>
  <ul>
    <li><a href="dashboard.php" class="active">Dashboard</a></li>
    <li><a href="active_pcs.php">Active PCs</a></li>
    <li><a href="manage_users.php">Manage Users</a></li>
    <li><a href="manage_rooms.php">Manage Rooms</a></li>
    <li><a href="maintenance_reports.php">Maintenance Reports</a></li>
    <li><a href="announcements.php">Announcements</a></li>
    <li><a href="../login.php">Logout</a></li>
  </ul>
</nav>
<h2>Add Equipment</h2>
<form method="post">
    <label>Name: <input type="text" name="name" required></label><br>
    <label>Description: <textarea name="description" required></textarea></label><br>
    <label>Room:
        <select name="room_id" required>
            <?php while($room = $rooms->fetch_assoc()): ?>
                <option value="<?= $room['room_id'] ?>"><?= htmlspecialchars($room['room_number']) ?></option>
            <?php endwhile; ?>
        </select>
    </label><br>
    <button type="submit" name="add_equipment">Add Equipment</button>
</form>

<h2>Equipment List</h2>
<table border="1" cellpadding="5" cellspacing="0">
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Description</th>
    <th>Room</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php while($eq = $equipments->fetch_assoc()): ?>
<tr>
    <td><?= $eq['equipment_id'] ?></td>
    <td><?= htmlspecialchars($eq['name']) ?></td>
    <td><?= htmlspecialchars($eq['description']) ?></td>
    <td><?= htmlspecialchars($eq['room_number']) ?></td>
    <td>
        <form method="post" style="display:inline-block" onsubmit="return confirm('Delete this equipment?');">
            <input type="hidden" name="equipment_id" value="<?= $eq['equipment_id'] ?>">
            <button type="submit" name="delete_equipment">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<a href="dashboard.php">Back to Dashboard</a>
</body>
</html>

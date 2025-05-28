<?php
session_start();
require_once "../db/db.php";

// Check if user is logged in and role is student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch available PCs for reporting (same logic as select_pc.php)
$where_clause = "WHERE pcs.is_active = 1 AND pcs.used_by IS NOT NULL"; 
// You want PCs that are currently assigned (used_by is NOT NULL) so student can report on their PC

$pcs_query = "SELECT pcs.pc_id, pcs.pc_number, rooms.room_number
              FROM pcs 
              JOIN rooms ON pcs.room_id = rooms.room_id
              $where_clause
              ORDER BY rooms.room_number, pcs.pc_number";

$available_pcs = $conn->query($pcs_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pc_id = $_POST['pc_id'] ?? null;
    $report_text = trim($_POST['report_text'] ?? '');

    if (empty($pc_id) || empty($report_text)) {
        $error = "Please fill all required fields.";
    } else {
        // Verify that the PC selected is assigned to the current user
        $check_pc = $conn->prepare("SELECT * FROM pcs WHERE pc_id = ? AND used_by = ?");
        $check_pc->bind_param("ii", $pc_id, $user_id);
        $check_pc->execute();
        $result = $check_pc->get_result();

        if ($result->num_rows === 0) {
            $error = "You can only report issues on PCs assigned to you.";
        } else {
            // Insert maintenance report
            $stmt = $conn->prepare("INSERT INTO maintenance_reports (pc_id, reported_by, report_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $pc_id, $user_id, $report_text);

            if ($stmt->execute()) {
                $success = "Report submitted successfully.";
            } else {
                $error = "Error submitting report: " . $stmt->error;
            }
            $stmt->close();
        }

        $check_pc->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Report Issue - Student</title>
</head>
<body>
<h1>Report a Maintenance Issue</h1>

<?php if ($success): ?>
    <div style="background: #e8f5e8; padding: 10px; border: 1px solid #4CAF50; color: #2e7d32; margin-bottom: 10px;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; color: #c62828; margin-bottom: 10px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($available_pcs->num_rows > 0): ?>
<form method="post" action="report_issue.php">
    <label for="pc_id">Select PC:</label><br />
    <select id="pc_id" name="pc_id" required>
        <option value="">-- Select a PC --</option>
        <?php while ($pc = $available_pcs->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($pc['pc_id']) ?>"
                <?= (isset($_POST['pc_id']) && $_POST['pc_id'] == $pc['pc_id']) ? 'selected' : '' ?>>
                Room <?= htmlspecialchars($pc['room_number']) ?> - PC <?= htmlspecialchars($pc['pc_number']) ?>
            </option>
        <?php endwhile; ?>
    </select><br /><br />

    <label for="report_text">Describe the issue:</label><br />
    <textarea id="report_text" name="report_text" rows="5" cols="50" required><?= isset($_POST['report_text']) ? htmlspecialchars($_POST['report_text']) : '' ?></textarea><br /><br />

    <button type="submit">Submit Issue</button>
</form>
<?php else: ?>
    <p>No PCs assigned to you currently. You cannot submit a report.</p>
<?php endif; ?>

<p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>

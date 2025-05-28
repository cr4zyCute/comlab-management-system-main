<?php
session_start();
require '../db/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Find the current active PC usage log for this user
$usage_query = $conn->query("SELECT * FROM usage_logs WHERE user_id = $user_id AND logout_time IS NULL LIMIT 1");
if ($usage_query->num_rows === 0) {
    // No active PC session, just redirect
    header("Location: dashboard.php");
    exit;
}

$usage = $usage_query->fetch_assoc();
$pc_id = $usage['pc_id'];

// Start transaction to update both usage_logs and pcs tables
$conn->begin_transaction();

try {
    // Update usage_logs: set logout_time to now
    $stmt1 = $conn->prepare("UPDATE usage_logs SET logout_time = NOW() WHERE usage_log_id = ?");
    $stmt1->bind_param("i", $usage['usage_log_id']);
    $stmt1->execute();

    // Update pcs: set used_by to NULL (free the PC)
    $stmt2 = $conn->prepare("UPDATE pcs SET used_by = NULL WHERE pc_id = ?");
    $stmt2->bind_param("i", $pc_id);
    $stmt2->execute();

    $conn->commit();

    header("Location: dashboard.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo "Failed to logout from PC: " . $e->getMessage();
}
?>

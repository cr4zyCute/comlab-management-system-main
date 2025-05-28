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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        nav a span.material-icons {
            margin-right: 12px;
            font-size: 24px;
            vertical-align: middle;
        }

        nav::-webkit-scrollbar {
            width: 6px;
        }

        nav::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar {
            transition: width 0.3s;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar w-64 bg-red-700 text-white p-6 flex flex-col overflow-y-auto shadow-lg">
            <div class="flex items-center mb-8">
                <h1 class="text-3xl font-extrabold tracking-wide">Student</h1>
                <button id="toggleSidebar" class="ml-2 text-white focus:outline-none">
                    <span class="material-icons">chevron_left</span>
                </button>
            </div>

            <p class="text-xs font-semibold uppercase mb-4 tracking-wide text-red-300">Menu</p>

            <ul class="flex-grow space-y-2">
                <li>
                    <a href="dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">dashboard</span>
                        <span class="font-semibold text-lg">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="select_pc.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">desktop_windows</span>
                        <span class="font-semibold text-lg">Select PC</span>
                    </a>
                </li>
                <li>
                    <a href="report_issue.php" class="flex items-center p-3 rounded-lg bg-red-800 hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">report_problem</span>
                        <span class="font-semibold text-lg">Report Issue</span>
                    </a>
                </li>
                <li>
                    <a href="announcements.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">announcement</span>
                        <span class="font-semibold text-lg">Announcements</span>
                    </a>
                </li>
            </ul>

            <div class="mt-auto pt-6 border-t border-red-800">
                <p class="text-xs font-semibold uppercase mb-4 tracking-wide text-red-300">Support</p>
                <ul>
                    <li>
                        <a href="../logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                            <span class="material-icons">logout</span>
                            <span class="font-semibold text-lg">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow p-6 md:p-10 overflow-y-auto">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-4xl font-extrabold text-gray-800">Report a Maintenance Issue</h2>
                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 transition-colors duration-200">
                    <span class="material-icons mr-2">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    <div class="flex items-center">
                        <span class="material-icons mr-2">check_circle</span>
                        <?= htmlspecialchars($success) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    <div class="flex items-center">
                        <span class="material-icons mr-2">error</span>
                        <?= htmlspecialchars($error) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($available_pcs->num_rows > 0): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <form method="post" action="report_issue.php" class="space-y-6">
                            <div>
                                <label for="pc_id" class="block text-sm font-medium text-gray-700 mb-2">Select PC</label>
                                <select id="pc_id" name="pc_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                    <option value="">-- Select a PC --</option>
                                    <?php while ($pc = $available_pcs->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($pc['pc_id']) ?>"
                                            <?= (isset($_POST['pc_id']) && $_POST['pc_id'] == $pc['pc_id']) ? 'selected' : '' ?>>
                                            Room <?= htmlspecialchars($pc['room_number']) ?> - PC <?= htmlspecialchars($pc['pc_number']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div>
                                <label for="report_text" class="block text-sm font-medium text-gray-700 mb-2">Describe the issue</label>
                                <textarea id="report_text" name="report_text" rows="5" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"><?= isset($_POST['report_text']) ? htmlspecialchars($_POST['report_text']) : '' ?></textarea>
                            </div>

                            <button type="submit"
                                class="w-full bg-red-700 text-white py-2 px-4 rounded-lg hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                                Submit Issue
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 text-center">
                        <span class="material-icons text-gray-400 text-6xl mb-4">desktop_windows</span>
                        <p class="text-gray-500 text-lg">No PCs assigned to you currently. You cannot submit a report.</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('w-64');
            sidebar.classList.toggle('w-20');
        });
    </script>
</body>

</html>
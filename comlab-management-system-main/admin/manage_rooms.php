<?php
session_start();
require '../db/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle PC status toggle
if (isset($_POST['toggle_pc'])) {
    $pc_id = $_POST['pc_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE pcs SET is_active = ? WHERE pc_id = ?");
    $stmt->bind_param("ii", $new_status, $pc_id);
    $stmt->execute();
    header("Location: manage_rooms.php");
    exit;
}

// Fetch all rooms with their PCs
$result = $conn->query("SELECT r.*, p.pc_id, p.pc_number, p.is_active 
                       FROM rooms r 
                       LEFT JOIN pcs p ON r.room_id = p.room_id 
                       ORDER BY r.room_number, p.pc_number");
$rooms = [];
while ($row = $result->fetch_assoc()) {
    if (!isset($rooms[$row['room_id']])) {
        $rooms[$row['room_id']] = [
            'room_id' => $row['room_id'],
            'room_number' => $row['room_number'],
            'pcs' => []
        ];
    }
    if ($row['pc_id']) {
        $rooms[$row['room_id']]['pcs'][] = [
            'pc_id' => $row['pc_id'],
            'pc_number' => $row['pc_number'],
            'is_active' => $row['is_active']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Admin Dashboard</title>
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
                <h1 class="text-3xl font-extrabold tracking-wide">Admin</h1>
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
                    <a href="active_pcs.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">desktop_windows</span>
                        <span class="font-semibold text-lg">Active PCs</span>
                    </a>
                </li>
                <li>
                    <a href="manage_users.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">people</span>
                        <span class="font-semibold text-lg">Users</span>
                    </a>
                </li>
                <li>
                    <a href="manage_rooms.php" class="flex items-center p-3 rounded-lg bg-red-800 hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">meeting_room</span>
                        <span class="font-semibold text-lg">Rooms</span>
                    </a>
                </li>
                <li>
                    <a href="maintenance_reports.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">description</span>
                        <span class="font-semibold text-lg">Reports</span>
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
                <h2 class="text-4xl font-extrabold text-gray-800">Manage Rooms</h2>
                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 transition-colors duration-200">
                    <span class="material-icons mr-2">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-800">
                                    Room <?= htmlspecialchars($room['room_number']) ?>
                                </h3>
                            </div>

                            <div class="space-y-3">
                                <?php foreach ($room['pcs'] as $pc): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <span class="material-icons text-gray-500 mr-2">desktop_windows</span>
                                            <span class="font-medium text-gray-700">PC <?= $pc['pc_number'] ?></span>
                                        </div>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="pc_id" value="<?= $pc['pc_id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= $pc['is_active'] ? 0 : 1 ?>">
                                            <button type="submit" name="toggle_pc"
                                                class="px-3 py-1 rounded-full text-sm font-medium transition-colors duration-200
                                                           <?= $pc['is_active']
                                                                ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                                : 'bg-red-100 text-red-800 hover:bg-red-200' ?>">
                                                <?= $pc['is_active'] ? 'Active' : 'Inactive' ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
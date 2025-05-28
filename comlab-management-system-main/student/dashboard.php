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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
                    <a href="dashboard.php" class="flex items-center p-3 rounded-lg bg-red-800 hover:bg-red-600 transition-colors duration-200">
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
                    <a href="report_issue.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
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
            <div class="mb-8">
                <h2 class="text-4xl font-extrabold text-gray-800">Welcome, <?= htmlspecialchars($user['full_name']) ?></h2>
                <p class="mt-2 text-gray-600">
                    <span class="font-semibold">Course:</span> <?= htmlspecialchars($user['course']) ?> |
                    <span class="font-semibold">Year:</span> <?= htmlspecialchars($user['year']) ?> |
                    <span class="font-semibold">Section:</span> <?= htmlspecialchars($user['section']) ?>
                </p>
            </div>

            <?php if ($current_pc): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8 rounded-lg shadow-sm">
                    <div class="flex items-center">
                        <span class="material-icons text-green-500 mr-3">computer</span>
                        <div>
                            <h3 class="text-lg font-semibold text-green-800">Currently Using PC</h3>
                            <p class="text-green-700">
                                <span class="font-semibold">Room:</span> <?= htmlspecialchars($current_pc['room_number']) ?> |
                                <span class="font-semibold">PC:</span> <?= htmlspecialchars($current_pc['pc_number']) ?> |
                                <span class="font-semibold">Login Time:</span> <?= htmlspecialchars($current_pc['login_time']) ?>
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="logout_pc.php" onclick="return confirm('Are you sure you want to logout from the PC?')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                            <span class="material-icons mr-2">logout</span>
                            Logout from PC
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Computer Lab Status -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Computer Lab Status</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total PCs</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupied</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($room = $room_stats->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                Room <?= htmlspecialchars($room['room_number']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= $room['total_pcs'] ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= $room['available_pcs'] ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= $room['occupied_pcs'] ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php if ($room['available_pcs'] > 0 && !$current_pc): ?>
                                                    <a href="select_pc.php?room=<?= $room['room_number'] ?>"
                                                        class="text-red-600 hover:text-red-800 font-medium">
                                                        Select PC
                                                    </a>
                                                <?php elseif ($current_pc): ?>
                                                    <span class="text-gray-400">Currently Using PC</span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">No Available PCs</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Announcements -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Announcements</h3>
                        <?php if ($announcements->num_rows > 0): ?>
                            <div class="space-y-4">
                                <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                    <div class="border-l-4 border-red-500 pl-4 py-2">
                                        <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($announcement['title']) ?></h4>
                                        <p class="text-sm text-gray-500 mb-2"><?= $announcement['date_posted'] ?></p>
                                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($announcement['message'])) ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="mt-4">
                                <a href="announcements.php" class="text-red-600 hover:text-red-800 font-medium inline-flex items-center">
                                    <span class="material-icons mr-1">arrow_forward</span>
                                    View All Announcements
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500">No announcements available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const toggleButton = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');

        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('w-64');
            sidebar.classList.toggle('w-20');
            const icon = toggleButton.querySelector('.material-icons');
            icon.textContent = sidebar.classList.contains('w-64') ? 'chevron_left' : 'chevron_right';
        });
    </script>
</body>

</html>
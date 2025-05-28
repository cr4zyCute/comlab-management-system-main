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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Student</title>
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
                    <a href="report_issue.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <span class="material-icons">report_problem</span>
                        <span class="font-semibold text-lg">Report Issue</span>
                    </a>
                </li>
                <li>
                    <a href="announcements.php" class="flex items-center p-3 rounded-lg bg-red-800 hover:bg-red-600 transition-colors duration-200">
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
                <h2 class="text-4xl font-extrabold text-gray-800">Announcements</h2>
                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 transition-colors duration-200">
                    <span class="material-icons mr-2">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <?php if ($announcements->num_rows > 0): ?>
                        <div class="space-y-6">
                            <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                <div class="p-6 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($announcement['title']) ?></h3>
                                        <span class="text-sm text-gray-500">
                                            <?= date('F j, Y g:i A', strtotime($announcement['date_posted'])) ?>
                                        </span>
                                    </div>
                                    <div class="prose max-w-none text-gray-700">
                                        <?= nl2br(htmlspecialchars($announcement['message'])) ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <span class="material-icons text-gray-400 text-6xl mb-4">announcement</span>
                            <p class="text-gray-500 text-lg">No announcements available at this time.</p>
                        </div>
                    <?php endif; ?>
                </div>
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
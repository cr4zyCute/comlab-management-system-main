<?php
session_start();
require '../db/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Fetch reports with PC info and reporter info
$sql = "SELECT maintenance_reports.*, pcs.pc_number, rooms.room_number, users.full_name
        FROM maintenance_reports
        LEFT JOIN pcs ON maintenance_reports.pc_id = pcs.pc_id
        LEFT JOIN rooms ON pcs.room_id = rooms.room_id
        LEFT JOIN users ON maintenance_reports.reported_by = users.user_id
        ORDER BY maintenance_reports.report_date DESC";
$reports = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance Reports - Admin Dashboard</title>
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
          <a href="manage_rooms.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
            <span class="material-icons">meeting_room</span>
            <span class="font-semibold text-lg">Rooms</span>
          </a>
        </li>
        <li>
          <a href="maintenance_reports.php" class="flex items-center p-3 rounded-lg bg-red-800 hover:bg-red-600 transition-colors duration-200">
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
        <h2 class="text-4xl font-extrabold text-gray-800">Maintenance Reports</h2>
        <a href="dashboard.php" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 transition-colors duration-200">
          <span class="material-icons mr-2">arrow_back</span>
          Back to Dashboard
        </a>
      </div>

      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PC Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported By</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Text</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php while ($report = $reports->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $report['report_id'] ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Room <?= htmlspecialchars($report['room_number']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PC <?= $report['pc_number'] ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($report['full_name']) ?></td>
                  <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($report['report_text']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $report['report_date'] ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
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
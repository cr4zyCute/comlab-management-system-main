  <?php
  include '../db/db.php';  // Adjust the path if needed

  // Query total active PCs
  $sql_active_pcs = "SELECT COUNT(*) as active_count FROM pcs WHERE is_active = 1";
  $result_active_pcs = $conn->query($sql_active_pcs);
  if (!$result_active_pcs) {
      die("SQL Error (active PCs): " . $conn->error);
  }
  $active_pcs = $result_active_pcs->fetch_assoc()['active_count'];

  // Query total users
  $sql_users = "SELECT COUNT(*) as user_count FROM users";
  $result_users = $conn->query($sql_users);
  if (!$result_users) {
      die("SQL Error (users): " . $conn->error);
  }
  $total_users = $result_users->fetch_assoc()['user_count'];

  // Query total rooms
  $sql_rooms = "SELECT COUNT(*) as room_count FROM rooms";
  $result_rooms = $conn->query($sql_rooms);
  if (!$result_rooms) {
      die("SQL Error (rooms): " . $conn->error);
  }
  $total_rooms = $result_rooms->fetch_assoc()['room_count'];

  // Query total announcements
  $sql_announcements = "SELECT COUNT(*) as announcements_count FROM announcements";
  $result_announcements = $conn->query($sql_announcements);
  if (!$result_announcements) {
      die("SQL Error (announcements): " . $conn->error);
  }
  $total_announcements = $result_announcements->fetch_assoc()['announcements_count'];

  // Query total maintenance reports
  $sql_reports = "SELECT COUNT(*) as reports_count FROM maintenance_reports";
  $result_reports = $conn->query($sql_reports);
  if (!$result_reports) {
      die("SQL Error (reports): " . $conn->error);
  }
  $total_reports = $result_reports->fetch_assoc()['reports_count'];

  // Query total PCs per room for chart
  $sql_pcs_per_room = "
      SELECT rooms.room_number, COUNT(pcs.pc_id) as total_pcs
      FROM rooms
      LEFT JOIN pcs ON pcs.room_id = rooms.room_id
      GROUP BY rooms.room_id, rooms.room_number
      ORDER BY rooms.room_number ASC
  ";
  $result_pcs_per_room = $conn->query($sql_pcs_per_room);
  if (!$result_pcs_per_room) {
      die("SQL Error (PCs per room): " . $conn->error);
  }

  // Prepare data for chart in PHP arrays
  $rooms_labels = [];
  $pcs_counts = [];
  while ($row = $result_pcs_per_room->fetch_assoc()) {
      $rooms_labels[] = $row['room_number'];
      $pcs_counts[] = (int)$row['total_pcs'];
  }
  ?>

  <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <span class="ml-auto text-xs font-bold bg-red-900 rounded-full px-2 py-0.5"><?= htmlspecialchars($active_pcs) ?></span>
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
            <span class="ml-auto text-xs font-bold bg-red-900 rounded-full px-2 py-0.5"><?= htmlspecialchars($total_users) ?></span>
          </a>
        </li>
        <li>
          <a href="manage_rooms.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
            <span class="material-icons">meeting_room</span>
            <span class="font-semibold text-lg">Rooms</span>
          </a>
        </li>
        <li>
          <a href="maintenance_reports.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
            <span class="material-icons">description</span>
            <span class="font-semibold text-lg">Reports</span>
            <span class="ml-auto text-xs font-bold bg-red-900 rounded-full px-2 py-0.5"><?= htmlspecialchars($total_reports) ?></span>
          </a>
        </li>
        <li>
          <a href="announcements.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200">
            <span class="material-icons">announcement</span>
            <span class="font-semibold text-lg">Announcements</span>
            <span class="ml-auto text-xs font-bold bg-red-900 rounded-full px-2 py-0.5"><?= htmlspecialchars($total_announcements) ?></span>
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

    <main class="flex-grow p-6 md:p-10 overflow-y-auto">
      <h2 class="text-4xl font-extrabold mb-8 text-gray-800">Dashboard</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12">
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
          <h3 class="text-lg font-semibold mb-2 text-red-700">Active PCs</h3>
          <p class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($active_pcs) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
          <h3 class="text-lg font-semibold mb-2 text-red-700">Users</h3>
          <p class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($total_users) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
          <h3 class="text-lg font-semibold mb-2 text-red-700">Rooms</h3>
          <p class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($total_rooms) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
          <h3 class="text-lg font-semibold mb-2 text-red-700">Reports</h3>
          <p class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($total_reports) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
          <h3 class="text-lg font-semibold mb-2 text-red-700">Announcements</h3>
          <p class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($total_announcements) ?></p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-xl font-semibold mb-4 text-red-700">Active PCs</h3>
          <canvas id="activePCsChart" class="w-full h-48"></canvas>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-xl font-semibold mb-4 text-red-700">Rooms</h3>
          <canvas id="totalRoomsChart" class="w-full h-48"></canvas>
        </div>
      </div>

      <div class="mt-12 bg-white p-6 rounded-lg shadow">
        <h3 class="text-2xl font-semibold mb-6 text-red-700">Total PCs per Room</h3>
        <canvas id="pcsPerRoomChart" class="w-full h-64"></canvas>
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

    // Chart initialization code...
    const ctx1 = document.getElementById('activePCsChart').getContext('2d');
    const activePCsChart = new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: ['Active PCs'],
        datasets: [{
          label: 'Active PCs',
          data: [<?= htmlspecialchars($active_pcs) ?>],
          backgroundColor: 'rgba(220, 38, 38, 0.7)',
          borderColor: 'rgba(220, 38, 38, 1)',
          borderWidth: 1,
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });

    const ctx2 = document.getElementById('totalRoomsChart').getContext('2d');
    const totalRoomsChart = new Chart(ctx2, {
      type: 'line',
      data: {
        labels: ['Total Rooms'],
        datasets: [{
          label: 'Rooms',
          data: [<?= htmlspecialchars($total_rooms) ?>],
          fill: false,
          backgroundColor: 'rgba(37, 99, 235, 0.7)',
          borderColor: 'rgba(37, 99, 235, 1)',
          borderWidth: 3,
          tension: 0.3,
          pointRadius: 5,
          pointHoverRadius: 7,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });

    const ctx3 = document.getElementById('pcsPerRoomChart').getContext('2d');
    const pcsPerRoomChart = new Chart(ctx3, {
      type: 'line',
      data: {
        labels: <?= json_encode($rooms_labels) ?>,
        datasets: [{
          label: 'PCs per Room',
          data: <?= json_encode($pcs_counts) ?>,
          fill: false,
          backgroundColor: 'rgba(234, 88, 12, 0.7)',
          borderColor: 'rgba(234, 88, 12, 1)',
          borderWidth: 3,
          tension: 0.3,
          pointRadius: 5,
          pointHoverRadius: 7,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        },
        plugins: {
          legend: {
            labels: {
              font: { size: 14 },
              color: '#D9480F'
            }
          }
        }
      }
    });
  </script>
</body>
</html> 
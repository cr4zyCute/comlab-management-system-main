<?php
$conn = new mysqli("localhost", "root", "", "comlab_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST["full_name"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $role = $conn->real_escape_string($_POST["role"]); // get role from form
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // For students, get course, year, section; else set to NULL
    if ($role === "student") {
        $course = "'" . $conn->real_escape_string($_POST["course"]) . "'";
        $year = "'" . $conn->real_escape_string($_POST["year"]) . "'";
        $section = "'" . $conn->real_escape_string($_POST["section"]) . "'";
    } else {
        $course = "NULL";
        $year = "NULL";
        $section = "NULL";
    }

    // Check if email exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (full_name, email, password, role, course, year, section) 
                VALUES ('$full_name', '$email', '$password', '$role', $course, $year, $section)";
        if ($conn->query($sql)) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed! " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Computer Lab Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-red-700 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">Create an Account</h2>
            </div>

            <div class="p-6">
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="material-icons text-red-500">error</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="relative">
                            <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">person</span>
                            <input type="text" name="full_name" id="full_name" required
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Enter your full name">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">email</span>
                            <input type="email" name="email" id="email" required
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Enter your email">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">lock</span>
                            <input type="password" name="password" id="password" required
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Create a password">
                        </div>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <div class="relative">
                            <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">badge</span>
                            <select name="role" id="role" onchange="toggleStudentFields()" required
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none">
                                <option value="student">Student</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div id="studentFields" class="space-y-6">
                        <div>
                            <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                            <div class="relative">
                                <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">school</span>
                                <select name="course" id="course"
                                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none">
                                    <option value="BSIT">BSIT</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                            <div class="relative">
                                <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">calendar_today</span>
                                <select name="year" id="year"
                                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none">
                                    <option value="1st year">1st year</option>
                                    <option value="2nd year">2nd year</option>
                                    <option value="3rd year">3rd year</option>
                                    <option value="4th year">4th year</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                            <div class="relative">
                                <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">groups</span>
                                <select name="section" id="section"
                                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-red-700 text-white py-2 px-4 rounded-lg hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200 flex items-center justify-center">
                        <span class="material-icons mr-2">how_to_reg</span>
                        Register
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="login.php" class="font-medium text-red-700 hover:text-red-800 transition-colors duration-200">
                            Login here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleStudentFields() {
            const role = document.getElementById('role').value;
            const studentFields = document.getElementById('studentFields');
            studentFields.style.display = role === 'student' ? 'block' : 'none';

            // Toggle required attribute for student fields
            const studentInputs = studentFields.querySelectorAll('select');
            studentInputs.forEach(input => {
                input.required = role === 'student';
            });
        }
        // Initialize the form correctly
        toggleStudentFields();
    </script>
</body>

</html>
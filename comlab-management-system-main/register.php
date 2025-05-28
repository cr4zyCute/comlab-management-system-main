<?php
$conn = new mysqli("localhost", "root", "", "comlab_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Updated field names to match the new form
    $first_name = $conn->real_escape_string($_POST["first_name"]);
    $last_name = $conn->real_escape_string($_POST["last_name"]);
    $student_id = $conn->real_escape_string($_POST["student_id"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $gender = $conn->real_escape_string($_POST["gender"]);
    $year = $conn->real_escape_string($_POST["year"]); // Assuming 'year' and 'section' remain separate in backend
    $section = $conn->real_escape_string($_POST["section"]); // Assuming 'year' and 'section' remain separate in backend
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = "student"; // Default role for registration is student as per the image
    $course = "BSIT"; // Default course as per previous code, not shown in image but kept for backend compatibility

    // Basic validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email or student ID exists
        $check_email = $conn->query("SELECT * FROM users WHERE email='$email'");
        $check_student_id = $conn->query("SELECT * FROM users WHERE student_id='$student_id'");

        if ($check_email->num_rows > 0) {
            $error = "Email already exists!";
        } elseif ($check_student_id->num_rows > 0) {
            $error = "Student ID already exists!";
        } else {
            // Modified INSERT statement to include new fields and match image
            $sql = "INSERT INTO users (full_name, student_id, email, password, role, course, year, section, gender) 
                    VALUES ('$first_name $last_name', '$student_id', '$email', '$hashed_password', '$role', '$course', '$year', '$section', '$gender')";

            if ($conn->query($sql)) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed! " . $conn->error;
            }
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
    <style>
        /* Add any necessary custom styles here */
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center">

    <!-- Header Section -->
    <header class="w-full bg-white shadow-md py-4">
        <div class="container mx-auto flex items-center justify-between px-4">
            <div class="flex items-center">
                <!-- Replace with your actual logo paths and alt text -->
                <img src="images/logo1.png" alt="Logo 1" class="h-10 mr-2">
                <img src="images/logo2.png" alt="Logo 2" class="h-10 mr-4">
                <h1 class="text-xl font-bold text-gray-800">COMPUTER LABORATORY MANAGEMENT SYSTEM</h1>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow container mx-auto px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-2xl">
            <div class="bg-red-700 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">REGISTER ACCOUNT</h2>
            </div>

            <div class="p-6">
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name:</label>
                        <input type="text" name="first_name" id="first_name" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name:</label>
                        <input type="text" name="last_name" id="last_name" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="student_id" class="block text-sm font-medium text-gray-700">Student ID.:</label>
                        <input type="text" name="student_id" id="student_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gender:</label>
                        <div class="mt-1 flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="Male" class="form-radio text-red-600" required>
                                <span class="ml-2 text-gray-700">MALE</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="Female" class="form-radio text-red-600" required>
                                <span class="ml-2 text-gray-700">FEMALE</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" name="email" id="email" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Year & Section:</label>
                        <div class="mt-1 grid grid-cols-2 gap-4">
                            <select name="year" id="year" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm appearance-none">
                                <option value="" disabled selected>Select Year</option>
                                <option value="1st year">1st year</option>
                                <option value="2nd year">2nd year</option>
                                <option value="3rd year">3rd year</option>
                                <option value="4th year">4th year</option>
                            </select>
                            <select name="section" id="section" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm appearance-none">
                                <option value="" disabled selected>Select Section</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Register
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="login.php" class="font-medium text-red-600 hover:text-red-500">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

</body>

</html>
<?php
session_start();
require "db/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["role"] = $user["role"];
            header("Location: " . ($user["role"] === "admin" ? "admin/dashboard.php" : "student/dashboard.php"));
            exit();
        }
    }
    $error = "Invalid email or password";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Computer Lab Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Add any necessary custom styles here */
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col lg:flex-row w-full max-w-4xl">
        <!-- Left Section: Branding -->
        <div class="w-full lg:w-1/2 bg-red-800 text-white flex items-center justify-center p-8">
            <div class="text-center">
                <!-- Replace with your actual logo paths and alt text -->
                <img src="images/logo1.png" alt="Logo 1" class="mx-auto mb-4 h-24">
                <img src="images/logo2.png" alt="Logo 2" class="mx-auto mb-6 h-24">
                <h1 class="text-3xl font-bold uppercase tracking-wide">BSIT</h1>
                <h2 class="text-2xl font-semibold uppercase tracking-wide">Computer Laboratory</h2>
                <h2 class="text-2xl font-semibold uppercase tracking-wide">Management System</h2>
            </div>
        </div>

        <!-- Right Section: Login Form -->
        <div class="w-full lg:w-1/2 p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">LOGIN</h2>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">EMAIL</label>
                    <input type="email" name="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">PASSWORD</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="password" name="password" id="password" required class="block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        <!-- You can add an icon here for password visibility toggle if needed -->
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        LOGIN
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account?
                    <a href="register.php" class="font-medium text-red-600 hover:text-red-500">Register now</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>
<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';

$message = '';
$messageType = 'error';

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user->name = $_POST['name'] ?? '';
        $user->email = $_POST['email'] ?? '';
        $user->password = $_POST['password'] ?? '';
        $user->role = $_POST['role'] ?? '';

        // Validate input
        if (empty($user->name) || empty($user->email) || empty($user->password)) {
            $message = "Please fill all required fields";
        } elseif (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format";
        } elseif (strlen($user->password) < 6) {
            $message = "Password must be at least 6 characters long";
        } elseif ($user->emailExists()) {
            $message = "Email already exists";
        } else {
            if ($user->register()) {
                $_SESSION['success_message'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $message = "Unable to register. Please try again.";
            }
        }
    }
} catch (Exception $e) {
    $message = "System Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Create your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        sign in to your account
                    </a>
                </p>
            </div>
            <?php if ($message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>
            <form class="mt-8 space-y-6" action="register.php" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="name" class="sr-only">Full Name</label>
                        <input id="name" name="name" type="text" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Full Name"
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Email address"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Password">
                    </div>
                    <div>
                        <label for="role" class="sr-only">Role</label>
                        <select id="role" name="role" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                            <option value="USER" <?php echo (isset($_POST['role']) && $_POST['role'] === 'USER') ? 'selected' : ''; ?>>Job Seeker</option>
                            <option value="HR" <?php echo (isset($_POST['role']) && $_POST['role'] === 'HR') ? 'selected' : ''; ?>>Company HR</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
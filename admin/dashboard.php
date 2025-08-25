<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../classes/Job.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$job = new Job($db);

// Get user statistics
$stats = $user->getUserStats();

// Get all users except admin
$users = $user->getAllUsers();

// Handle user actions (verify, block, etc.)
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['userId'])) {
        $targetUserId = $_POST['userId'];

        switch ($_POST['action']) {
            case 'verify':
                if ($user->verifyUser($targetUserId)) {
                    $message = "User verified successfully";
                    $messageType = "success";
                }
                break;
            case 'block':
                if ($user->blockUser($targetUserId)) {
                    $message = "User blocked successfully";
                    $messageType = "success";
                }
                break;
            case 'unblock':
                if ($user->unblockUser($targetUserId)) {
                    $message = "User unblocked successfully";
                    $messageType = "success";
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <a href="dashboard.php" class="text-2xl font-bold text-blue-600">Job Portal</a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="dashboard.php" class="text-blue-600 px-3 py-2">Dashboard</a>
                        <a href="users.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Users</a>
                        <a href="jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Jobs</a>
                        <a href="../auth/logout.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="mt-2 text-gray-600">Overview of the job portal system.</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="mt-8">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Job Seekers Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-blue-600 text-3xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Job Seekers
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            <?php echo $stats['jobSeekers']; ?>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <a href="users.php?role=USER" class="font-medium text-blue-600 hover:text-blue-900">
                                View all job seekers <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- HR Managers Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-building text-purple-600 text-3xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        HR Managers
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            <?php echo $stats['hrManagers']; ?>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <a href="users.php?role=HR" class="font-medium text-blue-600 hover:text-blue-900">
                                View all HR managers <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Active Jobs Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-briefcase text-green-600 text-3xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Active Jobs
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            <?php echo $job->getTotalJobs(); ?>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <a href="jobs.php" class="font-medium text-blue-600 hover:text-blue-900">
                                View all jobs <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activity</h3>
                    <div class="mt-4">
                        <!-- Add recent activity content here if needed -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users Section -->
        <div class="mt-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Recent Users</h2>
                <a href="users.php" class="text-blue-600 hover:text-blue-800">View all users</a>
            </div>
            <div class="mt-4 flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($users as $userData): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($userData['name']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo htmlspecialchars($userData['email']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $userData['role'] === 'HR' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                                <?php echo htmlspecialchars($userData['role']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                                                if ($userData['role'] === 'HR') {
                                                    echo $userData['isVerified'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                                } else {
                                                    echo 'bg-green-100 text-green-800';
                                                } ?>">
                                                <?php
                                                if ($userData['role'] === 'HR') {
                                                    echo $userData['isVerified'] ? 'Verified' : 'Pending';
                                                } else {
                                                    echo 'Active';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="dashboard.php" method="POST" class="inline">
                                                <input type="hidden" name="userId" value="<?php echo $userData['id']; ?>">
                                                <?php if (!$userData['isVerified'] && $userData['role'] === 'HR'): ?>
                                                    <button type="submit" name="action" value="verify" class="text-blue-600 hover:text-blue-900 mr-2">
                                                        Verify
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (!$userData['blockedByAdmin']): ?>
                                                    <button type="submit" name="action" value="block" class="text-red-600 hover:text-red-900">
                                                        Block
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="unblock" class="text-green-600 hover:text-green-900">
                                                        Unblock
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
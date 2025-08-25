<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'USER') {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$userData = $user->getUser($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <a href="/" class="text-2xl font-bold text-blue-600">Job Portal</a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="/" class="text-gray-600 hover:text-blue-600 px-3 py-2">Home</a>
                        <a href="jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Jobs</a>
                        <a href="profile.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Profile</a>
                        <a href="auth/logout.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-3xl font-bold text-gray-900">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p class="mt-2 text-gray-600">Find your next opportunity from our curated list of jobs.</p>
        </div>

        <!-- Stats Section -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Applications Stats -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-paper-plane text-blue-600 text-3xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Applications
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    0
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookmarks Stats -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bookmark text-blue-600 text-3xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Saved Jobs
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    0
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Completion -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle text-blue-600 text-3xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Profile Completion
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    <?php
                                    $completion = 0;
                                    if (!empty($userData['resume'])) $completion += 30;
                                    if (!empty($userData['skills'])) $completion += 20;
                                    if (!empty($userData['aboutMe'])) $completion += 20;
                                    if (!empty($userData['linkedinLink'])) $completion += 15;
                                    if (!empty($userData['githubLink'])) $completion += 15;
                                    echo $completion . '%';
                                    ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Jobs Section -->
        <div class="mt-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Recent Jobs</h2>
                <a href="jobs.php" class="text-blue-600 hover:text-blue-800">View all jobs</a>
            </div>
            <div class="mt-4 grid gap-5 max-w-lg mx-auto lg:grid-cols-3 lg:max-w-none">
                <!-- Job listings will be dynamically loaded here -->
                <p class="text-gray-500 text-center col-span-3">Loading recent jobs...</p>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jobsContainer = document.querySelector('.grid.gap-5');
        // Load recent jobs via AJAX
        fetch('api/jobs.php?limit=6')
            .then(response => response.json())
            .then(result => {
                if (result.status === 'error') {
                    throw new Error(result.message);
                }
                const jobs = result.data.jobs;
                if (jobs.length === 0) {
                    jobsContainer.innerHTML = '<p class="text-gray-500 text-center col-span-3">No jobs found</p>';
                    return;
                }

                jobsContainer.innerHTML = jobs.map(job => `
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="${job.companyLogo || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSIyMCIgZmlsbD0iI0U1RTdFQiIvPgogIDxwYXRoIGQ9Ik0xMiAxNkMxMiAxMy43OTA5IDEzLjc5MDkgMTIgMTYgMTJIMjRDMjYuMjA5MSAxMiAyOCAxMy43OTA5IDI4IDE2VjI0QzI4IDI2LjIwOTEgMjYuMjA5MSAyOCAyNCAyOEgxNkMxMy43OTA5IDI4IDEyIDI2LjIwOTEgMTIgMjRWMThaIiBmaWxsPSIjOUNBM0FGIi8+CiAgPHBhdGggZD0iTTE2IDE4SDI0TTE2IDIySDIwTTE2IDI2SDIyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K'}" alt="${job.companyName}" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSIyMCIgZmlsbD0iI0U1RTdFQiIvPgogIDxwYXRoIGQ9Ik0xMiAxNkMxMiAxMy43OTA5IDEzLjc5MDkgMTIgMTYgMTJIMjRDMjYuMjA5MSAxMiAyOCAxMy43OTA5IDI4IDE2VjI0QzI4IDI2LjIwOTEgMjYuMjA5MSAyOCAyNCAyOEgxNkMxMy43OTA5IDI4IDEyIDI2LjIwOTEgMTIgMjRWMThaIiBmaWxsPSIjOUNBM0FGIi8+CiAgPHBhdGggZD0iTTE2IDE4SDI0TTE2IDIySDIwTTE2IDI2SDIyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K'">
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">${job.title}</h3>
                                    <p class="text-sm text-gray-500">${job.companyName}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500">${job.description ? job.description.substring(0, 100) + '...' : 'No description available'}</p>
                            </div>
                            <div class="mt-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ${job.type}
                                </span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ${job.workMode}
                                </span>
                            </div>
                            <div class="mt-4">
                                <a href="job.php?id=${job.id}" class="text-blue-600 hover:text-blue-800">View Details →</a>
                            </div>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => {
                console.error('Error:', error);
                jobsContainer.innerHTML = '<p class="text-red-500 text-center col-span-3">Error loading jobs: ' + error.message + '</p>';
            });
    });
    </script>
</body>
</html>
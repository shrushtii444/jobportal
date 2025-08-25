<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'HR'): ?>
                        <a href="company/dashboard.php" class="text-2xl font-bold text-blue-600">Job Portal</a>
                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                        <a href="admin/dashboard.php" class="text-2xl font-bold text-blue-600">Job Portal</a>
                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'USER'): ?>
                        <a href="dashboard.php" class="text-2xl font-bold text-blue-600">Job Portal</a>
                    <?php else: ?>
                        <a href="/" class="text-2xl font-bold text-blue-600">Job Portal</a>
                    <?php endif; ?>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'HR'): ?>
                            <a href="company/dashboard.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Dashboard</a>
                            <a href="company/jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">My Jobs</a>
                            <a href="company/post-job.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Post Job</a>
                            <a href="company/profile.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Profile</a>
                        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                            <a href="admin/dashboard.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Dashboard</a>
                            <a href="admin/users.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Users</a>
                            <a href="admin/jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Jobs</a>
                        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'USER'): ?>
                            <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Dashboard</a>
                            <a href="jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Jobs</a>
                            <a href="profile.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Profile</a>
                        <?php else: ?>
                            <a href="/" class="text-gray-600 hover:text-blue-600 px-3 py-2">Home</a>
                            <a href="jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Jobs</a>
                        <?php endif; ?>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="auth/login.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Login</a>
                            <a href="auth/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md">Register</a>
                        <?php else: ?>
                            <a href="auth/logout.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Logout</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl">
                    Find Your Dream Job
                </h1>
                <p class="mt-3 max-w-md mx-auto text-base text-blue-200 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    Browse thousands of job listings from top companies and find the perfect opportunity for your career.
                </p>
                <div class="mt-10 max-w-xl mx-auto">
                    <form action="jobs.php" method="GET" class="sm:flex justify-center">
                        <div class="min-w-0 flex-1">
                            <input type="text" name="search" placeholder="Search jobs by title, company, or keywords..."
                                class="block w-full px-4 py-3 text-base text-gray-900 placeholder-gray-500 border border-transparent rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <button type="submit" class="block w-full px-4 py-3 font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                Search Jobs
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Jobs Section -->
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Featured Jobs
            </h2>
            <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
                Discover the latest opportunities from top companies
            </p>
        </div>

        <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3" id="featuredJobs">
            <!-- Jobs will be loaded here -->
            <div class="col-span-full text-center text-gray-500">
                Loading featured jobs...
            </div>
        </div>

        <div class="mt-10 text-center">
            <a href="jobs.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                View All Jobs
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <div class="bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Why Choose Us
                </h2>
                <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
                    We connect talented professionals with amazing opportunities
                </p>
            </div>

            <div class="mt-10 grid gap-8 md:grid-cols-3">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-search-dollar text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Latest Job Opportunities</h3>
                    <p class="text-gray-600">Access thousands of job listings from top companies across various industries.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-building text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Verified Companies</h3>
                    <p class="text-gray-600">All companies on our platform are verified to ensure legitimate opportunities.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-laptop-house text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Remote & Flexible Work</h3>
                    <p class="text-gray-600">Find opportunities that match your preferred work style and location.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">About Us</h3>
                    <p class="text-gray-400">Connecting talented professionals with amazing opportunities.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="jobs.php" class="text-gray-400 hover:text-white">Browse Jobs</a></li>
                        <li><a href="companies.php" class="text-gray-400 hover:text-white">Companies</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <p class="text-gray-400">Email: info@jobportal.com</p>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700 text-center">
                <p class="text-gray-400">&copy; <?php echo date('Y'); ?> Job Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const featuredJobs = document.getElementById('featuredJobs');

        // Load featured jobs
        fetch('api/jobs.php?limit=6')
            .then(response => response.json())
            .then(result => {
                if (result.status === 'error') {
                    throw new Error(result.message);
                }

                const jobs = result.data.jobs;

                if (jobs.length === 0) {
                    featuredJobs.innerHTML = '<div class="col-span-full text-center text-gray-500">No jobs found</div>';
                    return;
                }

                // Render jobs
                featuredJobs.innerHTML = jobs.map(job => `
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full"
                                         src="${job.companyLogo || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSIyMCIgZmlsbD0iI0U1RTdFQiIvPgogIDxwYXRoIGQ9Ik0xMiAxNkMxMiAxMy43OTA5IDEzLjc5MDkgMTIgMTYgMTJIMjRDMjYuMjA5MSAxMiAyOCAxMy43OTA5IDI4IDE2VjI0QzI4IDI2LjIwOTEgMjYuMjA5MSAyOCAyNCAyOEgxNkMxMy43OTA5IDI4IDEyIDI2LjIwOTEgMTIgMjRWMThaIiBmaWxsPSIjOUNBM0FGIi8+CiAgPHBhdGggZD0iTTE2IDE4SDI0TTE2IDIySDIwTTE2IDI2SDIyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K'}"
                                         alt="${job.companyName}"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSIyMCIgZmlsbD0iI0U1RTdFQiIvPgogIDxwYXRoIGQ9Ik0xMiAxNkMxMiAxMy43OTA5IDEzLjc5MDkgMTIgMTYgMTJIMjRDMjYuMjA5MSAxMiAyOCAxMy43OTA5IDI4IDE2VjI0QzI4IDI2LjIwOTEgMjYuMjA5MSAyOCAyNCAyOEgxNkMxMy43OTA5IDI4IDEyIDI2LjIwOTEgMTIgMjRWMThaIiBmaWxsPSIjOUNBM0FGIi8+CiAgPHBhdGggZD0iTTE2IDE4SDI0TTE2IDIySDIwTTE2IDI2SDIyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K'">
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <a href="job.php?id=${job.id}" class="hover:text-blue-600">
                                            ${job.title}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        ${job.companyName}
                                        ${job.isVerified ? '<span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Verified</span>' : ''}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500">
                                    ${job.description ? job.description.substring(0, 100) + '...' : 'No description available'}
                                </p>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ${job.type}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ${job.workMode}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    ${job.location}
                                </span>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    Posted ${new Date(job.createdAt).toLocaleDateString()}
                                </div>
                                <a href="job.php?id=${job.id}"
                                   class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => {
                console.error('Error:', error);
                featuredJobs.innerHTML = `
                    <div class="col-span-full text-center text-red-500">
                        Error loading jobs: ${error.message}
                    </div>`;
            });
    });
    </script>
</body>
</html>
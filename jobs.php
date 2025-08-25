<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs - Job Portal</title>
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
                        <a href="jobs.php" class="text-blue-600 px-3 py-2">Jobs</a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="auth/login.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Login</a>
                            <a href="auth/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md">Register</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Dashboard</a>
                            <a href="auth/logout.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Logout</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Search Section -->
        <div class="px-4 py-6 sm:px-0">
            <div class="bg-white rounded-lg shadow p-6">
                <form id="searchForm" class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="search" id="search"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Job title, company, or keywords">
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Job Type</label>
                        <select id="type" name="type"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">All Types</option>
                            <option value="FULL_TIME">Full Time</option>
                            <option value="PART_TIME">Part Time</option>
                            <option value="CONTRACT">Contract</option>
                            <option value="INTERNSHIP">Internship</option>
                        </select>
                    </div>
                    <div>
                        <label for="workMode" class="block text-sm font-medium text-gray-700">Work Mode</label>
                        <select id="workMode" name="workMode"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">All Modes</option>
                            <option value="REMOTE">Remote</option>
                            <option value="HYBRID">Hybrid</option>
                            <option value="OFFICE">Office</option>
                        </select>
                    </div>
                    <div>
                        <label for="minSalary" class="block text-sm font-medium text-gray-700">Minimum Salary</label>
                        <input type="number" name="minSalary" id="minSalary"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Enter minimum salary">
                    </div>
                </form>
            </div>
        </div>

        <!-- Jobs List -->
        <div class="mt-6 px-4 sm:px-0">
            <div id="jobsList" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Jobs will be loaded here -->
                <div class="col-span-full text-center text-gray-500">
                    Loading jobs...
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="mt-6 flex justify-center">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jobsList = document.getElementById('jobsList');
        const pagination = document.getElementById('pagination');
        const searchForm = document.getElementById('searchForm');
        let currentPage = 1;

        // Load jobs with current filters
        function loadJobs(page = 1) {
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData);
            params.append('page', page);

            jobsList.innerHTML = '<div class="col-span-full text-center text-gray-500">Loading jobs...</div>';

            fetch(`api/jobs.php?${params.toString()}`)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'error') {
                        throw new Error(result.message);
                    }

                    const jobs = result.data.jobs;
                    const paginationData = result.data.pagination;

                    if (jobs.length === 0) {
                        jobsList.innerHTML = '<div class="col-span-full text-center text-gray-500">No jobs found</div>';
                        pagination.innerHTML = '';
                        return;
                    }

                    // Render jobs
                    jobsList.innerHTML = jobs.map(job => `
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
                                        ${job.description ? job.description.substring(0, 150) + '...' : 'No description available'}
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
                                    ${job.salary_min && job.salary_max ? `
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        $${job.salary_min.toLocaleString()} - $${job.salary_max.toLocaleString()}
                                    </span>
                                    ` : ''}
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

                    // Render pagination
                    if (paginationData.totalPages > 1) {
                        let paginationHtml = '<nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">';

                        // Previous button
                        if (paginationData.currentPage > 1) {
                            paginationHtml += `
                                <button onclick="loadJobs(${paginationData.currentPage - 1})"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    Previous
                                </button>`;
                        }

                        // Page numbers
                        for (let i = 1; i <= paginationData.totalPages; i++) {
                            paginationHtml += `
                                <button onclick="loadJobs(${i})"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium ${i === paginationData.currentPage ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'}">
                                    ${i}
                                </button>`;
                        }

                        // Next button
                        if (paginationData.currentPage < paginationData.totalPages) {
                            paginationHtml += `
                                <button onclick="loadJobs(${paginationData.currentPage + 1})"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    Next
                                </button>`;
                        }

                        paginationHtml += '</nav>';
                        pagination.innerHTML = paginationHtml;
                    } else {
                        pagination.innerHTML = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    jobsList.innerHTML = `
                        <div class="col-span-full text-center text-red-500">
                            Error loading jobs: ${error.message}
                        </div>`;
                });
        }

        // Load initial jobs
        loadJobs();

        // Make loadJobs function available globally
        window.loadJobs = loadJobs;

        // Handle form input changes
        const formInputs = searchForm.querySelectorAll('input, select');
        formInputs.forEach(input => {
            input.addEventListener('change', () => loadJobs(1));
        });

        // Handle search input with debounce
        const searchInput = document.getElementById('search');
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadJobs(1), 300);
        });
    });
    </script>
</body>
</html>
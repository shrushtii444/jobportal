<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Job.php';
require_once '../classes/User.php';

// Check if user is logged in and is an HR
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$job = new Job($db);
$user = new User($db);

// Get company profile
$query = "SELECT * FROM Company WHERE userId = :userId LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":userId", $_SESSION['user_id']);
$stmt->execute();
$companyProfile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$companyProfile) {
    header("Location: profile.php");
    exit();
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Set job properties
        $job->companyId = $companyProfile['id'];
        $job->title = $_POST['title'];
        $job->description = $_POST['description'];
        $job->type = $_POST['type'];
        $job->workMode = $_POST['workMode'];
        $job->location = $_POST['location'];
        $job->skills = isset($_POST['skills']) ? json_encode(explode(',', $_POST['skills'])) : null;

        // Handle salary range
        if (isset($_POST['salary_min']) && isset($_POST['salary_max'])) {
            $job->salary_min = (int)$_POST['salary_min'];
            $job->salary_max = (int)$_POST['salary_max'];
        } else {
            $job->salary_min = null;
            $job->salary_max = null;
        }

        // Handle experience range
        if (isset($_POST['experience_min']) && isset($_POST['experience_max'])) {
            $job->experience_min = (int)$_POST['experience_min'];
            $job->experience_max = (int)$_POST['experience_max'];
        } else {
            $job->experience_min = null;
            $job->experience_max = null;
        }

        // Set default values
        $job->isActive = 1;
        $job->expired = 0;
        $job->deleted = 0;

        if ($job->create()) {
            header("Location: jobs.php");
            exit();
        } else {
            throw new Exception("Failed to create job listing.");
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Job - Job Portal</title>
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
                        <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Dashboard</a>
                        <a href="jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">My Jobs</a>
                        <a href="post-job.php" class="text-blue-600 px-3 py-2">Post Job</a>
                        <a href="profile.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Profile</a>
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
            <h1 class="text-3xl font-bold text-gray-900">Post New Job</h1>
            <p class="mt-2 text-gray-600">Create a new job listing for your company.</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Job Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form action="post-job.php" method="POST">
                <!-- Job Title -->
                <div class="mb-6">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Job Title *</label>
                    <input type="text" id="title" name="title" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="e.g., Senior Software Engineer">
                </div>

                <!-- Job Description -->
                <div class="mb-6">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Job Description *</label>
                    <textarea id="description" name="description" required rows="6"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Describe the role, responsibilities, and requirements..."></textarea>
                </div>

                <!-- Job Type -->
                <div class="mb-6">
                    <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Job Type *</label>
                    <select id="type" name="type" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select job type</option>
                        <option value="FULL_TIME">Full Time</option>
                        <option value="PART_TIME">Part Time</option>
                        <option value="CONTRACT">Contract</option>
                        <option value="INTERNSHIP">Internship</option>
                    </select>
                </div>

                <!-- Work Mode -->
                <div class="mb-6">
                    <label for="workMode" class="block text-gray-700 text-sm font-bold mb-2">Work Mode *</label>
                    <select id="workMode" name="workMode" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select work mode</option>
                        <option value="REMOTE">Remote</option>
                        <option value="HYBRID">Hybrid</option>
                        <option value="OFFICE">Office</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="mb-6">
                    <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Location *</label>
                    <input type="text" id="location" name="location" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="e.g., New York, NY or Remote">
                </div>

                <!-- Skills -->
                <div class="mb-6">
                    <label for="skills" class="block text-gray-700 text-sm font-bold mb-2">Required Skills *</label>
                    <input type="text" id="skills" name="skills" required
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="e.g., PHP, MySQL, JavaScript (comma-separated)">
                </div>

                <!-- Salary Range -->
                <div class="mb-6">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="hasSalaryRange" name="hasSalaryRange" value="yes"
                            class="mr-2">
                        <label for="hasSalaryRange" class="text-gray-700 text-sm font-bold">Include Salary Range</label>
                    </div>
                    <div id="salaryRangeFields" class="hidden grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label for="currency" class="block text-gray-700 text-sm font-bold mb-2">Currency</label>
                            <select id="currency" name="currency"
                                class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="INR">INR</option>
                            </select>
                        </div>
                        <div>
                            <label for="minSalary" class="block text-gray-700 text-sm font-bold mb-2">Minimum Salary</label>
                            <input type="number" id="minSalary" name="minSalary"
                                class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label for="maxSalary" class="block text-gray-700 text-sm font-bold mb-2">Maximum Salary</label>
                            <input type="number" id="maxSalary" name="maxSalary"
                                class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                </div>

                <!-- Experience Range -->
                <div class="mb-6">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="hasExperienceRange" name="hasExperienceRange" value="yes"
                            class="mr-2">
                        <label for="hasExperienceRange" class="text-gray-700 text-sm font-bold">Include Experience Range</label>
                    </div>
                    <div id="experienceRangeFields" class="hidden grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="minExperience" class="block text-gray-700 text-sm font-bold mb-2">Minimum Years</label>
                            <input type="number" id="minExperience" name="minExperience"
                                class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label for="maxExperience" class="block text-gray-700 text-sm font-bold mb-2">Maximum Years</label>
                            <input type="number" id="maxExperience" name="maxExperience"
                                class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                </div>

                <!-- Expiry Date -->
                <div class="mb-6">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="hasExpiryDate" name="hasExpiryDate" value="yes"
                            class="mr-2">
                        <label for="hasExpiryDate" class="text-gray-700 text-sm font-bold">Set Expiry Date</label>
                    </div>
                    <div id="expiryDateField" class="hidden">
                        <input type="date" id="expiryDate" name="expiryDate"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Post Job
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle salary range fields
        document.getElementById('hasSalaryRange').addEventListener('change', function() {
            document.getElementById('salaryRangeFields').classList.toggle('hidden');
            const inputs = document.getElementById('salaryRangeFields').getElementsByTagName('input');
            for (let input of inputs) {
                input.required = this.checked;
            }
        });

        // Toggle experience range fields
        document.getElementById('hasExperienceRange').addEventListener('change', function() {
            document.getElementById('experienceRangeFields').classList.toggle('hidden');
            const inputs = document.getElementById('experienceRangeFields').getElementsByTagName('input');
            for (let input of inputs) {
                input.required = this.checked;
            }
        });

        // Toggle expiry date field
        document.getElementById('hasExpiryDate').addEventListener('change', function() {
            document.getElementById('expiryDateField').classList.toggle('hidden');
            document.getElementById('expiryDate').required = this.checked;
        });
    </script>
</body>
</html>
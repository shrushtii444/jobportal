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

// Get dashboard statistics
$stats = [
    'totalJobs' => 0,
    'activeJobs' => 0,
    'totalApplications' => 0,
    'pendingApplications' => 0
];

// Get total and active jobs count
$query = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN isActive = TRUE AND expired = FALSE AND deleted = FALSE THEN 1 ELSE 0 END) as active
          FROM Job
          WHERE companyId = :companyId";
$stmt = $db->prepare($query);
$stmt->bindParam(":companyId", $companyProfile['id']);
$stmt->execute();
$jobStats = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['totalJobs'] = $jobStats['total'];
$stats['activeJobs'] = $jobStats['active'];

// Get total and pending applications count
$query = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending
          FROM Applications a
          JOIN Job j ON a.jobId = j.id
          WHERE j.companyId = :companyId";
$stmt = $db->prepare($query);
$stmt->bindParam(":companyId", $companyProfile['id']);
$stmt->execute();
$appStats = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['totalApplications'] = $appStats['total'];
$stats['pendingApplications'] = $appStats['pending'];

// Get recent job listings
$query = "SELECT j.*,
          (SELECT COUNT(*) FROM Applications a WHERE a.jobId = j.id) as applicationCount,
          (SELECT COUNT(*) FROM Applications a WHERE a.jobId = j.id AND a.status = 'PENDING') as pendingCount
          FROM Job j
          WHERE j.companyId = :companyId
          ORDER BY j.createdAt DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(":companyId", $companyProfile['id']);
$stmt->execute();
$recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent applications
$query = "SELECT a.*, j.title as jobTitle, u.name as applicantName, u.email as applicantEmail
          FROM Applications a
          JOIN Job j ON a.jobId = j.id
          JOIN Users u ON a.userId = u.id
          WHERE j.companyId = :companyId
          ORDER BY a.createdAt DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(":companyId", $companyProfile['id']);
$stmt->execute();
$recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Job Portal</title>
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
                        <a href="jobs.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">My Jobs</a>
                        <a href="post-job.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Post Job</a>
                        <a href="profile.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Profile</a>
                        <a href="../auth/logout.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="px-4 py-6 sm:px-0">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Welcome, <?php echo htmlspecialchars($companyProfile['companyName']); ?>!</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        <?php echo $companyProfile['isVerified'] ?
                            '<span class="text-green-600"><i class="fas fa-check-circle"></i> Verified Company</span>' :
                            '<span class="text-yellow-600"><i class="fas fa-clock"></i> Verification Pending</span>'; ?>
                    </p>
                </div>
                <a href="post-job.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Post New Job
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mt-8">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Jobs Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-briefcase text-blue-600 text-3xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Jobs</dt>
                                    <dd class="text-3xl font-semibold text-gray-900"><?php echo $stats['totalJobs']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Jobs Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Jobs</dt>
                                    <dd class="text-3xl font-semibold text-gray-900"><?php echo $stats['activeJobs']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Applications Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-blue-600 text-3xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Applications</dt>
                                    <dd class="text-3xl font-semibold text-gray-900"><?php echo $stats['totalApplications']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Applications Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-yellow-600 text-3xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Applications</dt>
                                    <dd class="text-3xl font-semibold text-gray-900"><?php echo $stats['pendingApplications']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Jobs -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Recent Jobs</h2>
                    <a href="jobs.php" class="text-sm text-blue-600 hover:text-blue-900">View all</a>
                </div>
                <div class="border-t border-gray-200">
                    <ul class="divide-y divide-gray-200">
                        <?php if (empty($recentJobs)): ?>
                            <li class="p-4 text-center text-gray-500">No jobs posted yet</li>
                        <?php else: ?>
                            <?php foreach ($recentJobs as $job): ?>
                                <li class="p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h3>
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?php echo htmlspecialchars($job['location']); ?>
                                                <span class="mx-2">•</span>
                                                <?php echo htmlspecialchars($job['type']); ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $job['applicationCount'] > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                <?php echo $job['applicationCount']; ?> Applications
                                            </span>
                                            <?php if ($job['pendingCount'] > 0): ?>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <?php echo $job['pendingCount']; ?> Pending
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Recent Applications</h2>
                    <a href="jobs.php" class="text-sm text-blue-600 hover:text-blue-900">View all</a>
                </div>
                <div class="border-t border-gray-200">
                    <ul class="divide-y divide-gray-200">
                        <?php if (empty($recentApplications)): ?>
                            <li class="p-4 text-center text-gray-500">No applications received yet</li>
                        <?php else: ?>
                            <?php foreach ($recentApplications as $application): ?>
                                <li class="p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($application['applicantName']); ?></h3>
                                            <p class="text-sm text-gray-500">
                                                Applied for <?php echo htmlspecialchars($application['jobTitle']); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php
                                                switch($application['status']) {
                                                    case 'PENDING':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'SHORTLISTED':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'REJECTED':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    case 'HIRED':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($application['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
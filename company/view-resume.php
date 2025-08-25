<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Job.php';

// Check if user is logged in and is an HR
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if application ID is provided
if (!isset($_GET['application_id'])) {
    header("Location: jobs.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$applicationId = $_GET['application_id'];

// Get application details
$query = "SELECT a.*, j.title as jobTitle, j.companyId, u.name as applicantName, u.email as applicantEmail
          FROM Applications a
          JOIN Job j ON a.jobId = j.id
          JOIN Users u ON a.userId = u.id
          WHERE a.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $applicationId);
$stmt->execute();
$application = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify that this application belongs to the current user's company
if (!$application || $application['companyId'] !== $_SESSION['user_id']) {
    header("Location: jobs.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resume - Job Portal</title>
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
        <!-- Page Header -->
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">View Application</h1>
                    <p class="mt-2 text-gray-600">Review applicant details and resume.</p>
                </div>
                <a href="view-applications.php?job_id=<?php echo $application['jobId']; ?>" class="text-blue-600 hover:text-blue-900">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Applications
                </a>
            </div>
        </div>

        <!-- Application Details -->
        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Application for <?php echo htmlspecialchars($application['jobTitle']); ?>
                </h3>

                <!-- Applicant Information -->
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Applicant Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo htmlspecialchars($application['applicantName']); ?>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo htmlspecialchars($application['applicantEmail']); ?>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Application Status</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
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
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Applied On</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo date('M d, Y', strtotime($application['createdAt'])); ?>
                            </dd>
                        </div>
                        <?php if ($application['coverLetter']): ?>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Cover Letter</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo nl2br(htmlspecialchars($application['coverLetter'])); ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($application['resume']): ?>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Resume</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <a href="<?php echo htmlspecialchars($application['resume']); ?>"
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-download mr-2"></i>
                                    Download Resume
                                </a>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex space-x-3">
                    <a href="update-application.php?id=<?php echo $applicationId; ?>"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>
                        Update Status
                    </a>
                    <?php if ($application['applicantEmail']): ?>
                    <a href="mailto:<?php echo htmlspecialchars($application['applicantEmail']); ?>"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact Applicant
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
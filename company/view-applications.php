<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Job.php';

// Check if user is logged in and is an HR
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if job ID is provided
if (!isset($_GET['job_id'])) {
    header("Location: jobs.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$jobId = $_GET['job_id'];

// Get job details and verify ownership
$query = "SELECT j.*, c.companyName
          FROM Job j
          JOIN Company c ON j.companyId = c.id
          WHERE j.id = :jobId AND c.userId = :userId";
$stmt = $db->prepare($query);
$stmt->bindParam(":jobId", $jobId);
$stmt->bindParam(":userId", $_SESSION['user_id']);
$stmt->execute();
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: jobs.php");
    exit();
}

// Get applications for this job
$query = "SELECT a.*, u.name as applicantName, u.email as applicantEmail, u.resume as profileResume
          FROM Applications a
          JOIN Users u ON a.userId = u.id
          WHERE a.jobId = :jobId
          ORDER BY
            CASE a.status
                WHEN 'PENDING' THEN 1
                WHEN 'SHORTLISTED' THEN 2
                WHEN 'HIRED' THEN 3
                WHEN 'REJECTED' THEN 4
            END,
            a.createdAt DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":jobId", $jobId);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications - Job Portal</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Applications</h1>
                    <p class="mt-2 text-gray-600">
                        Viewing applications for: <?php echo htmlspecialchars($job['title']); ?>
                    </p>
                </div>
                <a href="jobs.php" class="text-blue-600 hover:text-blue-900">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Jobs
                </a>
            </div>
        </div>

        <!-- Applications List -->
        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
            <?php if (empty($applications)): ?>
                <div class="p-4 text-center text-gray-500">
                    No applications received yet for this job.
                </div>
            <?php else: ?>
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Applicant
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Applied On
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Resume
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($applications as $application): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($application['applicantName']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($application['applicantEmail']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($application['createdAt'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php if ($application['resume']): ?>
                                                    <a href="../<?php echo htmlspecialchars($application['resume']); ?>"
                                                       target="_blank"
                                                       class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-file-alt mr-1"></i> Application Resume
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($application['profileResume']): ?>
                                                    <?php if ($application['resume']): ?>
                                                        <span class="mx-2">|</span>
                                                    <?php endif; ?>
                                                    <a href="../<?php echo htmlspecialchars($application['profileResume']); ?>"
                                                       target="_blank"
                                                       class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-file-alt mr-1"></i> Profile Resume
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <?php if ($application['status'] === 'PENDING'): ?>
                                                    <a href="update-status.php?id=<?php echo $application['id']; ?>&action=shortlist&job_id=<?php echo $jobId; ?>"
                                                       class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                                                        <i class="fas fa-user-check mr-1"></i> Shortlist
                                                    </a>
                                                <?php elseif ($application['status'] === 'SHORTLISTED'): ?>
                                                    <a href="update-status.php?id=<?php echo $application['id']; ?>&action=hire&job_id=<?php echo $jobId; ?>"
                                                       class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-2">
                                                        <i class="fas fa-check-circle mr-1"></i> Hire
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($application['status'] !== 'HIRED' && $application['status'] !== 'REJECTED'): ?>
                                                    <a href="update-status.php?id=<?php echo $application['id']; ?>&action=reject&job_id=<?php echo $jobId; ?>"
                                                       class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-2"
                                                       onclick="return confirm('Are you sure you want to reject this application?')">
                                                        <i class="fas fa-times-circle mr-1"></i> Reject
                                                    </a>
                                                <?php endif; ?>

                                                <a href="mailto:<?php echo htmlspecialchars($application['applicantEmail']); ?>"
                                                   class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-envelope mr-1"></i> Contact
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // Handle application status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['applicationId'])) {
        $applicationId = $_POST['applicationId'];
        $action = $_POST['action'];
        $newStatus = '';

        switch($action) {
            case 'shortlist':
                $newStatus = 'SHORTLISTED';
                break;
            case 'hire':
                $newStatus = 'HIRED';
                break;
            case 'reject':
                $newStatus = 'REJECTED';
                break;
        }

        if ($newStatus) {
            $updateQuery = "UPDATE Applications SET status = :status WHERE id = :id AND jobId = :jobId";
            $stmt = $db->prepare($updateQuery);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id', $applicationId);
            $stmt->bindParam(':jobId', $jobId);

            if ($stmt->execute()) {
                header("Location: view-applications.php?job_id=" . $jobId);
                exit();
            }
        }
    }
    ?>
</body>
</html>
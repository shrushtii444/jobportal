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
if (!isset($_GET['id'])) {
    header("Location: jobs.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$applicationId = $_GET['id'];

// Get application details
$query = "SELECT a.*, j.title as jobTitle, j.companyId
          FROM Applications a
          JOIN Job j ON a.jobId = j.id
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

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['status'])) {
        $query = "UPDATE Applications SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":status", $_POST['status']);
        $stmt->bindParam(":id", $applicationId);

        if ($stmt->execute()) {
            $message = "Application status updated successfully!";
            $messageType = "success";

            // Refresh application data
            $query = "SELECT a.*, j.title as jobTitle, j.companyId
                     FROM Applications a
                     JOIN Job j ON a.jobId = j.id
                     WHERE a.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $applicationId);
            $stmt->execute();
            $application = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "Failed to update application status.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Application Status - Job Portal</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Update Application Status</h1>
                    <p class="mt-2 text-gray-600">Update the status for this application.</p>
                </div>
                <a href="view-applications.php?job_id=<?php echo $application['jobId']; ?>" class="text-blue-600 hover:text-blue-900">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Applications
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Update Status Form -->
        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Application for <?php echo htmlspecialchars($application['jobTitle']); ?>
                </h3>
                <form action="update-application.php?id=<?php echo $applicationId; ?>" method="POST" class="mt-5">
                    <div class="max-w-xl">
                        <label for="status" class="block text-sm font-medium text-gray-700">Application Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="PENDING" <?php echo $application['status'] === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                            <option value="SHORTLISTED" <?php echo $application['status'] === 'SHORTLISTED' ? 'selected' : ''; ?>>Shortlisted</option>
                            <option value="REJECTED" <?php echo $application['status'] === 'REJECTED' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="HIRED" <?php echo $application['status'] === 'HIRED' ? 'selected' : ''; ?>>Hired</option>
                        </select>
                    </div>
                    <div class="mt-5">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
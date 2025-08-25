<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Job.php';
require_once 'classes/Company.php';

if (!isset($_GET['id'])) {
    header("Location: jobs.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$job = new Job($db);

$jobId = $_GET['id'];

// Get job details
$result = $job->getJob($jobId);
$jobData = $result->fetch(PDO::FETCH_ASSOC);

if (!$jobData) {
    header("Location: jobs.php");
    exit();
}

// Check if user has already applied
$hasApplied = false;
if (isset($_SESSION['user_id'])) {
    $query = "SELECT * FROM Applications WHERE jobId = :jobId AND userId = :userId";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":jobId", $jobId);
    $stmt->bindParam(":userId", $_SESSION['user_id']);
    $stmt->execute();
    $hasApplied = $stmt->rowCount() > 0;
}

// Handle job application
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth/login.php");
        exit();
    }

    if ($_SESSION['user_role'] !== 'USER') {
        $message = "Only job seekers can apply for jobs.";
        $messageType = "error";
    } else {
        try {
            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/resumes/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $resumePath = null;

            // Handle resume upload if provided
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($_FILES['resume']['name']);
                $extension = strtolower($fileInfo['extension']);

                // Validate file type
                $allowedTypes = ['pdf', 'doc', 'docx'];
                if (!in_array($extension, $allowedTypes)) {
                    throw new Exception("Invalid file type. Only PDF, DOC, and DOCX files are allowed.");
                }

                // Generate unique filename
                $resumePath = $uploadDir . 'resume_' . uniqid() . '.' . $extension;

                // Move uploaded file
                if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resumePath)) {
                    throw new Exception("Failed to upload resume.");
                }
            }

            // Insert application
            $query = "INSERT INTO Applications (id, jobId, userId, coverLetter, resume, status)
                     VALUES (UUID(), :jobId, :userId, :coverLetter, :resume, 'PENDING')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":jobId", $jobId);
            $stmt->bindParam(":userId", $_SESSION['user_id']);
            $stmt->bindParam(":coverLetter", $_POST['coverLetter']);
            $stmt->bindParam(":resume", $resumePath);

            if ($stmt->execute()) {
                $message = "Application submitted successfully!";
                $messageType = "success";
                $hasApplied = true;
            } else {
                throw new Exception("Failed to submit application.");
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
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
    <title><?php echo htmlspecialchars($jobData['title']); ?> - Job Portal</title>
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
        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Job Details -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold leading-6 text-gray-900">
                            <?php echo htmlspecialchars($jobData['title']); ?>
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Posted by <?php echo htmlspecialchars($jobData['companyName']); ?>
                            <?php if ($jobData['isVerified']): ?>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Verified
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($jobData['companyLogo']): ?>
                        <img src="<?php echo htmlspecialchars($jobData['companyLogo']); ?>"
                             alt="<?php echo htmlspecialchars($jobData['companyName']); ?>"
                             class="h-16 w-16 object-contain">
                    <?php endif; ?>
                </div>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Job Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php echo nl2br(htmlspecialchars($jobData['description'])); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php echo htmlspecialchars($jobData['location']); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Job Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php echo htmlspecialchars($jobData['type']); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Work Mode</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php echo htmlspecialchars($jobData['workMode']); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Salary Range</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php
                            if ($jobData['salary_min'] && $jobData['salary_max']) {
                                echo '$' . number_format($jobData['salary_min']) . ' - $' . number_format($jobData['salary_max']);
                            } elseif ($jobData['salary_min']) {
                                echo 'From $' . number_format($jobData['salary_min']);
                            } elseif ($jobData['salary_max']) {
                                echo 'Up to $' . number_format($jobData['salary_max']);
                            } else {
                                echo 'Not specified';
                            }
                            ?>
                        </dd>
                    </div>
                    <?php if ($jobData['skills']): ?>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Required Skills</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (json_decode($jobData['skills']) as $skill): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($skill); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
            <?php if ($jobData['companyBio'] || $jobData['companyWebsite']): ?>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">About the Company</h4>
                <?php if ($jobData['companyBio']): ?>
                    <p class="text-sm text-gray-600 mb-4">
                        <?php echo nl2br(htmlspecialchars($jobData['companyBio'])); ?>
                    </p>
                <?php endif; ?>
                <?php if ($jobData['companyWebsite']): ?>
                    <a href="<?php echo htmlspecialchars($jobData['companyWebsite']); ?>"
                       target="_blank"
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        Visit Company Website
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (!$hasApplied && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'USER'): ?>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Apply for this Position</h4>
                <form action="job.php?id=<?php echo $jobId; ?>" method="POST" enctype="multipart/form-data">
                    <div class="space-y-6">
                        <div>
                            <label for="coverLetter" class="block text-sm font-medium text-gray-700">Cover Letter</label>
                            <div class="mt-1">
                                <textarea id="coverLetter" name="coverLetter" rows="4"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Tell us why you're a great fit for this position..."></textarea>
                            </div>
                        </div>
                        <div>
                            <label for="resume" class="block text-sm font-medium text-gray-700">Resume (Optional)</label>
                            <div class="mt-1">
                                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx"
                                    class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                                <p class="mt-1 text-sm text-gray-500">PDF, DOC, or DOCX up to 5MB</p>
                            </div>
                        </div>
                        <div>
                            <button type="submit" name="apply"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Submit Application
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php elseif ($hasApplied): ?>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                You have already applied for this position.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <p class="text-center text-sm text-gray-600">
                    Please <a href="auth/login.php" class="text-blue-600 hover:text-blue-800">log in</a> to apply for this position.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
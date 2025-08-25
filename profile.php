<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';

// Check if user is logged in and is a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'USER') {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get user details
$userData = $user->getUser($_SESSION['user_id']);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Define upload directories with absolute paths
        $uploadDir = __DIR__ . '/uploads/resumes/';

        // Create directories if they don't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory.");
            }
        }

        $resumePath = $userData['resume'];

        // Handle resume upload if a file was provided
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['resume']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validate file type
            $allowedTypes = ['pdf', 'doc', 'docx'];
            if (!in_array($extension, $allowedTypes)) {
                throw new Exception("Invalid file type. Only PDF, DOC, and DOCX files are allowed.");
            }

            // Generate unique filename
            $filename = uniqid('resume_') . '.' . $extension;
            $fullPath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $fullPath)) {
                throw new Exception("Failed to upload resume.");
            }

            // Store relative path in database
            $resumePath = 'uploads/resumes/' . $filename;
        }

        // Update user profile
        $query = "UPDATE Users SET
                name = :name,
                email = :email,
                aboutMe = :aboutMe,
                skills = :skills,
                linkedinLink = :linkedinLink,
                githubLink = :githubLink" .
                ($resumePath ? ", resume = :resume" : "") .
                " WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $_SESSION['user_id']);
        $stmt->bindParam(":name", $_POST['name']);
        $stmt->bindParam(":email", $_POST['email']);
        $stmt->bindParam(":aboutMe", $_POST['aboutMe']);
        $stmt->bindParam(":skills", $_POST['skills']);
        $stmt->bindParam(":linkedinLink", $_POST['linkedinLink']);
        $stmt->bindParam(":githubLink", $_POST['githubLink']);
        if ($resumePath) {
            $stmt->bindParam(":resume", $resumePath);
        }

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $messageType = "success";

            // Refresh user data
            $userData = $user->getUser($_SESSION['user_id']);
        } else {
            throw new Exception("Failed to update profile.");
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
    <title>Profile - Job Portal</title>
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
                        <a href="bookmarks.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Bookmarks</a>
                        <a href="profile.php" class="text-blue-600 px-3 py-2">Profile</a>
                        <a href="auth/logout.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
            <p class="mt-2 text-gray-600">Update your personal information and resume.</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Profile Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <!-- Basic Information Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>

                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name *</label>
                        <input type="text" id="name" name="name" required
                            value="<?php echo htmlspecialchars($userData['name']); ?>"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($userData['email']); ?>"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <!-- About Me -->
                    <div class="mb-6">
                        <label for="aboutMe" class="block text-gray-700 text-sm font-bold mb-2">About Me</label>
                        <textarea id="aboutMe" name="aboutMe" rows="4"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Tell us about yourself, your experience, and what you're looking for..."><?php echo htmlspecialchars($userData['aboutMe'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Skills Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Skills & Expertise</h2>

                    <!-- Skills -->
                    <div class="mb-6">
                        <label for="skills" class="block text-gray-700 text-sm font-bold mb-2">Skills</label>
                        <textarea id="skills" name="skills" rows="3"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Enter your skills (e.g., JavaScript, Python, Project Management)"><?php echo htmlspecialchars($userData['skills'] ?? ''); ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">Separate skills with commas</p>
                    </div>
                </div>

                <!-- Social Links Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Social Links</h2>

                    <!-- LinkedIn -->
                    <div class="mb-6">
                        <label for="linkedinLink" class="block text-gray-700 text-sm font-bold mb-2">LinkedIn Profile</label>
                        <input type="url" id="linkedinLink" name="linkedinLink"
                            value="<?php echo htmlspecialchars($userData['linkedinLink'] ?? ''); ?>"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://www.linkedin.com/in/your-profile">
                    </div>

                    <!-- GitHub -->
                    <div class="mb-6">
                        <label for="githubLink" class="block text-gray-700 text-sm font-bold mb-2">GitHub Profile</label>
                        <input type="url" id="githubLink" name="githubLink"
                            value="<?php echo htmlspecialchars($userData['githubLink'] ?? ''); ?>"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://github.com/your-username">
                    </div>
                </div>

                <!-- Resume Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Resume</h2>

                    <div class="mb-6">
                        <?php if ($userData['resume']): ?>
                            <div class="mb-4">
                                <a href="<?php echo htmlspecialchars($userData['resume']); ?>"
                                   target="_blank"
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-file-pdf mr-2"></i>
                                    View Current Resume
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="resume" accept=".pdf,.doc,.docx"
                            class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Upload your resume in PDF, DOC, or DOCX format.</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
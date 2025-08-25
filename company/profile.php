<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';

// Check if user is logged in and is an HR
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get HR user details
$hrUser = $user->getUser($_SESSION['user_id']);

// Get company profile if exists
$query = "SELECT * FROM Company WHERE userId = :userId LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":userId", $_SESSION['user_id']);
$stmt->execute();
$companyProfile = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $uploadDir = '../uploads/company_logos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $logoPath = $companyProfile ? $companyProfile['companyLogo'] : null;

        // Handle logo upload if a file was provided
        if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['companyLogo']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPG, JPEG, and PNG files are allowed.");
            }

            // Generate unique filename
            $logoPath = $uploadDir . uniqid('company_') . '.' . $extension;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['companyLogo']['tmp_name'], $logoPath)) {
                throw new Exception("Failed to upload company logo.");
            }
        }

        if ($companyProfile) {
            // Update existing profile
            if ($logoPath) {
                $query = "UPDATE Company SET
                        companyName = :companyName,
                        companyEmail = :companyEmail,
                        companyBio = :companyBio,
                        companyWebsite = :companyWebsite,
                        companyLogo = :companyLogo
                        WHERE userId = :userId";
            } else {
                $query = "UPDATE Company SET
                        companyName = :companyName,
                        companyEmail = :companyEmail,
                        companyBio = :companyBio,
                        companyWebsite = :companyWebsite
                        WHERE userId = :userId";
            }
        } else {
            // Create new profile - automatically verified
            $query = "INSERT INTO Company (id, userId, companyName, companyEmail, companyBio, companyWebsite, companyLogo, isVerified)
                    VALUES (UUID(), :userId, :companyName, :companyEmail, :companyBio, :companyWebsite, :companyLogo, TRUE)";
        }

        $stmt = $db->prepare($query);
        $stmt->bindParam(":userId", $_SESSION['user_id']);
        $stmt->bindParam(":companyName", $_POST['companyName']);
        $stmt->bindParam(":companyEmail", $_POST['companyEmail']);
        $stmt->bindParam(":companyBio", $_POST['companyBio']);
        $stmt->bindParam(":companyWebsite", $_POST['companyWebsite']);
        if ($logoPath) {
            $stmt->bindParam(":companyLogo", $logoPath);
        }

        if ($stmt->execute()) {
            $message = "Company profile " . ($companyProfile ? "updated" : "created") . " successfully!";
            $messageType = "success";

            // Refresh company profile data
            $stmt = $db->prepare("SELECT * FROM Company WHERE userId = :userId LIMIT 1");
            $stmt->bindParam(":userId", $_SESSION['user_id']);
            $stmt->execute();
            $companyProfile = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            throw new Exception("Failed to save company profile.");
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
    <title>Company Profile - Job Portal</title>
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
                        <a href="profile.php" class="text-blue-600 px-3 py-2">Profile</a>
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
            <h1 class="text-3xl font-bold text-gray-900">Company Profile</h1>
            <p class="mt-2 text-gray-600">Manage your company information and branding.</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Profile Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <!-- Company Logo -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Company Logo</label>
                    <?php if ($companyProfile && $companyProfile['companyLogo']): ?>
                        <div class="mb-4">
                            <img src="<?php echo htmlspecialchars($companyProfile['companyLogo']); ?>"
                                alt="Company Logo"
                                class="h-32 w-32 object-contain border rounded">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="companyLogo" accept="image/jpeg,image/png"
                        class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500">Upload a square logo in JPG or PNG format.</p>
                </div>

                <!-- Company Name -->
                <div class="mb-6">
                    <label for="companyName" class="block text-gray-700 text-sm font-bold mb-2">Company Name *</label>
                    <input type="text" id="companyName" name="companyName" required
                        value="<?php echo $companyProfile ? htmlspecialchars($companyProfile['companyName']) : ''; ?>"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <!-- Company Email -->
                <div class="mb-6">
                    <label for="companyEmail" class="block text-gray-700 text-sm font-bold mb-2">Company Email *</label>
                    <input type="email" id="companyEmail" name="companyEmail" required
                        value="<?php echo $companyProfile ? htmlspecialchars($companyProfile['companyEmail']) : ''; ?>"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <!-- Company Website -->
                <div class="mb-6">
                    <label for="companyWebsite" class="block text-gray-700 text-sm font-bold mb-2">Company Website</label>
                    <input type="url" id="companyWebsite" name="companyWebsite"
                        value="<?php echo $companyProfile ? htmlspecialchars($companyProfile['companyWebsite']) : ''; ?>"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="https://">
                </div>

                <!-- Company Bio -->
                <div class="mb-6">
                    <label for="companyBio" class="block text-gray-700 text-sm font-bold mb-2">Company Bio *</label>
                    <textarea id="companyBio" name="companyBio" required rows="5"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Tell us about your company..."><?php echo $companyProfile ? htmlspecialchars($companyProfile['companyBio']) : ''; ?></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <?php echo $companyProfile ? 'Update Profile' : 'Create Profile'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
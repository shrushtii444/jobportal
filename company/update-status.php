<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an HR
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if all required parameters are present
if (!isset($_GET['id']) || !isset($_GET['action']) || !isset($_GET['job_id'])) {
    header("Location: jobs.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$applicationId = $_GET['id'];
$action = $_GET['action'];
$jobId = $_GET['job_id'];

// Verify that this application belongs to the current user's company
$query = "SELECT a.*, j.companyId
          FROM Applications a
          JOIN Job j ON a.jobId = j.id
          JOIN Company c ON j.companyId = c.id
          WHERE a.id = :id AND c.userId = :userId";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $applicationId);
$stmt->bindParam(":userId", $_SESSION['user_id']);
$stmt->execute();
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    header("Location: jobs.php");
    exit();
}

// Update application status
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
    default:
        header("Location: view-applications.php?job_id=" . $jobId);
        exit();
}

try {
    $updateQuery = "UPDATE Applications SET status = :status WHERE id = :id";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':status', $newStatus);
    $stmt->bindParam(':id', $applicationId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Application status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update application status.";
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
}

header("Location: view-applications.php?job_id=" . $jobId);
exit();
?>
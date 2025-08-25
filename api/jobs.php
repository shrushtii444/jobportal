<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Check if required files exist
    if (!file_exists('../config/database.php')) {
        throw new Exception('Database configuration file not found');
    }
    if (!file_exists('../classes/Job.php')) {
        throw new Exception('Job class file not found');
    }
    if (!file_exists('../classes/Company.php')) {
        throw new Exception('Company class file not found');
    }

    require_once '../config/database.php';
    require_once '../classes/Job.php';
    require_once '../classes/Company.php';

    // Test database connection
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Failed to establish database connection');
    }

    $job = new Job($db);

    // Get query parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $_GET['search'] : "";
    $type = isset($_GET['type']) ? $_GET['type'] : "";
    $workMode = isset($_GET['workMode']) ? $_GET['workMode'] : "";
    $minSalary = isset($_GET['minSalary']) ? (float)$_GET['minSalary'] : 0;

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Get jobs
    $stmt = $job->getActiveJobs($limit, $offset, $search, $type, $workMode, $minSalary);

    if (!$stmt) {
        throw new Exception('Failed to execute job query');
    }

    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $totalJobs = $job->countActiveJobs($search, $type, $workMode, $minSalary);

    // Calculate total pages
    $totalPages = ceil($totalJobs / $limit);

    // Prepare response
    $response = [
        'status' => 'success',
        'data' => [
            'jobs' => $jobs,
            'pagination' => [
                'total' => $totalJobs,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'limit' => $limit
            ]
        ]
    ];

    echo json_encode($response);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load jobs: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
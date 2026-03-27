<?php

require_once __DIR__ . '/../app/Config/Config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Require admin session for all log operations
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Admin authentication required']);
    exit();
}

try {
    $database = new Database();
    $db       = $database->connect();
    $logger   = new ActivityLog($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? 'read';

switch ($action) {
    case 'read':
        $filters = [
            'module'    => $_GET['module']    ?? '',
            'action'    => $_GET['log_action'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
            'search'    => $_GET['search']    ?? '',
        ];

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $logs  = $logger->getAll($filters, $perPage, $offset);
        $total = $logger->count($filters);

        echo json_encode([
            'success'    => true,
            'data'       => $logs,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'total_pages' => ceil($total / $perPage),
        ]);
        break;

    case 'filters':
        echo json_encode([
            'success' => true,
            'modules' => $logger->getModules(),
            'actions' => $logger->getActions(),
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>

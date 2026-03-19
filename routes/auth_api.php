<?php

require_once __DIR__ . '/../app/Config/Config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get request data
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Route requests
switch ($action) {
    case 'login':
        if ($method === 'POST') {
            login($data);
        }
        break;
        
    case 'logout':
        if ($method === 'GET') {
            logoutRedirect();
        } else {
            logout();
        }
        break;
        
    case 'check':
        checkAuth();
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Login function
 */
function login($data) {
    if (empty($data['username']) || empty($data['password']) || empty($data['user_type'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username, password, and user type are required'
        ]);
        return;
    }

    $username = $data['username'];
    $password = $data['password'];
    $user_type = $data['user_type'];

    // Demo credentials (replace with database check in production)
    $valid_credentials = [
        'admin' => [
            'username' => 'admin',
            'password' => 'admin123',
            'fullname' => 'System Administrator'
        ],
        'user' => [
            'username' => 'user',
            'password' => 'user123',
            'fullname' => 'Guest User'
        ]
    ];

    // Check credentials
    if (isset($valid_credentials[$user_type]) &&
        $valid_credentials[$user_type]['username'] === $username &&
        $valid_credentials[$user_type]['password'] === $password) {
        
        // Set session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['username'] = $username;
        $_SESSION['fullname'] = $valid_credentials[$user_type]['fullname'];
        $_SESSION['login_time'] = time();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'username' => $username,
                'user_type' => $user_type,
                'fullname' => $valid_credentials[$user_type]['fullname']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid username or password'
        ]);
    }
}

/**
 * Logout function (JSON response)
 */
function logout() {
    session_unset();
    session_destroy();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

/**
 * Logout with redirect (for direct link clicks)
 */
function logoutRedirect() {
    session_unset();
    session_destroy();
    header('Location: ../public/login.php');
    exit();
}

/**
 * Check authentication status
 */
function checkAuth() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'username' => $_SESSION['username'],
                'user_type' => $_SESSION['user_type'],
                'fullname' => $_SESSION['fullname']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'authenticated' => false
        ]);
    }
}

?>

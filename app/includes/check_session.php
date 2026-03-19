<?php
/**
 * Session Check - Include this at the top of protected pages
 * 
 * Usage:
 * require_once __DIR__ . '/../../app/includes/check_session.php';
 * checkSession('admin'); // or checkSession('user');
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkSession($required_role = null) {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ../login.php');
        exit();
    }

    // Check if specific role is required
    if ($required_role !== null && $_SESSION['user_type'] !== $required_role) {
        // Redirect to appropriate dashboard
        if ($_SESSION['user_type'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../user/dashboard.php');
        }
        exit();
    }

    // Session is valid
    return true;
}

function getSessionUser() {
    return [
        'username' => $_SESSION['username'] ?? '',
        'fullname' => $_SESSION['fullname'] ?? '',
        'user_type' => $_SESSION['user_type'] ?? ''
    ];
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isUser() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'user';
}
?>

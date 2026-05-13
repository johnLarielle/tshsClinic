<?php

// Error display: off in production, on in local dev
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']);
error_reporting($isLocal ? E_ALL : 0);
ini_set('display_errors', $isLocal ? 1 : 0);

// Set timezone
date_default_timezone_set('Asia/Manila');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Autoloader for classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/Models/' . $class . '.php',
        BASE_PATH . '/app/Controllers/' . $class . '.php',
        BASE_PATH . '/app/Config/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

?>
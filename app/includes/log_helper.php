<?php
/**
 * writeLog()
 * Convenience wrapper — call this anywhere a DB connection is available.
 *
 * @param PDO    $db
 * @param string $action       Uppercase constant, e.g. 'ADD_RECORD'
 * @param string $module       'Patient Records' | 'Medicine' | 'Auth'
 * @param string $description  Plain-English summary shown in the log table
 */
function writeLog($db, $action, $module, $description) {
    try {
        // Pull from session (safe defaults if called outside a session)
        $username = $_SESSION['username'] ?? 'system';
        $fullname = $_SESSION['fullname'] ?? 'System';

        $logger = new ActivityLog($db);
        $logger->log($action, $module, $description, $username, $fullname);
    } catch (Exception $e) {
        // Never let a failed log kill the main request
        error_log('ActivityLog write failed: ' . $e->getMessage());
    }
}
?>

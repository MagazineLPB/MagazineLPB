<?php
/**
 * Admin Authentication Guard
 * Include this file at the top of any admin-only page
 * Verifies session and redirects to login if not authenticated
 */

require_once __DIR__ . '/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Optional: Check session timeout (2 hours from login)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_LIFETIME)) {
    // Session expired
    session_destroy();
    header('Location: login.php');
    exit;
}

// Refresh login time on activity (sliding session)
$_SESSION['login_time'] = time();
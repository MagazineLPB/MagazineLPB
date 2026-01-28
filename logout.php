<?php
/**
 * Logout Script
 * Destroys session and redirects to login page
 */

require_once __DIR__ . '/../includes/config.php';

// Start session
session_name(SESSION_NAME);
session_start();

// Destroy session data
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[SESSION_NAME])) {
    setcookie(SESSION_NAME, '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
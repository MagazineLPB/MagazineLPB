<?php
/**
 * Configuration File
 * Contains application constants and settings
 */

// Database configuration
define('DB_PATH', __DIR__ . '/../database/magazine.db');

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/articles/');
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024); // 2MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Session configuration
define('SESSION_NAME', 'magazine_admin');
define('SESSION_LIFETIME', 3600 * 2); // 2 hours

// Application settings
define('ARTICLES_PER_PAGE', 12);
define('EXCERPT_LENGTH', 200);

// Timezone
date_default_timezone_set('UTC');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
<?php

// Router script for PHP's built-in server to handle CORS for static files
// This ensures upload requests go through Laravel to get CORS headers

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Route upload requests through Laravel for CORS headers
if (strpos($uri, '/uploads/') === 0) {
    // Always route through Laravel for uploads to get CORS headers
    // Ensure REQUEST_URI is preserved for Laravel routing
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    chdir(__DIR__);
    require __DIR__ . '/index.php';
    exit;
}

// For all other static files, serve directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve the file directly
}

// Everything else goes through Laravel
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
chdir(__DIR__);
require __DIR__ . '/index.php';


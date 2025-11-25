<?php
// Router for PHP built-in server
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$requestPath = ltrim($requestPath, '/');

// If root or empty, show landing page
if ($requestPath === '' || $requestPath === '/' || $requestPath === 'index.php') {
    if (file_exists('index.php')) {
        require 'index.php';
        return true;
    }
}

// If file exists, serve it
if ($requestPath !== '' && file_exists($requestPath)) {
    return false; // Let PHP serve the file
}

// If it's a directory, try index.php
if (is_dir($requestPath) && file_exists($requestPath . '/index.php')) {
    require $requestPath . '/index.php';
    return true;
}

// 404 - file not found
http_response_code(404);
echo "404 - File not found";
return true;


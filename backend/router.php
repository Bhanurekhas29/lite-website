<?php
// Router for PHP's built-in server (development only):
//   php -S localhost:8000 -t backend backend/router.php
// On Apache (production), .htaccess does this job.

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Serve uploaded images directly from the media folder (image types only).
if (str_starts_with($path, '/media/') && is_file(__DIR__ . $path)) {
    $types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (isset($types[$ext])) {
        header('Content-Type: ' . $types[$ext]);
        header('X-Content-Type-Options: nosniff');
        readfile(__DIR__ . $path);
        return true;
    }
    http_response_code(404);
    return true;
}

require __DIR__ . '/index.php';

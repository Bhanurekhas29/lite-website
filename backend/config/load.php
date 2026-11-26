<?php
// Loads the settings. On your own computer they come from config.php (never shared).
// On a hosting service they come from environment variables instead, so no secret is stored in the code.
$file = __DIR__ . '/config.php';
if (is_file($file)) {
    return require $file;
}

$env = static fn (string $name, string $default = ''): string => (string) (getenv($name) !== false ? getenv($name) : $default);

return [
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => (int) $env('DB_PORT', '3306'),
        'name' => $env('DB_NAME', 'lite_events'),
        'user' => $env('DB_USER', 'root'),
        'pass' => $env('DB_PASS', ''),
        // Cloud databases such as TiDB Cloud only accept encrypted connections.
        'ssl' => in_array(strtolower($env('DB_SSL', '')), ['1', 'true', 'yes', 'on'], true),
    ],
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', $env('ALLOWED_ORIGINS', ''))))),
    'app_key' => $env('APP_KEY'),
    // The public address of the site (used in password reset emails). Optional.
    'public_url' => $env('PUBLIC_URL'),
];

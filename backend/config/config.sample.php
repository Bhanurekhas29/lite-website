<?php
// Copy this file to config.php and fill in the real values.
// config.php is excluded from version control and must never be shared.
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'lite_events',
        'user' => 'root',
        'pass' => '',
    ],
    // Origins allowed to call the API during development (Vite dev server).
    'allowed_origins' => ['http://localhost:5173'],

    // Encrypts secrets stored in the database (for example the SMTP password).
    // Generate one with: php -r "echo base64_encode(random_bytes(32));"
    'app_key' => '',

    // Used only by database/install.php. Remove this block after installing.
    'seed' => [
        'admin_username' => 'admin',
        'admin_email'    => '',
        'admin_password' => '',
        'admin_name'     => 'Super Admin',
    ],
];

<?php
declare(strict_types=1);

// One-time installer. Run from the command line:
//   php backend/database/install.php
// Creates the database, runs schema.sql, and adds starter data.
// It refuses to run if the database already has tables.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this script from the command line only.');
}

$config = require __DIR__ . '/../config/config.php';
$db = $config['db'];
$seed = $config['seed'] ?? null;
if (!$seed || $seed['admin_password'] === '') {
    exit("Missing 'seed' settings in config.php\n");
}

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
$server = new PDO(sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']), $db['user'], $db['pass'], $options);

$name = $db['name'];
if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
    exit("Invalid database name\n");
}

$exists = $server->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
$exists->execute([$name]);
if ((int) $exists->fetchColumn() > 0) {
    exit("Database '$name' already has tables. Nothing was changed.\n");
}

$server->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $name), $db['user'], $db['pass'], $options);
$pdo->exec("SET time_zone = '+00:00'");

// Schema: run each statement separately.
$sql = file_get_contents(__DIR__ . '/schema.sql');
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    $pdo->exec($statement);
}
echo "Schema created.\n";

// Super Admin (password is stored as a hash only).
$pdo->prepare('INSERT INTO admins (username, email, password_hash, display_name, is_super, is_active) VALUES (?, ?, ?, ?, 1, 1)')
    ->execute([
        $seed['admin_username'],
        $seed['admin_email'] !== '' ? $seed['admin_email'] : null,
        password_hash($seed['admin_password'], PASSWORD_DEFAULT),
        $seed['admin_name'],
    ]);
echo "Super Admin created.\n";

// The 8 pages, in menu order. Home is locked (cannot be hidden).
$pages = [
    // title, menu label, footer label, slug, locked, show in footer
    ['Home', 'Home', null, 'home', 1, 1],
    ['About', 'About', null, 'about', 0, 1],
    ['Concert Partnerships', 'Concert Partnerships', null, 'concert-partnerships', 0, 1],
    ['Hindu & Tamil Weddings', 'Hindu & Tamil Weddings', null, 'hindu-tamil-weddings', 0, 1],
    ['Event Solutions', 'Event Solutions', 'Services', 'event-solutions', 0, 1],
    ['Sports Events & Hospitality', 'Sports Events & Hospitality', null, 'sports-events-hospitality', 0, 0],
    ['Our Work', 'Our Work', null, 'our-work', 0, 1],
    ['Contact', 'Contact', null, 'contact', 0, 1],
];
$ins = $pdo->prepare('INSERT INTO pages (title, menu_label, footer_label, slug, is_locked, show_in_header, show_in_footer, sort_order, seo_title) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?)');
foreach ($pages as $i => [$title, $menu, $footer, $slug, $locked, $inFooter]) {
    $ins->execute([$title, $menu, $footer, $slug, $locked, $inFooter, $i + 1, $title . ' | E for Events']);
}
echo count($pages) . " pages created.\n";

// Starter settings (all editable later in the admin panel).
$settings = [
    ['brand', 'website_name', 'E for Events'],
    ['brand', 'company_name', ''],
    ['brand', 'credits_enabled', '1'],
    ['brand', 'credits_text', 'Designed and Developed by vetriitsystems.com'],
    ['brand', 'credits_url', 'https://vetriitsystems.com'],
    ['brand', 'footer_tagline', 'CONCERTS • EVENTS • EXPERIENCES'],
    ['brand', 'footer_description', 'Bringing people, artists and communities together through memorable experiences.'],
    ['brand', 'footer_copyright', '© E for Events. All rights reserved.'],
    ['brand', 'footer_right_text', 'Music • Culture • Community • Together'],
    ['brand', 'header_button_label', 'Enquire Now'],
    ['brand', 'header_button_page_id', (string) $pdo->query("SELECT id FROM pages WHERE slug = 'contact'")->fetchColumn()],
    ['brand', 'favicon', ''],
    ['brand', 'logo_header', ''],
    ['brand', 'logo_footer', ''],
    ['contact', 'email', 'sat@jwala.uk'],
    ['contact', 'website', 'www.eforevents.uk'],
    ['contact', 'phone', ''],
    ['contact', 'phone_note', ''],
    ['contact', 'address', ''],
    ['social', 'instagram', '@eforeventsuk'],
    ['social', 'facebook', ''],
    ['social', 'linkedin', ''],
    ['social', 'whatsapp', ''],
    ['general', 'timezone', 'Europe/London'],
    ['seo', 'meta_title', 'E for Events'],
    ['seo', 'meta_description', ''],
    ['seo', 'keywords_primary', ''],
    ['seo', 'keywords_secondary', ''],
    ['email', 'smtp_host', ''],
    ['email', 'smtp_port', '587'],
    ['email', 'smtp_username', ''],
    ['email', 'smtp_password', ''],
    ['email', 'smtp_encryption', 'tls'],
    ['email', 'sender_name', 'E for Events'],
    ['email', 'sender_email', ''],
    ['email', 'enquiry_recipient', ''],
];
$ins = $pdo->prepare('INSERT INTO settings (group_name, setting_key, value_text, is_encrypted) VALUES (?, ?, ?, ?)');
foreach ($settings as [$group, $key, $value]) {
    $ins->execute([$group, $key, $value, $key === 'smtp_password' ? 1 : 0]);
}
echo count($settings) . " settings created.\n";

// Sample enquiry form choices (replace in the admin panel).
$options = [
    'enquiry_type' => ['Concert or live entertainment', 'Partnership enquiry', 'Wedding', 'Corporate event', 'Sports or hospitality', 'Other'],
    'attendance' => ['Under 50', '50 to 100', '100 to 250', '250 to 500', '500 to 1,000', 'Over 1,000'],
    'services' => ['Concert or live entertainment', 'Partnership with event company', 'Full event management', 'Hindu or Tamil wedding', 'Corporate event or conference', 'Sports, cultural or community event', 'Specific service or subcontracting requirement', 'Other'],
];
$ins = $pdo->prepare('INSERT INTO form_options (field_key, label, sort_order) VALUES (?, ?, ?)');
$count = 0;
foreach ($options as $field => $labels) {
    foreach ($labels as $i => $label) {
        $ins->execute([$field, $label, $i + 1]);
        $count++;
    }
}
echo "$count form options created.\n";
echo "Done.\n";

<?php
declare(strict_types=1);
// One-time: adds the short description and picture description to projects.
// Run: php backend/database/migrate_project_summary.php (safe to run again)
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/load.php';
$pdo = Database::connect($config['db']);
foreach (['summary' => 'VARCHAR(500) NULL', 'image_alt' => 'VARCHAR(255) NULL'] as $col => $def) {
    $has = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $has->execute(['projects', $col]);
    if (!(int) $has->fetchColumn()) { $pdo->exec("ALTER TABLE projects ADD COLUMN $col $def AFTER our_role"); echo "Added projects.$col\n"; }
}
echo "projects is ready.\n";

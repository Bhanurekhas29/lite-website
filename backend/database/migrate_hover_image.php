<?php
declare(strict_types=1);
// One-time: adds the project hover image column. Run: php backend/database/migrate_hover_image.php
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/config.php';
$pdo = Database::connect($config['db']);
$has = $pdo->query("SHOW COLUMNS FROM projects LIKE 'hover_media_id'")->fetch();
if ($has) { exit("Already done.\n"); }
$pdo->exec('ALTER TABLE projects ADD COLUMN hover_media_id INT UNSIGNED NULL AFTER image_media_id,
  ADD CONSTRAINT fk_projects_hover FOREIGN KEY (hover_media_id) REFERENCES media (id) ON DELETE RESTRICT');
echo "Added hover_media_id.\n";

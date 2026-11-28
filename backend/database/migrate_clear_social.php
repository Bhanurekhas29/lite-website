<?php
declare(strict_types=1);
// One-time: empties the Instagram handle that came from the drafting documents, so the client adds
// their own social links in Admin > Settings > Contact and social. Safe to run on every start.
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/load.php';
$pdo = Database::connect($config['db']);

$marker = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE group_name = 'system' AND setting_key = 'patch_clear_social_1'");
$marker->execute();
if ((int) $marker->fetchColumn() > 0) { echo "Social links already cleared.\n"; exit(0); }

// Only the handle that was set from the documents is cleared; anything else the client typed stays.
$pdo->prepare("UPDATE settings SET value_text = '' WHERE group_name = 'social' AND setting_key = 'instagram' AND value_text = '@eforeventsuk'")->execute();
$pdo->prepare("INSERT INTO settings (group_name, setting_key, value_text, is_encrypted) VALUES ('system', 'patch_clear_social_1', '1', 0)")->execute();
echo "Social links cleared.\n";

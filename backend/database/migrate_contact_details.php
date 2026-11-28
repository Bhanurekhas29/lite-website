<?php
declare(strict_types=1);
// One-time: fills in the phone number, Facebook and Instagram links given by the client.
// Only fields that are still empty are set, so anything edited in the admin is never overwritten.
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/load.php';
$pdo = Database::connect($config['db']);

$marker = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE group_name = 'system' AND setting_key = 'patch_contact_details_1'");
$marker->execute();
if ((int) $marker->fetchColumn() > 0) { echo "Contact details already applied.\n"; exit(0); }

$values = [
    ['contact', 'phone', '07940173183'],
    ['social', 'facebook', 'https://www.facebook.com/eforeventsuk'],
    ['social', 'instagram', 'https://www.instagram.com/eforeventsuk/'],
];
$get = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE group_name = ? AND setting_key = ?');
$upd = $pdo->prepare("UPDATE settings SET value_text = ? WHERE group_name = ? AND setting_key = ? AND (value_text IS NULL OR value_text = '')");
$ins = $pdo->prepare('INSERT INTO settings (group_name, setting_key, value_text, is_encrypted) VALUES (?, ?, ?, 0)');
foreach ($values as [$g, $k, $v]) {
    $get->execute([$g, $k]);
    if ((int) $get->fetchColumn() > 0) { $upd->execute([$v, $g, $k]); } else { $ins->execute([$g, $k, $v]); }
}
$ins->execute(['system', 'patch_contact_details_1', '1']);
echo "Contact details applied.\n";

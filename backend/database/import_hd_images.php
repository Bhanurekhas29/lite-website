<?php
declare(strict_types=1);

// One-time import of the high-resolution event photos (files named 20.webp to 27.webp).
//   php backend/database/import_hd_images.php "C:\path\to\folder"
// Exact duplicates are skipped. Images are kept at up to 2000 px wide, converted to WebP,
// and given a web-safe name and alt text written from what each photo shows.

if (PHP_SAPI !== 'cli') {
    exit('Run this script from the command line only.');
}

foreach (['Response', 'HttpException', 'Database', 'PageService', 'MediaService'] as $file) {
    require __DIR__ . '/../src/' . $file . '.php';
}
$config = require __DIR__ . '/../config/config.php';
$pdo = Database::connect($config['db']);

$folder = rtrim($argv[1] ?? '', '\\/');
if (!is_dir($folder)) {
    exit("Folder not found: $folder\n");
}
$adminId = (int) $pdo->query('SELECT id FROM admins WHERE is_super = 1 LIMIT 1')->fetchColumn();

// File => [new file name, alt text]
$images = [
    '20.webp' => ['concert-stage-flames-crowd-hands-raised', 'Rock concert with stage flames and a crowd with hands raised'],
    '21.webp' => ['hindu-tamil-wedding-couple-sacred-fire-mandap', 'Hindu and Tamil wedding couple holding hands at a flower-covered mandap beside the sacred fire'],
    '22.webp' => ['ballroom-pink-lighting-flower-chandeliers-band', 'Ballroom with pink lighting, flower chandeliers and a band playing for dining guests'],
    '23.webp' => ['pink-faded-concert-stage-singer-band', 'Concert stage with a singer and band in soft pink light, faded on the left'],
    '24.webp' => ['gala-dinner-ballroom-stage-screen', 'Gala dinner in a chandelier ballroom with a large stage screen'],
    '25.webp' => ['stadium-hospitality-suite-sunset-faded', 'Stadium hospitality suite with a set table overlooking the pitch at sunset'],
    '26.webp' => ['arena-concert-stage-led-screens-crowd', 'Arena concert with LED screens, stage lighting and a large crowd'],
    '27.webp' => ['gala-dinner-ballroom-stage-screen-copy', 'Gala dinner in a chandelier ballroom with a large stage screen'],
];

$seen = [];
foreach ($images as $file => [$slug, $alt]) {
    $path = $folder . DIRECTORY_SEPARATOR . $file;
    if (!is_file($path)) {
        echo "MISSING  $file\n";
        continue;
    }
    $hash = md5_file($path);
    if (isset($seen[$hash])) {
        echo "SKIPPED  $file (same picture as {$seen[$hash]})\n";
        continue;
    }
    $seen[$hash] = $file;

    // Copy under its new name so the library shows a meaningful original file name.
    $named = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $slug . '.webp';
    copy($path, $named);
    $exists = $pdo->prepare('SELECT 1 FROM media WHERE file_name = ?');
    $exists->execute([$slug . '.webp']);
    if ($exists->fetchColumn()) {
        echo "ALREADY  $slug\n";
        unlink($named);
        continue;
    }
    $media = MediaService::importLocal($pdo, $named, $slug, $alt, $adminId, 2000);
    unlink($named);
    printf("%-52s %4dx%-4d %4d KB\n", basename($media['url']), $media['width'], $media['height'], $media['size_bytes'] / 1024);
}
echo "Done.\n";

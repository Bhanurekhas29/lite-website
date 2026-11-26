<?php
declare(strict_types=1);

// One-time import of the supplied event photos into the media library.
//   php backend/database/import_images.php "C:\path\to\folder"
// Originals are never changed. Each image is converted to WebP (not enlarged)
// and given a web-safe name and alt text. Already-imported files are skipped.
//
// The supplied file names did not describe the pictures, so the new name and
// alt text below were written from what each photo actually shows.

if (PHP_SAPI !== 'cli') {
    exit('Run this script from the command line only.');
}

foreach (['Response', 'HttpException', 'Database', 'PageService', 'MediaService'] as $file) {
    require __DIR__ . '/../src/' . $file . '.php';
}
$config = require __DIR__ . '/../config/config.php';
$pdo = Database::connect($config['db']);

$folder = $argv[1] ?? '';
if (!is_dir($folder)) {
    exit("Folder not found: $folder\n");
}
$adminId = (int) $pdo->query('SELECT id FROM admins WHERE is_super = 1 LIMIT 1')->fetchColumn();

// Images chosen for import (text-baked banners and tiny thumbnails are left out).
// Original file name => [new file name, alt text]
$images = [
    'Live concert atmosphere with stage lights and laser beams.png' => ['concert-stage-lights-large-crowd', 'Concert stage with bright lights above a large crowd'],
    'Concert crowd silhouette.png' => ['faded-seated-audience-background', 'Softly faded view of a seated audience'],
    'Concert stage with vibrant lighting and cheering crowd.png' => ['pink-faded-concert-stage-background', 'Concert stage with pink lighting, softly faded'],
    'Audience silhouette in concert hall.png' => ['red-theatre-stage-band-wide', 'Theatre with red seating and a band on stage under purple lights'],
    'Opulent floral mandap backdrop at wedding venue.png' => ['floral-wedding-hall-mandap-wide', 'Wedding hall with a large floral mandap and chandeliers'],
    'Opulent luxury banquet hall with pink mood lighting and floral chandeliers.png' => ['pink-lit-ballroom-chandeliers-gala-dinner', 'Grand ballroom with chandeliers and pink lighting set for a gala dinner'],
    'Luxury grand illuminated event banquet ballroom with pink lighting.png' => ['audience-glowing-phone-lights', 'Audience holding up glowing phone lights at an event'],
    'Luxury gala dinner venue in a prestigious London ballroom with chandeliers.png' => ['gala-dinner-speaker-presentation', 'Speaker presenting to guests at the Indian Team Gala Dinner'],
    'Concert audience with confetti and lights.png' => ['guests-laughing-talking-event', 'Guests laughing and talking at an event'],
    'Traditional Hindu & Tamil wedding couple seated at an ornate floral mandap.png' => ['hindu-tamil-wedding-couple-floral-mandap', 'Hindu and Tamil wedding couple seated at a floral mandap'],
    'Soccer ball resting on green soccer field ready for match.png' => ['group-photo-event-attendees', 'Group photo of event attendees standing and seated in a conference room'],
    'Live concert production with crowd hands in air and stage lights.png' => ['concert-crowd-hands-raised-stage', 'Crowd with hands raised in front of a large concert stage'],
    'Fine dining event dinner arrangement.png' => ['four-men-posing-together', 'Four men posing together at an event'],
    'Traditional brass Diya oil lamp surrounded by vibrant floral garland and warm wedding lights.png' => ['brass-diya-flower-garlands-wedding', 'Brass diya lamp surrounded by flower garlands at a wedding'],
    'Soccer ball resting on the grass pitch inside a packed stadium.png' => ['sports-trophies-cups-table', 'Table of sports trophies and cups'],
    'Live concert production with band performing under stage lights.png' => ['theatre-red-seating-band-on-stage', 'Theatre with red seating and a band performing on stage'],
    'Spectators in elevated stadium hospitality terrace overlooking the lit field.png' => ['badminton-game-sports-hall', 'People playing badminton in a sports hall'],
    'Community Cultural Festival dancers and staging.png' => ['community-arts-venue-collage', 'Collage of a community arts venue exterior, stage and seating'],
    'Live Music Concert Arena UK.png' => ['live-band-stage-drums-keyboards', 'Live band with drums and keyboards on a purple-lit stage'],
    'Business Conference in London auditorium.png' => ['business-conference-round-tables', 'Delegates seated at round tables at a business conference'],
    'Luxury wedding mandap decorated with florals.png' => ['wedding-table-fruit-decorations', 'Wedding table with fruit and traditional decorations'],
    'Concert stage lights and crowd.png' => ['seated-audience-watching-performance', 'Seated audience watching a performance in a hall'],
    'Corporate banquet ballroom stage presentation.png' => ['men-posing-beside-event-banner', 'Group of men posing beside an event banner'],
    'Traditional Hindu and Tamil Wedding ceremony.png' => ['hindu-tamil-wedding-fire-ritual', 'Hindu and Tamil wedding ceremony with the couple at the sacred fire'],
    'Corporate Conference hall with delegates.png' => ['group-photo-event-attendees-small', 'Group photo of event attendees on a patterned carpet'],
    'Container.png' => ['hindu-tamil-wedding-fire-ritual-small', 'Hindu and Tamil wedding ceremony with the couple at the sacred fire'],
    'Cultural and community festivities performance.png' => ['community-arts-venue-collage-small', 'Collage of a community arts venue exterior, stage and seating'],
    'Sound Engineer at Audio Mixing Console.png' => ['musical-instruments-collage-magenta-lights', 'Collage of musical instruments and drums under magenta lighting'],
    'London Eye and Thames skyline at sunset.png' => ['stage-lighting-concert-poster-collage', 'Collage of stage lighting, a concert poster and empty auditoriums'],
    'Concert Stage and Spectators.png' => ['red-theatre-stage-view', 'Theatre with red seating and a stage lit in purple'],
    'Elegant wedding dining table arrangement with candles and pink flowers.png' => ['audience-seated-hall-wide', 'Audience seated in a hall watching a show'],
    'Image (Concert stage).png' => ['audience-seated-hall-portrait', 'Audience seated and applauding in a hall'],
];

$exists = $pdo->prepare('SELECT 1 FROM media WHERE file_name = ?');
$before = $after = $done = $skipped = 0;
foreach ($images as $name => [$slug, $alt]) {
    $path = rtrim($folder, '\\/') . DIRECTORY_SEPARATOR . $name;
    if (!is_file($path)) {
        echo "MISSING  $name\n";
        continue;
    }
    $exists->execute([$name]);
    if ($exists->fetchColumn()) {
        $skipped++;
        continue;
    }
    $media = MediaService::importLocal($pdo, $path, $slug, $alt, $adminId);
    $before += filesize($path);
    $after += $media['size_bytes'];
    $done++;
    printf("%-58s %4dx%-4d %4d KB\n", basename($media['url']), $media['width'], $media['height'], $media['size_bytes'] / 1024);
}
printf("\nImported %d, skipped %d already imported.\n", $done, $skipped);
if ($done) {
    printf("Size: %.1f MB as PNG -> %.1f MB as WebP.\n", $before / 1048576, $after / 1048576);
}

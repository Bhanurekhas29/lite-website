<?php
declare(strict_types=1);
// One-time image updates for the live site (About, Home, Event Solutions, Concert Partnerships,
// Sports). Safe to run on every start: it does nothing once the first new image is registered, so
// later edits made in the admin are never overwritten.
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/load.php';
$pdo = Database::connect($config['db']);

$media = 
[
  56 => 
  array (
    'file_name' => 'about-banquet-hall-pink-lighting.webp',
    'file_path' => 'uploads/2026/11/about-banquet-hall-pink-lighting-226eca.webp',
    'alt_text' => 'Banquet hall with round tables and chairs set for a gala dinner under pink and purple lighting',
    'mime_type' => 'image/webp',
    'size_bytes' => 256442,
    'width' => 1600,
    'height' => 900,
  ),
  58 => 
  array (
    'file_name' => 'corporate-business-talk-audience.webp',
    'file_path' => 'uploads/2026/11/corporate-business-talk-audience-22576e.webp',
    'alt_text' => 'Speaker with a microphone addressing a seated audience in suits at a corporate event',
    'mime_type' => 'image/webp',
    'size_bytes' => 305086,
    'width' => 1600,
    'height' => 1068,
  ),
  59 => 
  array (
    'file_name' => 'live-entertainment-audience-stage.webp',
    'file_path' => 'uploads/2026/11/live-entertainment-audience-stage-5070f7.webp',
    'alt_text' => 'Two performers on stage facing a large smiling audience in a theatre hall',
    'mime_type' => 'image/webp',
    'size_bytes' => 330586,
    'width' => 1600,
    'height' => 1031,
  ),
  60 => 
  array (
    'file_name' => 'artist-guest-logistics-live-stage.webp',
    'file_path' => 'uploads/2026/11/artist-guest-logistics-live-stage-7cb6ee.webp',
    'alt_text' => 'Live band performing on a purple-lit stage beneath a large LED screen',
    'mime_type' => 'image/webp',
    'size_bytes' => 232600,
    'width' => 1600,
    'height' => 1000,
  ),
  61 => 
  array (
    'file_name' => 'trusted-by-industry-professionals-framed-presentation.jpg',
    'file_path' => 'uploads/2026/11/trusted-by-industry-professionals-framed-presentation-35d199.jpg',
    'alt_text' => 'Guests on stage presenting a framed concert poster at a live music event',
    'mime_type' => 'image/jpeg',
    'size_bytes' => 330669,
    'width' => 2000,
    'height' => 1268,
  ),
  62 => 
  array (
    'file_name' => 'event-packages-bass-guitar-stage.webp',
    'file_path' => 'uploads/2026/11/event-packages-bass-guitar-stage-aae0b6.webp',
    'alt_text' => 'Bass guitar and stage light on a purple-lit stage with an audience behind',
    'mime_type' => 'image/webp',
    'size_bytes' => 281384,
    'width' => 1600,
    'height' => 1000,
  ),
  63 => 
  array (
    'file_name' => 'partnership-subcontracting-live-concert-stage.webp',
    'file_path' => 'uploads/2026/11/partnership-subcontracting-live-concert-stage-8acb4d.webp',
    'alt_text' => 'Musicians and singers performing on a stage with colourful LED screens behind them',
    'mime_type' => 'image/webp',
    'size_bytes' => 221384,
    'width' => 1600,
    'height' => 1064,
  ),
  64 => 
  array (
    'file_name' => 'flexible-sports-partner-cricket-trophies.webp',
    'file_path' => 'uploads/2026/11/flexible-sports-partner-cricket-trophies-e2158d.webp',
    'alt_text' => 'Cricket award trophies including All Rounder, Best Bowler and Most Sixes 2018',
    'mime_type' => 'image/webp',
    'size_bytes' => 369406,
    'width' => 1600,
    'height' => 1063,
  ),
  65 => 
  array (
    'file_name' => 'complete-event-coordination-venue-at-night.webp',
    'file_path' => 'uploads/2026/11/complete-event-coordination-venue-at-night-4c2b46.webp',
    'alt_text' => 'Glass-fronted event venue lit up against a deep blue night sky',
    'mime_type' => 'image/webp',
    'size_bytes' => 148328,
    'width' => 1600,
    'height' => 1068,
  ),
];
// Already applied? (the first new image is registered)
$done = $pdo->prepare('SELECT COUNT(*) FROM media WHERE file_path = ?');
$done->execute([$media[56]['file_path']]);
if ((int) $done->fetchColumn() > 0) { echo "Image updates already applied.\n"; exit(0); }

// Register the new images and remember their ids here.
$ids = [];
$insert = $pdo->prepare('INSERT INTO media (file_name, file_path, alt_text, mime_type, size_bytes, width, height) VALUES (?,?,?,?,?,?,?)');
foreach ($media as $key => $m) {
    $insert->execute([$m['file_name'], $m['file_path'], $m['alt_text'], $m['mime_type'], $m['size_bytes'], $m['width'], $m['height']]);
    $ids[$key] = (int) $pdo->lastInsertId();
}

// Point a picture field at a new image (section id, item id or null, field key).
$setImage = function (int $section, ?int $item, string $key, int $mediaId) use ($pdo): void {
    $find = $item === null
        ? $pdo->prepare('SELECT id FROM section_fields WHERE section_id = ? AND item_id IS NULL AND field_key = ?')
        : $pdo->prepare('SELECT id FROM section_fields WHERE section_id = ? AND item_id = ? AND field_key = ?');
    $find->execute($item === null ? [$section, $key] : [$section, $item, $key]);
    $row = $find->fetchColumn();
    if ($row) {
        $pdo->prepare('UPDATE section_fields SET media_id = ? WHERE id = ?')->execute([$mediaId, $row]);
    } else {
        $pdo->prepare('INSERT INTO section_fields (section_id, item_id, field_key, media_id) VALUES (?,?,?,?)')->execute([$section, $item, $key, $mediaId]);
    }
};

$setImage(10, null, 'image', $ids[56]);            // About: Our story
$pdo->prepare("DELETE FROM section_fields WHERE section_id = 10 AND item_id IS NULL AND field_key = 'image2'")->execute();
$pdo->prepare("UPDATE section_fields SET value_text = 'wide' WHERE section_id = 10 AND item_id IS NULL AND field_key = 'image_shape'")->execute();
$setImage(3, 3, 'image', $ids[59]);                // Home: Concerts & Live Entertainment
$setImage(3, 5, 'image', $ids[58]);                // Home: Corporate Events & Conferences
$setImage(18, null, 'image', $ids[61]);            // Concert Partnerships: Trusted by Industry Professionals
$setImage(27, 54, 'image', $ids[60]);              // Event Solutions: Artist & Guest Logistics
$setImage(28, null, 'image', $ids[62]);            // Event Solutions: Full Management or Individual Services
$setImage(28, 63, 'image', $ids[63]);              // Event Solutions: Partnership & Subcontracting
$setImage(35, null, 'image', $ids[64]);            // Sports: A Flexible Partner
$setImage(36, 81, 'image', $ids[65]);              // Sports: Complete Event Coordination
echo "Image updates applied.\n";

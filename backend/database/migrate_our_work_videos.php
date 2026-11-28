<?php
declare(strict_types=1);
// One-time: puts the Wedding and Gala Dinner videos on the Our Work page in place of the three project
// cards (which are hidden, not deleted). Safe to run on every start: it does nothing once applied.
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/load.php';
$pdo = Database::connect($config['db']);

$patch = json_decode(<<<'PATCH_JSON'
{
 "fields": {
  "eyebrow": "Featured Event Projects",
  "eyebrow_style": "line",
  "heading": "Real Events. Practical Support.",
  "heading_accent": "Across Every Type of Occasion.",
  "accent_new_line": "1",
  "intro": "Our event planning portfolio brings together real events and projects. Each one shows how we work behind the scenes in event project management, coordinating suppliers, managing logistics and keeping everything on track, so you can focus on your event.",
  "background_strength": "light"
 },
 "items": [
  {
   "fields": {
    "title": "Wedding Film",
    "video_url": "https://www.youtube.com/watch?v=ZKrqqNNYGSM",
    "video_title": "Every Wedding Has a Story: E for Events Cinematic Wedding Film",
    "open_mode": "newtab",
    "badge_left": "E for Events Cinematic Film",
    "badge_right": "4K Ultra HD",
    "play_label": "Watch Wedding Film (4K)",
    "caption": "Dimple & Harry – London RAC Club"
   },
   "poster": {
    "path": "uploads/2026/11/wedding-table-fruit-decorations-0ba391.webp",
    "alt": "Wedding stage with red drapes, decorated ceremony items, flowers and a deity figure"
   }
  },
  {
   "fields": {
    "title": "Gala Dinner Film",
    "video_url": "https://www.youtube.com/watch?v=FmZ4vHkw2Fc",
    "video_title": "E for Events showcase video: Over 70's World Cup Indian Team Gala Dinner",
    "open_mode": "newtab",
    "badge_left": "E for Events 4K Showcase",
    "badge_right": "Ultra HD",
    "play_label": "Watch Gala Dinner Film",
    "caption": "The Dorchester Mayfair Gala Dinner & Awards"
   },
   "poster": {
    "path": "uploads/2026/11/gala-dinner-speaker-presentation-cb68d8.webp",
    "alt": "Speaker with a microphone presenting to guests at the Over 70's World Cup Indian Team Gala Dinner"
   }
  }
 ]
}
PATCH_JSON
, true);

// Already applied? The marker is stored once everything below has run.
$marker = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE group_name = 'system' AND setting_key = 'patch_our_work_videos_1'");
$marker->execute();
if ((int) $marker->fetchColumn() > 0) { echo "Our Work videos already applied.\n"; exit(0); }

$pg = $pdo->prepare('SELECT id FROM pages WHERE slug = ?');
$pg->execute(['our-work']);
$pageId = (int) $pg->fetchColumn();
if (!$pageId) { echo "Our Work page not found.\n"; exit(0); }

$pdo->beginTransaction();
try {
    $exists = $pdo->prepare("SELECT COUNT(*) FROM sections WHERE page_id = ? AND type = 'video_grid'");
    $exists->execute([$pageId]);
    if (!(int) $exists->fetchColumn()) {
        // The videos take the place of the three project cards, which are hidden (not deleted).
        $cards = $pdo->prepare("SELECT id, sort_order FROM sections WHERE page_id = ? AND type = 'project_cards' LIMIT 1");
        $cards->execute([$pageId]);
        $old = $cards->fetch();
        $order = $old ? (int) $old['sort_order'] : 2;
        $pdo->prepare('UPDATE sections SET sort_order = sort_order + 1 WHERE page_id = ? AND sort_order >= ?')->execute([$pageId, $order]);
        $pdo->prepare("INSERT INTO sections (page_id, type, label, is_visible, sort_order) VALUES (?, 'video_grid', 'Videos', 1, ?)")->execute([$pageId, $order]);
        $sid = (int) $pdo->lastInsertId();
        if ($old) { $pdo->prepare('UPDATE sections SET is_visible = 0 WHERE id = ?')->execute([$old['id']]); }

        $text = $pdo->prepare('INSERT INTO section_fields (section_id, item_id, field_key, value_text) VALUES (?, ?, ?, ?)');
        foreach ($patch['fields'] as $k => $v) { $text->execute([$sid, null, $k, (string) $v]); }

        $media = $pdo->prepare('SELECT id FROM media WHERE file_path = ?');
        $image = $pdo->prepare('INSERT INTO section_fields (section_id, item_id, field_key, value_text, media_id) VALUES (?, ?, ?, ?, ?)');
        foreach ($patch['items'] as $n => $it) {
            $pdo->prepare('INSERT INTO section_items (section_id, is_visible, sort_order) VALUES (?, 1, ?)')->execute([$sid, $n + 1]);
            $iid = (int) $pdo->lastInsertId();
            foreach ($it['fields'] as $k => $v) { $text->execute([$sid, $iid, $k, (string) $v]); }
            $media->execute([$it['poster']['path']]);
            $mid = $media->fetchColumn();
            if ($mid) { $image->execute([$sid, $iid, 'poster', $it['poster']['alt'], (int) $mid]); }
        }
        echo "Added the Our Work videos.\n";
    }
    $pdo->prepare("INSERT INTO settings (group_name, setting_key, value_text, is_encrypted) VALUES ('system', 'patch_our_work_videos_1', '1', 0)")->execute();
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR, 'Our Work videos failed and were rolled back: ' . $e->getMessage() . "\n");
    exit(1);
}

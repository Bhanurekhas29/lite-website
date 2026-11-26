<?php
declare(strict_types=1);

/** Loading and saving pages, sections, items and their fields. */
final class PageService
{
    public static function mediaUrl(string $path): string
    {
        return '/media/' . ltrim($path, '/');
    }

    // ------------------------------------------------------------------
    // Reading
    // ------------------------------------------------------------------

    private static function pageData(array $r): array
    {
        return [
            'id' => (int) $r['id'],
            'title' => $r['title'],
            'menu_label' => $r['menu_label'],
            'footer_label' => $r['footer_label'],
            'slug' => $r['slug'],
            'is_visible' => (bool) $r['is_visible'],
            'is_locked' => (bool) $r['is_locked'],
            'show_in_header' => (bool) $r['show_in_header'],
            'show_in_footer' => (bool) $r['show_in_footer'],
            'seo_title' => $r['seo_title'] ?? '',
            'seo_description' => $r['seo_description'] ?? '',
            'seo_keywords_primary' => $r['seo_keywords_primary'] ?? '',
            'seo_keywords_secondary' => $r['seo_keywords_secondary'] ?? '',
        ];
    }

    private static function defaultValue(array $spec): mixed
    {
        return match ($spec['type']) {
            'image', 'icon' => null,
            'button', 'link', 'action' => ['label' => '', 'icon' => null, 'link_type' => 'none', 'link_page_id' => null, 'link_url' => ''],
            'toggle' => $spec['default'] ?? '0',
            'select' => $spec['default'] ?? array_key_first($spec['options']),
            default => '',
        };
    }

    private static function fromRow(array $spec, ?array $row): mixed
    {
        if ($row === null) {
            return self::defaultValue($spec);
        }
        return match ($spec['type']) {
            'image' => $row['media_id'] ? [
                'id' => (int) $row['media_id'],
                'url' => self::mediaUrl($row['file_path']),
                'alt' => $row['alt_text'],
            ] : null,
            'icon' => $row['icon'],
            'button', 'link', 'action' => [
                'label' => $row['value_text'] ?? '',
                'icon' => $row['icon'],
                'link_type' => $row['link_type'],
                'link_page_id' => $row['link_page_id'] !== null ? (int) $row['link_page_id'] : null,
                'link_url' => $row['link_url'] ?? '',
            ],
            default => $row['value_text'] ?? self::defaultValue($spec),
        };
    }

    /** All sections of a page with their fields and items. */
    public static function loadSections(PDO $pdo, int $pageId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order, id');
        $stmt->execute([$pageId]);
        $sections = $stmt->fetchAll();

        $stmt = $pdo->prepare(
            'SELECT f.*, m.file_path, m.alt_text FROM section_fields f
             JOIN sections s ON s.id = f.section_id
             LEFT JOIN media m ON m.id = f.media_id
             WHERE s.page_id = ?'
        );
        $stmt->execute([$pageId]);
        $rows = [];
        foreach ($stmt->fetchAll() as $f) {
            $rows[(int) $f['section_id']][(int) ($f['item_id'] ?? 0)][$f['field_key']] = $f;
        }

        $stmt = $pdo->prepare(
            'SELECT i.* FROM section_items i JOIN sections s ON s.id = i.section_id
             WHERE s.page_id = ? ORDER BY i.sort_order, i.id'
        );
        $stmt->execute([$pageId]);
        $items = [];
        foreach ($stmt->fetchAll() as $i) {
            $items[(int) $i['section_id']][] = $i;
        }

        $out = [];
        foreach ($sections as $s) {
            $sid = (int) $s['id'];
            $type = Schemas::type($s['type']);
            if (!$type) {
                continue;
            }
            $fields = [];
            foreach ($type['fields'] as $spec) {
                $fields[$spec['key']] = self::fromRow($spec, $rows[$sid][0][$spec['key']] ?? null);
            }
            $section = [
                'id' => $sid,
                'type' => $s['type'],
                'label' => $s['label'],
                'is_visible' => (bool) $s['is_visible'],
                'sort_order' => (int) $s['sort_order'],
                'fields' => $fields ?: new stdClass(),
            ];
            if (isset($type['items'])) {
                $list = [];
                foreach ($items[$sid] ?? [] as $i) {
                    $iid = (int) $i['id'];
                    $itemFields = [];
                    foreach ($type['items']['fields'] as $spec) {
                        $itemFields[$spec['key']] = self::fromRow($spec, $rows[$sid][$iid][$spec['key']] ?? null);
                    }
                    $list[] = ['id' => $iid, 'is_visible' => (bool) $i['is_visible'], 'fields' => $itemFields];
                }
                $section['items'] = $list;
            }
            $out[] = $section;
        }
        return $out;
    }

    public static function loadPage(PDO $pdo, string $slug): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $page = self::pageData($row);
        $sections = self::loadSections($pdo, $page['id']);

        // Only send the section types this page uses.
        $types = [];
        foreach ($sections as $s) {
            $types[$s['type']] ??= Schemas::type($s['type']);
        }
        return ['page' => $page, 'sections' => $sections, 'types' => $types ?: new stdClass()];
    }

    // ------------------------------------------------------------------
    // Validating and writing field values
    // ------------------------------------------------------------------

    private static function cleanIcon(mixed $icon): ?string
    {
        $icon = is_string($icon) ? trim($icon) : '';
        if ($icon === '') {
            return null;
        }
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $icon) || strlen($icon) > 60) {
            throw new HttpException('Choose a valid icon');
        }
        return $icon;
    }

    private static function cleanUrl(mixed $url): ?string
    {
        $url = is_string($url) ? trim($url) : '';
        if ($url === '') {
            return null;
        }
        if (strlen($url) > 500 || !preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $url)) {
            throw new HttpException('Links must start with https://, http://, mailto:, tel:, / or #');
        }
        return $url;
    }

    private static function mediaExists(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('SELECT 1 FROM media WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetchColumn()) {
            throw new HttpException('That image no longer exists');
        }
    }

    private static function pageExists(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('SELECT 1 FROM pages WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetchColumn()) {
            throw new HttpException('That page no longer exists');
        }
    }

    /** Turn an input value into the columns stored in section_fields. */
    private static function toRow(PDO $pdo, array $spec, mixed $input): array
    {
        $row = ['value_text' => null, 'media_id' => null, 'icon' => null, 'link_type' => 'none', 'link_page_id' => null, 'link_url' => null];
        switch ($spec['type']) {
            case 'text':
                $row['value_text'] = mb_substr(trim((string) ($input ?? '')), 0, 500);
                break;
            case 'textarea':
                $row['value_text'] = mb_substr(trim((string) ($input ?? '')), 0, 5000);
                break;
            case 'choices':
                // one choice per line; blank lines and repeats are dropped
                $seen = [];
                foreach (preg_split('/\R/', (string) ($input ?? '')) as $line) {
                    $line = mb_substr(trim($line), 0, 150);
                    if ($line !== '' && !in_array($line, $seen, true)) {
                        $seen[] = $line;
                    }
                }
                $row['value_text'] = implode("
", array_slice($seen, 0, 40));
                break;
            case 'url':
                $url = mb_substr(trim((string) ($input ?? '')), 0, 500);
                if ($url !== '' && !preg_match('#^https?://\S+$#i', $url)) {
                    throw new HttpException('Links must start with https:// or http://');
                }
                $row['value_text'] = $url;
                break;
            case 'toggle':
                $row['value_text'] = in_array($input, ['1', 1, true, 'true'], true) ? '1' : '0';
                break;
            case 'select':
                $value = (string) ($input ?? '');
                $row['value_text'] = array_key_exists($value, $spec['options']) ? $value : self::defaultValue($spec);
                break;
            case 'image':
                $id = is_array($input) ? (int) ($input['id'] ?? 0) : 0;
                if ($id > 0) {
                    self::mediaExists($pdo, $id);
                    $row['media_id'] = $id;
                }
                break;
            case 'icon':
                $row['icon'] = self::cleanIcon($input);
                break;
            case 'button':
            case 'link':
            case 'action':
                $b = is_array($input) ? $input : [];
                $row['value_text'] = mb_substr(trim((string) ($b['label'] ?? '')), 0, 120);
                $row['icon'] = self::cleanIcon($b['icon'] ?? null);
                $type = (string) ($b['link_type'] ?? 'none');
                if ($type === 'page') {
                    $pid = (int) ($b['link_page_id'] ?? 0);
                    if ($pid > 0) {
                        self::pageExists($pdo, $pid);
                        $row['link_type'] = 'page';
                        $row['link_page_id'] = $pid;
                    }
                } elseif ($type === 'url') {
                    $url = self::cleanUrl($b['link_url'] ?? '');
                    if ($url !== null) {
                        $row['link_type'] = 'url';
                        $row['link_url'] = $url;
                    }
                }
                break;
        }
        return $row;
    }

    private static function insertField(PDO $pdo, int $sectionId, ?int $itemId, string $key, array $r): void
    {
        $pdo->prepare(
            'INSERT INTO section_fields (section_id, item_id, field_key, value_text, media_id, icon, link_type, link_page_id, link_url)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$sectionId, $itemId, $key, $r['value_text'], $r['media_id'], $r['icon'], $r['link_type'], $r['link_page_id'], $r['link_url']]);
    }

    private static function writeFields(PDO $pdo, int $sectionId, ?int $itemId, array $specs, array $input): void
    {
        foreach ($specs as $spec) {
            self::insertField($pdo, $sectionId, $itemId, $spec['key'], self::toRow($pdo, $spec, $input[$spec['key']] ?? null));
        }
    }

    // ------------------------------------------------------------------
    // Saving
    // ------------------------------------------------------------------

    private static function sectionRow(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare('SELECT * FROM sections WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Section not found', 404);
        }
        return $row;
    }

    private static function sectionById(PDO $pdo, array $row): array
    {
        foreach (self::loadSections($pdo, (int) $row['page_id']) as $s) {
            if ($s['id'] === (int) $row['id']) {
                return $s;
            }
        }
        throw new HttpException('Section not found', 404);
    }

    public static function saveSection(PDO $pdo, int $id, array $body): array
    {
        $row = self::sectionRow($pdo, $id);
        $type = Schemas::type($row['type']);
        if (!$type) {
            throw new HttpException('Unknown section type');
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM section_fields WHERE section_id = ? AND item_id IS NULL')->execute([$id]);
            self::writeFields($pdo, $id, null, $type['fields'], is_array($body['fields'] ?? null) ? $body['fields'] : []);

            if (isset($type['items'])) {
                $payload = is_array($body['items'] ?? null) ? array_values($body['items']) : [];
                if (count($payload) > ($type['items']['max'] ?? 50)) {
                    throw new HttpException('Too many ' . strtolower($type['items']['label']) . 's');
                }

                $existing = $pdo->prepare('SELECT id FROM section_items WHERE section_id = ?');
                $existing->execute([$id]);
                $existingIds = array_map('intval', array_column($existing->fetchAll(), 'id'));
                $keep = array_map(fn ($i) => (int) ($i['id'] ?? 0), $payload);

                foreach (array_diff($existingIds, $keep) as $gone) {
                    $pdo->prepare('DELETE FROM section_items WHERE id = ?')->execute([$gone]);
                }

                foreach ($payload as $index => $item) {
                    $itemId = (int) ($item['id'] ?? 0);
                    $visible = !empty($item['is_visible']) ? 1 : 0;
                    if ($itemId && in_array($itemId, $existingIds, true)) {
                        $pdo->prepare('UPDATE section_items SET is_visible = ?, sort_order = ? WHERE id = ?')->execute([$visible, $index + 1, $itemId]);
                        $pdo->prepare('DELETE FROM section_fields WHERE item_id = ?')->execute([$itemId]);
                    } else {
                        $pdo->prepare('INSERT INTO section_items (section_id, is_visible, sort_order) VALUES (?, ?, ?)')->execute([$id, $visible, $index + 1]);
                        $itemId = (int) $pdo->lastInsertId();
                    }
                    self::writeFields($pdo, $id, $itemId, $type['items']['fields'], is_array($item['fields'] ?? null) ? $item['fields'] : []);
                }
            }

            $pdo->prepare('UPDATE sections SET updated_at = UTC_TIMESTAMP() WHERE id = ?')->execute([$id]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return self::sectionById($pdo, $row);
    }

    public static function setSectionVisibility(PDO $pdo, int $id, bool $visible): void
    {
        self::sectionRow($pdo, $id);
        $pdo->prepare('UPDATE sections SET is_visible = ? WHERE id = ?')->execute([$visible ? 1 : 0, $id]);
    }

    /** Save the new order of a page's sections. $ids must be exactly the page's section ids. */
    public static function reorderSections(PDO $pdo, string $slug, array $ids): void
    {
        $stmt = $pdo->prepare('SELECT s.id FROM sections s JOIN pages p ON p.id = s.page_id WHERE p.slug = ?');
        $stmt->execute([$slug]);
        $have = array_map('intval', array_column($stmt->fetchAll(), 'id'));
        $ids = array_map('intval', $ids);
        sort($have);
        $check = $ids;
        sort($check);
        if ($have !== $check) {
            throw new HttpException('The section list is out of date. Reload the page.');
        }
        $pdo->beginTransaction();
        $update = $pdo->prepare('UPDATE sections SET sort_order = ? WHERE id = ?');
        foreach ($ids as $i => $id) {
            $update->execute([$i + 1, $id]);
        }
        $pdo->commit();
    }

    public static function savePage(PDO $pdo, string $slug, array $b): array
    {
        $stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Page not found', 404);
        }
        $locked = (bool) $row['is_locked'];

        $menuLabel = mb_substr(trim((string) ($b['menu_label'] ?? '')), 0, 100);
        if ($menuLabel === '') {
            throw new HttpException('Enter a menu label');
        }
        $footerLabel = mb_substr(trim((string) ($b['footer_label'] ?? '')), 0, 100);

        $newSlug = $locked ? $row['slug'] : strtolower(trim((string) ($b['slug'] ?? $row['slug'])));
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $newSlug) || strlen($newSlug) > 120) {
            throw new HttpException('The page address can use lowercase letters, numbers and hyphens only');
        }
        if ($newSlug !== $row['slug']) {
            $dup = $pdo->prepare('SELECT 1 FROM pages WHERE slug = ? AND id <> ?');
            $dup->execute([$newSlug, $row['id']]);
            if ($dup->fetchColumn()) {
                throw new HttpException('Another page already uses that address');
            }
        }

        $visible = !empty($b['is_visible']);
        if ($locked && !$visible) {
            throw new HttpException('The Home page cannot be hidden');
        }

        $text = fn (string $k, int $max) => mb_substr(trim((string) ($b[$k] ?? '')), 0, $max);
        $pdo->prepare(
            'UPDATE pages SET menu_label = ?, footer_label = ?, slug = ?, is_visible = ?, show_in_header = ?, show_in_footer = ?,
             seo_title = ?, seo_description = ?, seo_keywords_primary = ?, seo_keywords_secondary = ? WHERE id = ?'
        )->execute([
            $menuLabel,
            $footerLabel !== '' ? $footerLabel : null,
            $newSlug,
            $visible ? 1 : 0,
            !empty($b['show_in_header']) ? 1 : 0,
            !empty($b['show_in_footer']) ? 1 : 0,
            $text('seo_title', 255),
            $text('seo_description', 500),
            $text('seo_keywords_primary', 500),
            $text('seo_keywords_secondary', 500),
            $row['id'],
        ]);

        $links = $pdo->prepare('SELECT COUNT(*) FROM section_fields WHERE link_page_id = ?');
        $links->execute([$row['id']]);

        $fresh = $pdo->prepare('SELECT * FROM pages WHERE id = ?');
        $fresh->execute([$row['id']]);
        return ['page' => self::pageData($fresh->fetch()), 'linked_buttons' => (int) $links->fetchColumn()];
    }
}

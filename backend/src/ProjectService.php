<?php
declare(strict_types=1);

/** Projects (shown on Our Work and, when ticked, on Home) and their categories. */
final class ProjectService
{
    private const MAX_SERVICES = 12;

    // ------------------------------------------------------------------
    // Projects
    // ------------------------------------------------------------------

    private static function shape(array $r): array
    {
        return [
            'id' => (int) $r['id'],
            'title' => $r['title'],
            'category_id' => (int) $r['category_id'],
            'category_name' => $r['category_name'],
            'category_icon' => $r['category_icon'],
            'image' => $r['image_media_id'] ? [
                'id' => (int) $r['image_media_id'],
                'url' => PageService::mediaUrl($r['file_path']),
                'alt' => $r['alt_text'],
            ] : null,
            'hover_image' => $r['hover_media_id'] ? [
                'id' => (int) $r['hover_media_id'],
                'url' => PageService::mediaUrl($r['hover_path']),
                'alt' => $r['hover_alt'],
            ] : null,
            'event_year' => $r['event_year'] ?? '',
            'location' => $r['location'] ?? '',
            'our_role' => $r['our_role'] ?? '',
            'summary' => $r['summary'] ?? '',
            'image_alt' => $r['image_alt'] ?? '',
            'show_on_home' => (bool) $r['show_on_home'],
            'is_visible' => (bool) $r['is_visible'],
            'sort_order' => (int) $r['sort_order'],
        ];
    }

    private const SELECT =
        'SELECT p.*, c.name AS category_name, c.icon AS category_icon, m.file_path, m.alt_text,
                h.file_path AS hover_path, h.alt_text AS hover_alt
         FROM projects p JOIN project_categories c ON c.id = p.category_id
         LEFT JOIN media m ON m.id = p.image_media_id
         LEFT JOIN media h ON h.id = p.hover_media_id';

    public static function list(PDO $pdo): array
    {
        $rows = $pdo->query(self::SELECT . ' ORDER BY p.sort_order, p.id')->fetchAll();
        $counts = [];
        foreach ($pdo->query('SELECT project_id, COUNT(*) AS n FROM project_services GROUP BY project_id')->fetchAll() as $c) {
            $counts[(int) $c['project_id']] = (int) $c['n'];
        }
        return array_map(fn ($r) => self::shape($r) + ['service_count' => $counts[(int) $r['id']] ?? 0], $rows);
    }

    public static function get(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare(self::SELECT . ' WHERE p.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Project not found', 404);
        }
        $services = $pdo->prepare('SELECT text FROM project_services WHERE project_id = ? ORDER BY sort_order, id');
        $services->execute([$id]);
        return self::shape($row) + ['services' => array_column($services->fetchAll(), 'text')];
    }

    private static function text(mixed $v, int $max): string
    {
        return mb_substr(trim((string) ($v ?? '')), 0, $max);
    }

    /** Validate what the admin sent. */
    private static function clean(PDO $pdo, array $b): array
    {
        $title = self::text($b['title'] ?? '', 200);
        if ($title === '') {
            throw new HttpException('Enter a project title');
        }

        $categoryId = (int) ($b['category_id'] ?? 0);
        $exists = $pdo->prepare('SELECT 1 FROM project_categories WHERE id = ?');
        $exists->execute([$categoryId]);
        if (!$exists->fetchColumn()) {
            throw new HttpException('Choose a category');
        }

        $media = $pdo->prepare('SELECT 1 FROM media WHERE id = ?');
        $ids = [];
        foreach (['image', 'hover_image'] as $key) {
            $ids[$key] = is_array($b[$key] ?? null) ? (int) ($b[$key]['id'] ?? 0) : 0;
            if ($ids[$key] > 0) {
                $media->execute([$ids[$key]]);
                if (!$media->fetchColumn()) {
                    throw new HttpException('That image no longer exists');
                }
            }
        }
        $imageId = $ids['image'];
        $hoverId = $ids['hover_image'];

        $services = [];
        foreach (is_array($b['services'] ?? null) ? $b['services'] : [] as $s) {
            $s = self::text($s, 200);
            if ($s !== '') {
                $services[] = $s;
            }
        }
        if (count($services) > self::MAX_SERVICES) {
            throw new HttpException('A project can list up to ' . self::MAX_SERVICES . ' services');
        }

        return [
            'title' => $title,
            'category_id' => $categoryId,
            'image_media_id' => $imageId > 0 ? $imageId : null,
            'hover_media_id' => $hoverId > 0 ? $hoverId : null,
            'event_year' => self::text($b['event_year'] ?? '', 20) ?: null,
            'location' => self::text($b['location'] ?? '', 150) ?: null,
            'our_role' => self::text($b['our_role'] ?? '', 255) ?: null,
            'summary' => self::text($b['summary'] ?? '', 500) ?: null,
            'image_alt' => self::text($b['image_alt'] ?? '', 255) ?: null,
            'show_on_home' => !empty($b['show_on_home']) ? 1 : 0,
            'is_visible' => !empty($b['is_visible']) ? 1 : 0,
            'services' => $services,
        ];
    }

    private static function writeServices(PDO $pdo, int $projectId, array $services): void
    {
        $pdo->prepare('DELETE FROM project_services WHERE project_id = ?')->execute([$projectId]);
        $insert = $pdo->prepare('INSERT INTO project_services (project_id, text, sort_order) VALUES (?, ?, ?)');
        foreach ($services as $i => $text) {
            $insert->execute([$projectId, $text, $i + 1]);
        }
    }

    public static function create(PDO $pdo, array $b): array
    {
        $c = self::clean($pdo, $b);
        $pdo->beginTransaction();
        try {
            $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM projects')->fetchColumn();
            $pdo->prepare(
                'INSERT INTO projects (category_id, title, image_media_id, hover_media_id, event_year, location, our_role, summary, image_alt, show_on_home, is_visible, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$c['category_id'], $c['title'], $c['image_media_id'], $c['hover_media_id'], $c['event_year'], $c['location'], $c['our_role'], $c['summary'], $c['image_alt'], $c['show_on_home'], $c['is_visible'], $next]);
            $id = (int) $pdo->lastInsertId();
            self::writeServices($pdo, $id, $c['services']);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        return self::get($pdo, $id);
    }

    public static function update(PDO $pdo, int $id, array $b): array
    {
        self::get($pdo, $id);
        $c = self::clean($pdo, $b);
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'UPDATE projects SET category_id = ?, title = ?, image_media_id = ?, hover_media_id = ?, event_year = ?, location = ?, our_role = ?, summary = ?, image_alt = ?,
                 show_on_home = ?, is_visible = ? WHERE id = ?'
            )->execute([$c['category_id'], $c['title'], $c['image_media_id'], $c['hover_media_id'], $c['event_year'], $c['location'], $c['our_role'], $c['summary'], $c['image_alt'], $c['show_on_home'], $c['is_visible'], $id]);
            self::writeServices($pdo, $id, $c['services']);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        return self::get($pdo, $id);
    }

    /** Quick switches from the list: only the flags that were sent change. */
    public static function setFlags(PDO $pdo, int $id, array $b): void
    {
        self::get($pdo, $id);
        foreach (['show_on_home', 'is_visible'] as $flag) {
            if (array_key_exists($flag, $b)) {
                $pdo->prepare("UPDATE projects SET $flag = ? WHERE id = ?")->execute([!empty($b[$flag]) ? 1 : 0, $id]);
            }
        }
    }

    public static function delete(PDO $pdo, int $id): void
    {
        self::get($pdo, $id);
        $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
    }

    /** Save a new order. $ids must be exactly the existing project ids. */
    public static function reorder(PDO $pdo, array $ids): void
    {
        $have = array_map('intval', array_column($pdo->query('SELECT id FROM projects')->fetchAll(), 'id'));
        $ids = array_map('intval', $ids);
        sort($have);
        $check = $ids;
        sort($check);
        if ($have !== $check) {
            throw new HttpException('The project list is out of date. Reload the page.');
        }
        $pdo->beginTransaction();
        $update = $pdo->prepare('UPDATE projects SET sort_order = ? WHERE id = ?');
        foreach ($ids as $i => $id) {
            $update->execute([$i + 1, $id]);
        }
        $pdo->commit();
    }

    // ------------------------------------------------------------------
    // Categories
    // ------------------------------------------------------------------

    public static function categories(PDO $pdo): array
    {
        $rows = $pdo->query(
            'SELECT c.*, (SELECT COUNT(*) FROM projects p WHERE p.category_id = c.id) AS projects
             FROM project_categories c ORDER BY c.sort_order, c.id'
        )->fetchAll();
        return array_map(fn ($r) => [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'icon' => $r['icon'],
            'projects' => (int) $r['projects'],
        ], $rows);
    }

    private static function cleanCategory(PDO $pdo, array $b, ?int $id): array
    {
        $name = self::text($b['name'] ?? '', 100);
        if ($name === '') {
            throw new HttpException('Enter a category name');
        }
        $dup = $pdo->prepare('SELECT 1 FROM project_categories WHERE name = ? AND id <> ?');
        $dup->execute([$name, $id ?? 0]);
        if ($dup->fetchColumn()) {
            throw new HttpException('A category with that name already exists');
        }
        $icon = is_string($b['icon'] ?? null) ? trim($b['icon']) : '';
        if ($icon !== '' && (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $icon) || strlen($icon) > 60)) {
            throw new HttpException('Choose a valid icon');
        }
        return [$name, $icon !== '' ? $icon : null];
    }

    public static function createCategory(PDO $pdo, array $b): void
    {
        [$name, $icon] = self::cleanCategory($pdo, $b, null);
        $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM project_categories')->fetchColumn();
        $pdo->prepare('INSERT INTO project_categories (name, icon, sort_order) VALUES (?, ?, ?)')->execute([$name, $icon, $next]);
    }

    public static function updateCategory(PDO $pdo, int $id, array $b): void
    {
        $exists = $pdo->prepare('SELECT 1 FROM project_categories WHERE id = ?');
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) {
            throw new HttpException('Category not found', 404);
        }
        [$name, $icon] = self::cleanCategory($pdo, $b, $id);
        $pdo->prepare('UPDATE project_categories SET name = ?, icon = ? WHERE id = ?')->execute([$name, $icon, $id]);
    }

    public static function deleteCategory(PDO $pdo, int $id): void
    {
        $used = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE category_id = ?');
        $used->execute([$id]);
        $n = (int) $used->fetchColumn();
        if ($n > 0) {
            throw new HttpException("This category is used by $n project" . ($n === 1 ? '' : 's') . '. Move them to another category first.');
        }
        $pdo->prepare('DELETE FROM project_categories WHERE id = ?')->execute([$id]);
    }
}

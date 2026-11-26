<?php
declare(strict_types=1);

/** The Media screen: browsing, usage, renaming, replacing and deleting library images. */
final class MediaLibrary
{
    private const PER_PAGE = 36;

    private const USES_SQL =
        '(SELECT COUNT(*) FROM section_fields f WHERE f.media_id = m.id)
         + (SELECT COUNT(*) FROM settings s WHERE s.media_id = m.id)
         + (SELECT COUNT(*) FROM projects p WHERE p.image_media_id = m.id OR p.hover_media_id = m.id)
         + (SELECT COUNT(*) FROM admins a WHERE a.photo_media_id = m.id)';

    private static function absolute(string $filePath): string
    {
        return dirname(__DIR__) . '/media/' . $filePath;
    }

    private static function iso(?string $mysqlTime): ?string
    {
        return $mysqlTime ? str_replace(' ', 'T', $mysqlTime) . 'Z' : null;
    }

    private static function shape(array $r): array
    {
        $item = MediaService::shape($r);
        $item['name'] = pathinfo($r['file_name'], PATHINFO_FILENAME);
        $item['created_at'] = self::iso($r['created_at']);
        $item['uses'] = (int) ($r['uses'] ?? 0);
        return $item;
    }

    public static function library(PDO $pdo, string $filter, string $q, int $page): array
    {
        $where = [];
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $like = '%' . addcslashes(mb_substr($q, 0, 100), '%_\\') . '%';
            $where[] = '(m.file_name LIKE ? OR m.alt_text LIKE ?)';
            array_push($params, $like, $like);
        }
        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $having = match ($filter) {
            'used' => 'HAVING uses > 0',
            'unused' => 'HAVING uses = 0',
            default => '',
        };

        $base = 'SELECT m.*, (' . self::USES_SQL . ") AS uses FROM media m $clause $having";
        $count = $pdo->prepare("SELECT COUNT(*) FROM ($base) x");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * self::PER_PAGE;
        $rows = $pdo->prepare("$base ORDER BY m.id DESC LIMIT " . self::PER_PAGE . " OFFSET $offset");
        $rows->execute($params);

        $stats = $pdo->query(
            'SELECT COUNT(*) AS total, COALESCE(SUM(size_bytes), 0) AS bytes,
                    SUM(CASE WHEN (' . self::USES_SQL . ') > 0 THEN 1 ELSE 0 END) AS used
             FROM media m'
        )->fetch();

        return [
            'items' => array_map([self::class, 'shape'], $rows->fetchAll()),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'stats' => [
                'all' => (int) $stats['total'],
                'used' => (int) $stats['used'],
                'unused' => (int) $stats['total'] - (int) $stats['used'],
                'bytes' => (int) $stats['bytes'],
            ],
        ];
    }

    private static function row(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare('SELECT m.*, (' . self::USES_SQL . ') AS uses FROM media m WHERE m.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Image not found', 404);
        }
        return $row;
    }

    /** Where this image is used, so nothing is deleted or replaced blindly. */
    private static function usage(PDO $pdo, int $id): array
    {
        $uses = [];

        $stmt = $pdo->prepare(
            'SELECT DISTINCT p.title, p.slug, s.label, p.sort_order AS page_order, s.sort_order AS section_order
             FROM section_fields f
             JOIN sections s ON s.id = f.section_id JOIN pages p ON p.id = s.page_id
             WHERE f.media_id = ? ORDER BY page_order, section_order'
        );
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $r) {
            $uses[] = ['kind' => 'page', 'label' => $r['title'] . ': ' . $r['label'], 'to' => '/admin/pages/' . $r['slug']];
        }

        $names = [
            'brand.logo_header' => 'Brand: header logo',
            'brand.logo_footer' => 'Brand: footer logo',
            'brand.favicon' => 'Brand: favicon',
        ];
        $stmt = $pdo->prepare('SELECT group_name, setting_key FROM settings WHERE media_id = ?');
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $r) {
            $key = $r['group_name'] . '.' . $r['setting_key'];
            $uses[] = ['kind' => 'settings', 'label' => $names[$key] ?? "Settings: $key", 'to' => '/admin/settings/' . ($r['group_name'] === 'brand' ? 'brand' : 'contact')];
        }

        $stmt = $pdo->prepare('SELECT id, title FROM projects WHERE image_media_id = ? OR hover_media_id = ?');
        $stmt->execute([$id, $id]);
        foreach ($stmt->fetchAll() as $r) {
            $uses[] = ['kind' => 'project', 'label' => 'Project: ' . $r['title'], 'to' => '/admin/projects/' . $r['id']];
        }

        $stmt = $pdo->prepare('SELECT display_name FROM admins WHERE photo_media_id = ?');
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $r) {
            $uses[] = ['kind' => 'admin', 'label' => 'Profile photo: ' . $r['display_name'], 'to' => '/admin/profile'];
        }
        return $uses;
    }

    public static function detail(PDO $pdo, int $id): array
    {
        return self::shape(self::row($pdo, $id)) + ['usage' => self::usage($pdo, $id)];
    }

    /** Change the alt text and/or the name. A new name also renames the file (good for SEO). */
    public static function update(PDO $pdo, int $id, array $b): array
    {
        $row = self::row($pdo, $id);
        $alt = mb_substr(trim((string) ($b['alt'] ?? $row['alt_text'])), 0, 255);

        $name = trim((string) ($b['name'] ?? pathinfo($row['file_name'], PATHINFO_FILENAME)));
        $name = mb_substr($name, 0, 100);
        if ($name === '') {
            throw new HttpException('Enter a name for the image');
        }

        $ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
        $oldBase = pathinfo($row['file_name'], PATHINFO_FILENAME);
        $filePath = $row['file_path'];
        $fileName = $row['file_name'];

        if ($name !== $oldBase) {
            $dir = dirname($row['file_path']);
            $newPath = $dir . '/' . MediaService::slugify($name) . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
            if (!@rename(self::absolute($row['file_path']), self::absolute($newPath))) {
                throw new HttpException('The file could not be renamed', 500);
            }
            $filePath = $newPath;
            $fileName = $name . '.' . $ext;
        }

        try {
            $pdo->prepare('UPDATE media SET alt_text = ?, file_name = ?, file_path = ? WHERE id = ?')->execute([$alt, $fileName, $filePath, $id]);
        } catch (Throwable $e) {
            if ($filePath !== $row['file_path']) {
                @rename(self::absolute($filePath), self::absolute($row['file_path']));
            }
            throw $e;
        }
        return self::detail($pdo, $id);
    }

    /** Swap the picture but keep the same library entry, so every page that uses it updates. */
    public static function replace(PDO $pdo, int $id, ?array $file): array
    {
        $row = self::row($pdo, $id);
        if (!$file) {
            throw new HttpException('Choose an image to upload');
        }
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || ($file['size'] ?? 0) > MediaService::MAX_BYTES) {
            throw new HttpException('That file is too large. The limit is 8 MB.');
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new HttpException('The upload failed. Try again.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $info = @getimagesize($file['tmp_name']);
        if (!isset(MediaService::ALLOWED[$mime]) || $info === false) {
            throw new HttpException('Upload a JPG, PNG, WebP or GIF image');
        }

        $ext = MediaService::ALLOWED[$mime];
        $dir = dirname($row['file_path']);
        $newPath = $dir . '/' . MediaService::slugify(pathinfo($row['file_name'], PATHINFO_FILENAME)) . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], self::absolute($newPath))) {
            throw new HttpException('The file could not be saved', 500);
        }

        $label = pathinfo($row['file_name'], PATHINFO_FILENAME) . '.' . $ext;
        $pdo->prepare('UPDATE media SET file_path = ?, file_name = ?, mime_type = ?, size_bytes = ?, width = ?, height = ? WHERE id = ?')
            ->execute([$newPath, $label, $mime, (int) $file['size'], $info[0], $info[1], $id]);
        @unlink(self::absolute($row['file_path']));
        return self::detail($pdo, $id);
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $row = self::row($pdo, $id);
        $usage = self::usage($pdo, $id);
        if ($usage) {
            $n = count($usage);
            throw new HttpException("This image is used in $n place" . ($n === 1 ? '' : 's') . ". Replace it there first, or use \"Replace image\" to change the picture.");
        }
        $pdo->prepare('DELETE FROM media WHERE id = ?')->execute([$id]);
        @unlink(self::absolute($row['file_path']));
    }

    /** Delete several images. Images that are in use are skipped, never deleted. */
    public static function deleteMany(PDO $pdo, array $ids): array
    {
        $deleted = 0;
        $skipped = 0;
        foreach (array_unique(array_map('intval', $ids)) as $id) {
            try {
                self::delete($pdo, $id);
                $deleted++;
            } catch (HttpException $e) {
                $skipped++;
            }
        }
        return ['deleted' => $deleted, 'skipped' => $skipped];
    }
}

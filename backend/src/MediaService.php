<?php
declare(strict_types=1);

/** The image library. Files live in backend/media, the database stores the path. */
final class MediaService
{
    public const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    public const MAX_BYTES = 8 * 1024 * 1024;

    public static function shape(array $r): array
    {
        return [
            'id' => (int) $r['id'],
            'file_name' => $r['file_name'],
            'url' => PageService::mediaUrl($r['file_path']),
            'alt' => $r['alt_text'],
            'mime_type' => $r['mime_type'],
            'size_bytes' => (int) $r['size_bytes'],
            'width' => $r['width'] !== null ? (int) $r['width'] : null,
            'height' => $r['height'] !== null ? (int) $r['height'] : null,
            'created_at' => $r['created_at'],
        ];
    }

    public static function list(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT * FROM media ORDER BY id DESC LIMIT 300')->fetchAll();
        return array_map([self::class, 'shape'], $rows);
    }

    public static function upload(PDO $pdo, ?array $file, int $adminId): array
    {
        if (!$file) {
            throw new HttpException('Choose an image to upload');
        }
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new HttpException('That file is too large. The limit is 8 MB.');
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new HttpException('The upload failed. Try again.');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new HttpException('That file is too large. The limit is 8 MB.');
        }

        // Trust the file contents, not the name or the browser-supplied type.
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $info = @getimagesize($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime]) || $info === false) {
            throw new HttpException('Upload a JPG, PNG, WebP or GIF image');
        }

        $dir = 'uploads/' . gmdate('Y') . '/' . gmdate('m');
        $absolute = dirname(__DIR__) . '/media/' . $dir;
        if (!is_dir($absolute) && !mkdir($absolute, 0755, true) && !is_dir($absolute)) {
            throw new HttpException('The media folder is not writable', 500);
        }

        // Keep a readable name (good for SEO) with a short random ending so names never clash.
        $original = mb_substr(basename((string) $file['name']), 0, 255);
        $stored = self::slugify(pathinfo($original, PATHINFO_FILENAME)) . '-' . bin2hex(random_bytes(3)) . '.' . self::ALLOWED[$mime];
        if (!move_uploaded_file($file['tmp_name'], $absolute . '/' . $stored)) {
            throw new HttpException('The file could not be saved', 500);
        }
        $alt = mb_substr(trim(preg_replace('/[-_]+/', ' ', pathinfo($original, PATHINFO_FILENAME))), 0, 255);

        $pdo->prepare(
            'INSERT INTO media (file_name, file_path, alt_text, mime_type, size_bytes, width, height, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$original, $dir . '/' . $stored, $alt, $mime, (int) $file['size'], $info[0], $info[1], $adminId]);

        $stmt = $pdo->prepare('SELECT * FROM media WHERE id = ?');
        $stmt->execute([$pdo->lastInsertId()]);
        return self::shape($stmt->fetch());
    }

    /** Lowercase, hyphenated, web-safe version of a name (at most 60 characters). */
    public static function slugify(string $name): string
    {
        $slug = strtolower(str_replace('&', ' and ', $name));
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
        if (strlen($slug) > 60) {
            $slug = substr($slug, 0, 60);
            $slug = substr($slug, 0, (int) strrpos($slug, '-') ?: 60);
        }
        return $slug !== '' ? $slug : 'image';
    }

    /**
     * Import a local image file as WebP (command-line use). The source file is left
     * untouched. Images wider than $maxWidth are scaled down, never up.
     */
    public static function importLocal(PDO $pdo, string $path, string $slug, string $alt, int $adminId, int $maxWidth = 1600): array
    {
        $info = @getimagesize($path);
        $source = match ($info[2] ?? 0) {
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => false,
        };
        if (!$source) {
            throw new RuntimeException("Not a readable image: $path");
        }
        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);

        [$w, $h] = [imagesx($source), imagesy($source)];
        if ($w > $maxWidth) {
            $scaled = imagescale($source, $maxWidth, (int) round($h * $maxWidth / $w), IMG_BICUBIC);
            imagedestroy($source);
            $source = $scaled;
            [$w, $h] = [imagesx($source), imagesy($source)];
        }

        $dir = 'uploads/' . gmdate('Y') . '/' . gmdate('m');
        $absolute = dirname(__DIR__) . '/media/' . $dir;
        if (!is_dir($absolute) && !mkdir($absolute, 0755, true) && !is_dir($absolute)) {
            throw new RuntimeException('The media folder is not writable');
        }
        $stored = self::slugify($slug) . '-' . bin2hex(random_bytes(3)) . '.webp';
        imagewebp($source, $absolute . '/' . $stored, 82);
        imagedestroy($source);

        $pdo->prepare(
            'INSERT INTO media (file_name, file_path, alt_text, mime_type, size_bytes, width, height, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([basename($path), $dir . '/' . $stored, $alt, 'image/webp', filesize($absolute . '/' . $stored), $w, $h, $adminId]);

        $stmt = $pdo->prepare('SELECT * FROM media WHERE id = ?');
        $stmt->execute([$pdo->lastInsertId()]);
        return self::shape($stmt->fetch());
    }
}

<?php
declare(strict_types=1);

final class Database
{
    /** Connect to the lite_events database. */
    public static function connect(array $db): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']);
        return new PDO($dsn, $db['user'], $db['pass'], self::options($db) + [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            // Times are stored in UTC.
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
        ]);
    }

    /** Encrypted connection settings, used by cloud databases. */
    private static function options(array $db): array
    {
        if (empty($db['ssl'])) {
            return [];
        }
        $bundle = ['/etc/ssl/certs/ca-certificates.crt', '/etc/pki/tls/certs/ca-bundle.crt'];
        foreach ($bundle as $path) {
            if (is_file($path)) {
                return [PDO::MYSQL_ATTR_SSL_CA => $path, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true];
            }
        }
        return [PDO::MYSQL_ATTR_SSL_CA => '', PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
    }

    /** Health check: is the server reachable, and does the database exist yet? */
    public static function status(array $db): array
    {
        try {
            $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']);
            $pdo = new PDO($dsn, $db['user'], $db['pass'], self::options($db) + [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = ?');
            $stmt->execute([$db['name']]);
            return ['server' => 'connected', 'database_exists' => (bool) $stmt->fetchColumn()];
        } catch (PDOException $e) {
            error_log('DB health check failed: ' . $e->getMessage());
            return ['server' => 'unreachable', 'database_exists' => false];
        }
    }
}

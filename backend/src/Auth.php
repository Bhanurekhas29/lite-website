<?php
declare(strict_types=1);

final class Auth
{
    private const IDLE_SECONDS = 7200; // log out after 2 hours without activity

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_name('lite_admin');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    /** Check credentials. Returns the admin row, or null (same result for every failure). */
    public static function login(PDO $pdo, string $identifier, string $password): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $admin = $stmt->fetch() ?: null;

        // Always verify a hash so timing does not reveal whether the account exists.
        $hash = $admin['password_hash'] ?? password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $valid = password_verify($password, $hash);

        $_SESSION['failed_logins'] = (int) ($_SESSION['failed_logins'] ?? 0);
        if (!$admin || !$valid || !(int) $admin['is_active']) {
            $_SESSION['failed_logins']++;
            usleep(min(3000000, 300000 * $_SESSION['failed_logins']));
            return null;
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['last_activity'] = time();
        $_SESSION['failed_logins'] = 0;
        $pdo->prepare('UPDATE admins SET last_login_at = UTC_TIMESTAMP() WHERE id = ?')->execute([$admin['id']]);

        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
        }
        return $admin;
    }

    /** The signed-in admin (public fields only), or null. */
    public static function user(PDO $pdo): ?array
    {
        $id = $_SESSION['admin_id'] ?? null;
        if (!$id) {
            return null;
        }
        if (time() - (int) ($_SESSION['last_activity'] ?? 0) > self::IDLE_SECONDS) {
            self::logout();
            return null;
        }
        $stmt = $pdo->prepare('SELECT id, username, email, display_name, is_super FROM admins WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        $admin = $stmt->fetch();
        if (!$admin) {
            self::logout();
            return null;
        }
        $_SESSION['last_activity'] = time();
        return self::publicFields($admin);
    }

    public static function publicFields(array $admin): array
    {
        return [
            'id' => (int) $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'display_name' => $admin['display_name'],
            'is_super' => (bool) $admin['is_super'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /** Change the signed-in admin's own password (the current one must be given). */
    public static function changePassword(PDO $pdo, int $id, string $current, string $new): void
    {
        $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
        $stmt->execute([$id]);
        $hash = (string) $stmt->fetchColumn();
        if ($current === '' || !password_verify($current, $hash)) {
            usleep(600000);
            throw new HttpException('Your current password is not correct');
        }
        if (hash_equals($current, $new)) {
            throw new HttpException('Choose a password that is different from the current one');
        }
        PasswordReset::checkPassword($new);
        $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $id]);
        $pdo->prepare('DELETE FROM password_resets WHERE admin_id = ?')->execute([$id]);
    }

    /** Stop with 403 unless the signed-in admin is the super admin. */
    public static function requireSuper(array $user): void
    {
        if (empty($user['is_super'])) {
            Response::json(['error' => 'Only the super admin can manage users'], 403);
            exit;
        }
    }

    /** Stop with 401 unless an admin is signed in. */
    public static function require(PDO $pdo): array
    {
        $user = self::user($pdo);
        if (!$user) {
            Response::json(['error' => 'Please sign in'], 401);
            exit;
        }
        return $user;
    }
}

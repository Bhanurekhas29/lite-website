<?php
declare(strict_types=1);

/** The people who can sign in to the admin panel. Only the super admin manages them. */
final class AdminUsers
{
    private static function shape(array $r): array
    {
        return [
            'id' => (int) $r['id'],
            'username' => $r['username'],
            'email' => $r['email'],
            'display_name' => $r['display_name'],
            'is_super' => (bool) $r['is_super'],
            'is_active' => (bool) $r['is_active'],
            'last_login_at' => $r['last_login_at'] ? str_replace(' ', 'T', $r['last_login_at']) . 'Z' : null,
            'created_at' => $r['created_at'] ? str_replace(' ', 'T', $r['created_at']) . 'Z' : null,
        ];
    }

    public static function list(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT id, username, email, display_name, is_super, is_active, last_login_at, created_at FROM admins ORDER BY is_super DESC, id')->fetchAll();
        return array_map([self::class, 'shape'], $rows);
    }

    private static function row(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('That user no longer exists', 404);
        }
        return $row;
    }

    /** Validate the form. $id is the user being edited (null when adding). */
    private static function clean(PDO $pdo, array $b, ?int $id): array
    {
        $name = mb_substr(trim((string) ($b['display_name'] ?? '')), 0, 120);
        if ($name === '') {
            throw new HttpException('Enter the name');
        }

        $username = strtolower(trim((string) ($b['username'] ?? '')));
        if (!preg_match('/^[a-z0-9._-]{3,40}$/', $username)) {
            throw new HttpException('The username needs 3 to 40 characters: lowercase letters, numbers, dots, dashes or underscores');
        }

        $email = trim((string) ($b['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            throw new HttpException('Enter a valid email address');
        }

        $dup = $pdo->prepare('SELECT username, email FROM admins WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1');
        $dup->execute([$username, $email, $id ?? 0]);
        if ($found = $dup->fetch()) {
            throw new HttpException(strcasecmp($found['username'], $username) === 0 ? 'That username is already used' : 'That email address is already used');
        }

        $password = (string) ($b['password'] ?? '');
        if ($id === null || $password !== '') {
            PasswordReset::checkPassword($password);
        }

        return [$name, $username, $email, $password, !empty($b['is_active']) ? 1 : 0];
    }

    public static function create(PDO $pdo, array $b): array
    {
        [$name, $username, $email, $password, $active] = self::clean($pdo, $b, null);
        $pdo->prepare(
            'INSERT INTO admins (username, email, password_hash, display_name, is_super, is_active) VALUES (?, ?, ?, ?, 0, ?)'
        )->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $name, $active]);
        return self::shape(self::row($pdo, (int) $pdo->lastInsertId()));
    }

    public static function update(PDO $pdo, int $id, array $b, int $currentId): array
    {
        $existing = self::row($pdo, $id);
        [$name, $username, $email, $password, $active] = self::clean($pdo, $b, $id);

        if ($id === $currentId && !$active) {
            throw new HttpException('You cannot switch off your own account');
        }
        if ((int) $existing['is_super'] === 1 && !$active) {
            throw new HttpException('The super admin account must stay active');
        }

        $pdo->prepare('UPDATE admins SET display_name = ?, username = ?, email = ?, is_active = ? WHERE id = ?')
            ->execute([$name, $username, $email, $active, $id]);
        if ($password !== '') {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
            $pdo->prepare('DELETE FROM password_resets WHERE admin_id = ?')->execute([$id]);
        }
        return self::shape(self::row($pdo, $id));
    }

    public static function delete(PDO $pdo, int $id, int $currentId): void
    {
        $existing = self::row($pdo, $id);
        if ($id === $currentId) {
            throw new HttpException('You cannot delete your own account');
        }
        if ((int) $existing['is_super'] === 1) {
            throw new HttpException('The super admin account cannot be deleted');
        }
        $pdo->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
    }
}

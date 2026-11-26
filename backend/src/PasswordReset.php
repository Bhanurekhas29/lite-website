<?php
declare(strict_types=1);

/** "Forgot password": a one-hour link sent by email, then a new password. */
final class PasswordReset
{
    private const LIFETIME_MINUTES = 60;
    private const MIN_GAP_SECONDS = 60;
    private const MAX_PER_HOUR = 5;

    /**
     * Start a reset. The caller always shows the same message, whether or not the account exists,
     * so nobody can use this form to find out which usernames or emails are real.
     */
    public static function request(PDO $pdo, string $identifier, string $baseUrl): void
    {
        $stmt = $pdo->prepare('SELECT id, email, display_name FROM admins WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $admin = $stmt->fetch();
        if (!$admin || !filter_var((string) $admin['email'], FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $recent = $pdo->prepare(
            'SELECT COUNT(*) AS total, MAX(created_at) AS latest FROM password_resets
             WHERE admin_id = ? AND created_at > (UTC_TIMESTAMP() - INTERVAL 1 HOUR)'
        );
        $recent->execute([$admin['id']]);
        $r = $recent->fetch();
        if ((int) $r['total'] >= self::MAX_PER_HOUR) {
            return;
        }
        if ($r['latest'] !== null && strtotime($r['latest'] . ' UTC') > time() - self::MIN_GAP_SECONDS) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $pdo->prepare(
            'INSERT INTO password_resets (admin_id, token_hash, expires_at) VALUES (?, ?, UTC_TIMESTAMP() + INTERVAL ' . self::LIFETIME_MINUTES . ' MINUTE)'
        )->execute([$admin['id'], hash('sha256', $token)]);

        $link = rtrim($baseUrl, '/') . '/admin/reset-password?token=' . $token;
        $body = implode("\r\n", [
            'Hello ' . $admin['display_name'] . ',',
            '',
            'Someone asked to reset the password for your E for Events admin account.',
            'To choose a new password, open this link within ' . self::LIFETIME_MINUTES . ' minutes:',
            '',
            $link,
            '',
            'If you did not ask for this, you can ignore this email. Your password stays the same.',
        ]);
        try {
            Mailer::send(SettingsService::smtp($pdo), (string) $admin['email'], 'Reset your E for Events admin password', $body);
        } catch (Throwable $e) {
            // The page still answers the same way. The cause is only written to the server log.
            error_log('Password reset email not sent: ' . $e->getMessage());
            if (getenv('APP_DEBUG_RESET') === '1') {
                error_log('Password reset link (debug): ' . $link);
            }
        }
    }

    /** Is this link still good? */
    public static function valid(PDO $pdo, string $token): bool
    {
        return self::find($pdo, $token) !== null;
    }

    /** Set the new password and use up the link. */
    public static function reset(PDO $pdo, string $token, string $password): void
    {
        $row = self::find($pdo, $token);
        if (!$row) {
            throw new HttpException('This reset link is no longer valid. Ask for a new one.', 400);
        }
        self::checkPassword($password);
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $row['admin_id']]);
            // This link and any other open links for the account are finished.
            $pdo->prepare('DELETE FROM password_resets WHERE admin_id = ?')->execute([$row['admin_id']]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function checkPassword(string $password): void
    {
        if (mb_strlen($password) < 10 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            throw new HttpException('Use at least 10 characters, with letters and numbers');
        }
        if (mb_strlen($password) > 200) {
            throw new HttpException('That password is too long');
        }
    }

    private static function find(PDO $pdo, string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $stmt = $pdo->prepare(
            'SELECT pr.admin_id FROM password_resets pr JOIN admins a ON a.id = pr.admin_id
             WHERE pr.token_hash = ? AND pr.expires_at > UTC_TIMESTAMP() AND a.is_active = 1 LIMIT 1'
        );
        $stmt->execute([hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    }
}

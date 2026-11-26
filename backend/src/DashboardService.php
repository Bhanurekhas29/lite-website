<?php
declare(strict_types=1);

/** The numbers and lists shown on the admin Overview screen. */
final class DashboardService
{
    public static function overview(PDO $pdo): array
    {
        $count = static fn (string $sql): int => (int) $pdo->query($sql)->fetchColumn();

        $tz = $pdo->query("SELECT value_text FROM settings WHERE group_name = 'general' AND setting_key = 'timezone'")->fetchColumn();
        try {
            $zone = new DateTimeZone($tz ?: 'Europe/London');
        } catch (Throwable) {
            $zone = new DateTimeZone('Europe/London');
        }
        $utc = new DateTimeZone('UTC');

        // Enquiries per day for the last 14 days, counted in the chosen time zone.
        $today = new DateTimeImmutable('today', $zone);
        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = $today->modify("-$i day");
            $days[$d->format('Y-m-d')] = ['date' => $d->format('Y-m-d'), 'label' => $d->format('j M'), 'count' => 0];
        }
        $since = $today->modify('-13 day')->setTimezone($utc)->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare('SELECT created_at FROM enquiries WHERE created_at >= ?');
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $created) {
            $key = (new DateTimeImmutable($created, $utc))->setTimezone($zone)->format('Y-m-d');
            if (isset($days[$key])) {
                $days[$key]['count']++;
            }
        }

        $monthStart = $today->modify('first day of this month')->setTimezone($utc)->format('Y-m-d H:i:s');
        $thisMonth = $pdo->prepare('SELECT COUNT(*) FROM enquiries WHERE created_at >= ?');
        $thisMonth->execute([$monthStart]);

        $types = $pdo->query(
            "SELECT enquiry_type AS name, COUNT(*) AS total FROM enquiries
             WHERE enquiry_type IS NOT NULL AND enquiry_type <> '' GROUP BY enquiry_type ORDER BY total DESC, name LIMIT 6"
        )->fetchAll();

        $recent = $pdo->query(
            'SELECT id, name, company, email, enquiry_type, event_location, status, created_at FROM enquiries ORDER BY created_at DESC, id DESC LIMIT 5'
        )->fetchAll();
        foreach ($recent as &$r) {
            $r['id'] = (int) $r['id'];
            $r['created_at'] = str_replace(' ', 'T', $r['created_at']) . 'Z';
        }
        unset($r);

        return [
            'cards' => [
                'new_enquiries' => $count("SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
                'enquiries_total' => $count('SELECT COUNT(*) FROM enquiries'),
                'enquiries_month' => (int) $thisMonth->fetchColumn(),
                'projects' => $count('SELECT COUNT(*) FROM projects WHERE is_visible = 1'),
                'pages_visible' => $count('SELECT COUNT(*) FROM pages WHERE is_visible = 1'),
                'pages_total' => $count('SELECT COUNT(*) FROM pages'),
                'media' => $count('SELECT COUNT(*) FROM media'),
                'admins' => $count('SELECT COUNT(*) FROM admins WHERE is_active = 1'),
            ],
            'daily' => array_values($days),
            'types' => array_map(fn ($t) => ['name' => $t['name'], 'total' => (int) $t['total']], $types),
            'recent' => $recent,
            'email_ready' => self::emailReady($pdo),
        ];
    }

    /** Has the SMTP email been set up? (Used for a gentle reminder on the Overview.) */
    private static function emailReady(PDO $pdo): bool
    {
        $stmt = $pdo->query("SELECT setting_key, value_text FROM settings WHERE group_name = 'email' AND setting_key IN ('smtp_host', 'smtp_port', 'sender_email')");
        $have = [];
        foreach ($stmt->fetchAll() as $row) {
            if (trim((string) $row['value_text']) !== '') {
                $have[$row['setting_key']] = true;
            }
        }
        return count($have) === 3;
    }
}

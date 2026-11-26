<?php
declare(strict_types=1);

/** Receives enquiries from the public form, checks them against the form's admin settings, and stores them. */
final class EnquiryService
{
    private const MAX_PER_HOUR = 5;

    /** The visible enquiry form section (its items say which fields are active, required and what the choices are). */
    private static function formConfig(PDO $pdo): ?array
    {
        $row = $pdo->query(
            "SELECT s.id, s.page_id FROM sections s JOIN pages p ON p.id = s.page_id
             WHERE s.type = 'enquiry_form' AND s.is_visible = 1 AND p.is_visible = 1 ORDER BY s.id LIMIT 1"
        )->fetch();
        if (!$row) {
            return null;
        }
        foreach (PageService::loadSections($pdo, (int) $row['page_id']) as $section) {
            if ($section['id'] === (int) $row['id']) {
                return $section;
            }
        }
        return null;
    }

    private static function lines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $text) ?: [])));
    }

    /** @return int the new enquiry id, or 0 when a bot was silently ignored */
    public static function submit(PDO $pdo, array $b, string $ip): int
    {
        // Hidden field that people never see. Bots fill it in.
        if (trim((string) ($b['website_url'] ?? '')) !== '') {
            return 0;
        }

        $form = self::formConfig($pdo);
        if (!$form) {
            throw new HttpException('The enquiry form is not available', 404);
        }

        $recent = $pdo->prepare('SELECT COUNT(*) FROM enquiries WHERE ip_address = ? AND created_at > (UTC_TIMESTAMP() - INTERVAL 1 HOUR)');
        $recent->execute([$ip]);
        if ((int) $recent->fetchColumn() >= self::MAX_PER_HOUR) {
            throw new HttpException('You have sent several enquiries recently. Please try again later.', 429);
        }

        $max = ['name' => 150, 'company' => 150, 'email' => 190, 'phone' => 40, 'enquiry_type' => 150,
            'timeframe' => 150, 'location' => 190, 'attendance' => 150, 'message' => 5000];
        $value = ['name' => '', 'company' => '', 'email' => '', 'phone' => '', 'enquiry_type' => '', 'timeframe' => '',
            'location' => '', 'attendance' => '', 'message' => ''];
        $services = [];

        foreach ($form['items'] ?? [] as $item) {
            if (!$item['is_visible']) {
                continue;
            }
            $f = $item['fields'];
            $key = $f['field'];
            $label = $f['label'] ?: 'This field';
            $required = $f['required'] === '1';
            $options = self::lines($f['options'] ?? '');

            if ($key === 'services') {
                $given = is_array($b['services'] ?? null) ? array_map('strval', $b['services']) : [];
                $given = array_values(array_unique(array_filter(array_map('trim', $given))));
                foreach ($given as $g) {
                    if (!in_array($g, $options, true)) {
                        throw new HttpException("Choose valid options for $label");
                    }
                }
                if ($required && !$given) {
                    throw new HttpException("$label is required");
                }
                $services = $given;
                continue;
            }

            $text = mb_substr(trim((string) ($b[$key] ?? '')), 0, $max[$key] ?? 150);
            if ($required && $text === '') {
                throw new HttpException("$label is required");
            }
            if ($text !== '' && $key === 'email' && !filter_var($text, FILTER_VALIDATE_EMAIL)) {
                throw new HttpException('Enter a valid email address');
            }
            if ($text !== '' && $key === 'phone' && !preg_match('/^\+?\d+$/', $text)) {
                throw new HttpException('The telephone number can contain numbers only, with an optional + at the start');
            }
            if ($text !== '' && in_array($key, ['enquiry_type', 'attendance'], true) && !in_array($text, $options, true)) {
                throw new HttpException("Choose a valid option for $label");
            }
            $value[$key] = $text;
        }

        if (empty($b['consent'])) {
            throw new HttpException('Please confirm that you agree to the privacy policy');
        }

        $pdo->prepare(
            'INSERT INTO enquiries (name, company, email, phone, enquiry_type, event_timeframe, event_location, attendance,
                                    services, message, consent_given, consent_at, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, UTC_TIMESTAMP(), ?)'
        )->execute([
            $value['name'], $value['company'] ?: null, $value['email'], $value['phone'] ?: null, $value['enquiry_type'],
            $value['timeframe'] ?: null, $value['location'], $value['attendance'] ?: null,
            $services ? json_encode($services, JSON_UNESCAPED_UNICODE) : null, $value['message'] ?: null, $ip,
        ]);
        $id = (int) $pdo->lastInsertId();

        self::notify($pdo, $value, $services);
        return $id;
    }

    // ------------------------------------------------------------------
    // Admin: reading and managing received enquiries
    // ------------------------------------------------------------------

    /** MySQL UTC time -> ISO 8601, so the browser can show it in the chosen time zone. */
    private static function iso(?string $mysqlTime): ?string
    {
        return $mysqlTime ? str_replace(' ', 'T', $mysqlTime) . 'Z' : null;
    }

    private static function summary(array $r): array
    {
        return [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'company' => $r['company'],
            'email' => $r['email'],
            'enquiry_type' => $r['enquiry_type'],
            'event_location' => $r['event_location'],
            'status' => $r['status'],
            'created_at' => self::iso($r['created_at']),
        ];
    }

    public static function list(PDO $pdo, string $status, string $q, int $page): array
    {
        $perPage = 20;
        $where = [];
        $params = [];
        if (in_array($status, ['new', 'read'], true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        $q = trim($q);
        if ($q !== '') {
            $like = '%' . addcslashes(mb_substr($q, 0, 100), '%_\\') . '%';
            $where[] = '(name LIKE ? OR email LIKE ? OR company LIKE ? OR event_location LIKE ? OR enquiry_type LIKE ? OR message LIKE ?)';
            array_push($params, $like, $like, $like, $like, $like, $like);
        }
        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = $pdo->prepare("SELECT COUNT(*) FROM enquiries $clause");
        $total->execute($params);
        $total = (int) $total->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $rows = $pdo->prepare(
            "SELECT id, name, company, email, enquiry_type, event_location, status, created_at
             FROM enquiries $clause ORDER BY created_at DESC, id DESC LIMIT $perPage OFFSET $offset"
        );
        $rows->execute($params);

        $counts = ['all' => 0, 'new' => 0, 'read' => 0];
        foreach ($pdo->query('SELECT status, COUNT(*) AS n FROM enquiries GROUP BY status')->fetchAll() as $c) {
            $counts[$c['status']] = (int) $c['n'];
            $counts['all'] += (int) $c['n'];
        }

        return [
            'items' => array_map([self::class, 'summary'], $rows->fetchAll()),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'counts' => $counts,
        ];
    }

    private static function row(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare('SELECT * FROM enquiries WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Enquiry not found', 404);
        }
        return $row;
    }

    public static function get(PDO $pdo, int $id): array
    {
        $r = self::row($pdo, $id);
        $services = $r['services'] ? json_decode($r['services'], true) : [];
        return self::summary($r) + [
            'phone' => $r['phone'],
            'timeframe' => $r['event_timeframe'],
            'attendance' => $r['attendance'],
            'services' => is_array($services) ? $services : [],
            'message' => $r['message'],
            'consent_given' => (bool) $r['consent_given'],
            'consent_at' => self::iso($r['consent_at']),
            'ip_address' => $r['ip_address'],
        ];
    }

    public static function setStatus(PDO $pdo, int $id, string $status): void
    {
        self::row($pdo, $id);
        if (!in_array($status, ['new', 'read'], true)) {
            throw new HttpException('Choose new or read');
        }
        $pdo->prepare('UPDATE enquiries SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public static function markAllRead(PDO $pdo): int
    {
        $stmt = $pdo->prepare("UPDATE enquiries SET status = 'read' WHERE status = 'new'");
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function delete(PDO $pdo, int $id): void
    {
        self::row($pdo, $id);
        $pdo->prepare('DELETE FROM enquiries WHERE id = ?')->execute([$id]);
    }

    /** Email the new enquiry to the recipient set in Email settings. Never blocks or fails the submission. */
    private static function notify(PDO $pdo, array $v, array $services): void
    {
        try {
            $stmt = $pdo->prepare("SELECT value_text FROM settings WHERE group_name = 'email' AND setting_key = 'enquiry_recipient'");
            $stmt->execute();
            $to = trim((string) $stmt->fetchColumn());
            if ($to === '') {
                return;
            }
            $lines = [
                'A new enquiry was sent from the website.', '',
                'Name: ' . $v['name'], 'Company: ' . $v['company'], 'Email: ' . $v['email'], 'Phone: ' . $v['phone'],
                'Type of enquiry: ' . $v['enquiry_type'], 'Event date or timeframe: ' . $v['timeframe'],
                'Event location: ' . $v['location'], 'Approximate attendance: ' . $v['attendance'],
                'Services required: ' . implode(', ', $services), '', 'Message:', $v['message'],
            ];
            Mailer::send(SettingsService::smtp($pdo), $to, 'New enquiry from ' . $v['name'], implode("\r\n", $lines));
        } catch (Throwable $e) {
            error_log('Enquiry notification not sent: ' . $e->getMessage());
        }
    }
}

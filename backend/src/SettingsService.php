<?php
declare(strict_types=1);

/**
 * The Settings screens: which fields each has, plus loading, validating and saving.
 * Field types: text, textarea, email, url, int, toggle, select, image, page, secret.
 * A field is identified as "group.key" (the group and key columns of the settings table).
 */
final class SettingsService
{
    private static function f(string $id, string $label, string $type = 'text', array $extra = []): array
    {
        [$group, $key] = explode('.', $id, 2);
        return array_merge(['id' => $id, 'group' => $group, 'key' => $key, 'label' => $label, 'type' => $type], $extra);
    }

    public static function screens(): array
    {
        return [
            'brand' => ['title' => 'Brand', 'cards' => [
                ['title' => 'Identity', 'fields' => [
                    self::f('brand.website_name', 'Website name', 'text', ['required' => true]),
                    self::f('brand.company_name', 'Company name'),
                    self::f('brand.favicon', 'Favicon', 'image', ['help' => 'The small icon in the browser tab. Use a square image.']),
                    self::f('brand.logo_header', 'Header logo', 'image'),
                    self::f('brand.logo_footer', 'Footer logo', 'image', ['help' => 'Shown on the dark footer, so a light version often works best.']),
                ]],
                ['title' => 'Header', 'fields' => [
                    self::f('brand.header_button_label', 'Button text', 'text', ['help' => 'Leave empty to hide the button.']),
                    self::f('brand.header_button_page_id', 'Button links to', 'page'),
                ]],
                ['title' => 'Footer', 'fields' => [
                    self::f('brand.footer_tagline', 'Tagline'),
                    self::f('brand.footer_description', 'Description', 'textarea'),
                    self::f('brand.footer_copyright', 'Copyright line'),
                    self::f('brand.footer_right_text', 'Text on the right of the copyright line'),
                ]],
                ['title' => 'Credits', 'fields' => [
                    self::f('brand.credits_enabled', 'Show the credits row in the footer', 'toggle'),
                    self::f('brand.credits_text', 'Credit text'),
                    self::f('brand.credits_url', 'Credit link', 'url', ['help' => 'The whole credit text links here.']),
                ]],
            ]],
            'contact' => ['title' => 'Contact and social', 'cards' => [
                ['title' => 'Contact details', 'fields' => [
                    self::f('contact.email', 'Email', 'email'),
                    self::f('contact.website', 'Website'),
                    self::f('contact.phone', 'Phone'),
                    self::f('contact.phone_note', 'Note under the phone number', 'text', ['help' => 'For example: Approved business number.']),
                    self::f('contact.address', 'Address', 'textarea'),
                ]],
                ['title' => 'Social', 'fields' => [
                    self::f('social.instagram', 'Instagram', 'text', ['help' => 'A handle such as @yourname, or the full link to the profile. Leave empty to hide the icon.']),
                    self::f('social.facebook', 'Facebook', 'url', ['help' => 'The full link to the page, starting with https://. Leave empty to hide the icon.']),
                    self::f('social.linkedin', 'LinkedIn', 'url', ['help' => 'The full link to the page or profile, starting with https://. Leave empty to hide the icon.']),
                    self::f('social.whatsapp', 'WhatsApp number', 'text', ['help' => 'With the country code, for example +44 7700 900123. Leave empty to hide the icon.']),
                ]],
                ['title' => 'Time zone', 'fields' => [
                    self::f('general.timezone', 'Time zone', 'select', [
                        'options' => ['Europe/London' => 'UK time (Europe/London)', 'Asia/Kolkata' => 'India time (Asia/Kolkata)'],
                        'help' => 'Dates and times in the admin panel are shown in this time zone. UK time is the default.',
                    ]),
                ]],
            ]],
            'seo' => ['title' => 'SEO settings', 'cards' => [
                ['title' => 'Site-wide defaults', 'help' => 'Used when a page has no SEO text of its own. Each page can override these.', 'fields' => [
                    self::f('seo.meta_title', 'Meta title'),
                    self::f('seo.meta_description', 'Meta description', 'textarea'),
                    self::f('seo.keywords_primary', 'Primary keywords'),
                    self::f('seo.keywords_secondary', 'Secondary keywords'),
                ]],
            ]],
            'email' => ['title' => 'Email settings', 'cards' => [
                ['title' => 'SMTP server', 'fields' => [
                    self::f('email.smtp_host', 'SMTP host'),
                    self::f('email.smtp_port', 'Port', 'int'),
                    self::f('email.smtp_encryption', 'Encryption', 'select', ['options' => ['tls' => 'TLS (STARTTLS)', 'ssl' => 'SSL', 'none' => 'None']]),
                    self::f('email.smtp_username', 'Username'),
                    self::f('email.smtp_password', 'Password', 'secret', ['help' => 'Stored encrypted. It is never shown again after saving.']),
                ]],
                ['title' => 'Sender and recipient', 'fields' => [
                    self::f('email.sender_name', 'Sender name'),
                    self::f('email.sender_email', 'Sender email', 'email'),
                    self::f('email.enquiry_recipient', 'Send new enquiries to', 'email', ['help' => 'The address that receives messages from the enquiry form.']),
                ]],
            ]],
        ];
    }

    public static function screen(string $name): array
    {
        $screen = self::screens()[$name] ?? null;
        if (!$screen) {
            throw new HttpException('Settings screen not found', 404);
        }
        return $screen;
    }

    /** @return array<string, array> id => spec */
    private static function fieldsOf(array $screen): array
    {
        $out = [];
        foreach ($screen['cards'] as $card) {
            foreach ($card['fields'] as $field) {
                $out[$field['id']] = $field;
            }
        }
        return $out;
    }

    private static function rows(PDO $pdo): array
    {
        $rows = $pdo->query(
            'SELECT s.*, m.file_path, m.alt_text FROM settings s LEFT JOIN media m ON m.id = s.media_id'
        )->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['group_name'] . '.' . $r['setting_key']] = $r;
        }
        return $out;
    }

    public static function load(PDO $pdo, string $name): array
    {
        $screen = self::screen($name);
        $rows = self::rows($pdo);
        $values = [];
        foreach (self::fieldsOf($screen) as $id => $spec) {
            $row = $rows[$id] ?? null;
            $values[$id] = match ($spec['type']) {
                'image' => ($row && $row['media_id']) ? ['id' => (int) $row['media_id'], 'url' => PageService::mediaUrl($row['file_path']), 'alt' => $row['alt_text']] : null,
                'page' => ($row && $row['value_text'] !== '' && $row['value_text'] !== null) ? (int) $row['value_text'] : null,
                'toggle' => ($row['value_text'] ?? '0') === '1' ? '1' : '0',
                'secret' => ['set' => (bool) ($row && $row['value_text'] !== '' && $row['value_text'] !== null)],
                default => $row['value_text'] ?? '',
            };
        }
        return ['screen' => $screen, 'values' => $values];
    }

    private static function text(mixed $v, int $max): string
    {
        return mb_substr(trim((string) ($v ?? '')), 0, $max);
    }

    /** Validate one submitted value. Returns [value_text, media_id, is_encrypted] or null to leave unchanged. */
    private static function normalize(PDO $pdo, array $spec, mixed $v): ?array
    {
        $label = $spec['label'];
        switch ($spec['type']) {
            case 'text':
                $s = self::text($v, 500);
                if (!empty($spec['required']) && $s === '') {
                    throw new HttpException("$label is required");
                }
                return [$s, null, 0];
            case 'textarea':
                return [self::text($v, 2000), null, 0];
            case 'email':
                $s = self::text($v, 190);
                if ($s !== '' && !filter_var($s, FILTER_VALIDATE_EMAIL)) {
                    throw new HttpException("$label must be a valid email address");
                }
                return [$s, null, 0];
            case 'url':
                $s = self::text($v, 500);
                if ($s !== '' && !preg_match('#^https?://[^\s/$.?\#].[^\s]*$#i', $s)) {
                    throw new HttpException("$label must start with https:// or http://");
                }
                return [$s, null, 0];
            case 'int':
                $s = self::text($v, 6);
                if ($s !== '' && (!ctype_digit($s) || (int) $s < 1 || (int) $s > 65535)) {
                    throw new HttpException("$label must be a number between 1 and 65535");
                }
                return [$s, null, 0];
            case 'toggle':
                return [in_array($v, ['1', 1, true, 'true'], true) ? '1' : '0', null, 0];
            case 'select':
                $s = (string) ($v ?? '');
                if (!array_key_exists($s, $spec['options'])) {
                    throw new HttpException("Choose a valid option for $label");
                }
                return [$s, null, 0];
            case 'image':
                $id = is_array($v) ? (int) ($v['id'] ?? 0) : 0;
                if ($id > 0) {
                    $stmt = $pdo->prepare('SELECT 1 FROM media WHERE id = ?');
                    $stmt->execute([$id]);
                    if (!$stmt->fetchColumn()) {
                        throw new HttpException('That image no longer exists');
                    }
                    return ['', $id, 0];
                }
                return ['', null, 0];
            case 'page':
                $id = (int) ($v ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare('SELECT 1 FROM pages WHERE id = ?');
                    $stmt->execute([$id]);
                    if (!$stmt->fetchColumn()) {
                        throw new HttpException('That page no longer exists');
                    }
                    return [(string) $id, null, 0];
                }
                return ['', null, 0];
            case 'secret':
                if ($v === null) {
                    return ['', null, 1]; // remove the saved secret
                }
                $s = (string) $v;
                if ($s === '') {
                    return null; // unchanged
                }
                return [Crypto::encrypt(mb_substr($s, 0, 500)), null, 1];
        }
        return null;
    }

    public static function save(PDO $pdo, string $name, array $input): array
    {
        $screen = self::screen($name);
        $specs = self::fieldsOf($screen);

        $changes = [];
        foreach ($specs as $id => $spec) {
            if (!array_key_exists($id, $input)) {
                continue;
            }
            $result = self::normalize($pdo, $spec, $input[$id]);
            if ($result !== null) {
                $changes[$id] = $result;
            }
        }

        $find = $pdo->prepare('SELECT id FROM settings WHERE group_name = ? AND setting_key = ?');
        $update = $pdo->prepare('UPDATE settings SET value_text = ?, media_id = ?, is_encrypted = ? WHERE id = ?');
        $insert = $pdo->prepare('INSERT INTO settings (group_name, setting_key, value_text, media_id, is_encrypted) VALUES (?, ?, ?, ?, ?)');

        $pdo->beginTransaction();
        try {
            foreach ($changes as $id => [$text, $media, $encrypted]) {
                [$group, $key] = explode('.', $id, 2);
                $find->execute([$group, $key]);
                $existing = $find->fetchColumn();
                if ($existing) {
                    $update->execute([$text, $media, $encrypted, $existing]);
                } else {
                    $insert->execute([$group, $key, $text, $media, $encrypted]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return self::load($pdo, $name);
    }

    /** SMTP settings ready for the Mailer, or throws if they are incomplete. */
    public static function smtp(PDO $pdo): array
    {
        $rows = self::rows($pdo);
        $get = fn (string $k): string => (string) ($rows["email.$k"]['value_text'] ?? '');

        $password = '';
        if ($get('smtp_password') !== '') {
            $password = Crypto::decrypt($get('smtp_password'));
            if ($password === null) {
                throw new HttpException('The saved SMTP password could not be read. Enter it again and save.');
            }
        }
        if ($get('smtp_host') === '' || $get('smtp_port') === '' || $get('sender_email') === '') {
            throw new HttpException('Fill in and save the SMTP host, port and sender email first');
        }
        return [
            'host' => $get('smtp_host'),
            'port' => (int) $get('smtp_port'),
            'encryption' => $get('smtp_encryption') ?: 'tls',
            'username' => $get('smtp_username'),
            'password' => $password,
            'from_email' => $get('sender_email'),
            'from_name' => $get('sender_name'),
        ];
    }
}

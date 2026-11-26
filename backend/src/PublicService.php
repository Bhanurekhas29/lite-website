<?php
declare(strict_types=1);

/** What the public website is allowed to see. Hidden pages, sections and items never appear here. */
final class PublicService
{
    private static function href(string $slug): string
    {
        return $slug === 'home' ? '/' : '/' . $slug;
    }

    private static function image(mixed $v): ?array
    {
        return is_array($v) ? ['url' => $v['url'], 'alt' => $v['alt'] ?? ''] : null;
    }

    /** Site-wide information: brand, contact, menus. Email (SMTP) settings are never included. */
    public static function site(PDO $pdo): array
    {
        $rows = $pdo->query(
            "SELECT s.group_name, s.setting_key, s.value_text, m.file_path, m.alt_text
             FROM settings s LEFT JOIN media m ON m.id = s.media_id
             WHERE s.group_name IN ('brand', 'contact', 'social', 'general', 'seo')"
        )->fetchAll();
        $s = [];
        foreach ($rows as $r) {
            $s[$r['group_name']][$r['setting_key']] = $r['file_path']
                ? ['url' => PageService::mediaUrl($r['file_path']), 'alt' => $r['alt_text']]
                : ($r['value_text'] ?? '');
        }
        $brand = $s['brand'] ?? [];

        $header = $footer = $hrefById = [];
        $pages = $pdo->query(
            'SELECT id, slug, menu_label, footer_label, show_in_header, show_in_footer
             FROM pages WHERE is_visible = 1 ORDER BY sort_order, id'
        )->fetchAll();
        foreach ($pages as $p) {
            $href = self::href($p['slug']);
            $hrefById[(int) $p['id']] = $href;
            if ($p['show_in_header']) {
                $header[] = ['label' => $p['menu_label'], 'href' => $href];
            }
            if ($p['show_in_footer']) {
                $footer[] = ['label' => $p['footer_label'] ?: $p['menu_label'], 'href' => $href];
            }
        }

        $buttonLabel = trim((string) ($brand['header_button_label'] ?? ''));
        $buttonHref = $hrefById[(int) ($brand['header_button_page_id'] ?? 0)] ?? null;

        return [
            'brand' => [
                'name' => $brand['website_name'] ?? 'E for Events',
                'favicon' => self::image($brand['favicon'] ?? null),
                'logo_header' => self::image($brand['logo_header'] ?? null),
                'logo_footer' => self::image($brand['logo_footer'] ?? null),
                'footer_tagline' => $brand['footer_tagline'] ?? '',
                'footer_description' => $brand['footer_description'] ?? '',
                'footer_copyright' => $brand['footer_copyright'] ?? '',
                'footer_right_text' => $brand['footer_right_text'] ?? '',
                'header_button' => ($buttonLabel !== '' && $buttonHref) ? ['label' => $buttonLabel, 'href' => $buttonHref] : null,
                'credits' => [
                    'enabled' => ($brand['credits_enabled'] ?? '0') === '1',
                    'text' => $brand['credits_text'] ?? '',
                    'url' => $brand['credits_url'] ?? '',
                ],
            ],
            'contact' => $s['contact'] ?? new stdClass(),
            'social' => $s['social'] ?? new stdClass(),
            'seo' => ['title' => $s['seo']['meta_title'] ?? '', 'description' => $s['seo']['meta_description'] ?? ''],
            'timezone' => $s['general']['timezone'] ?? 'Europe/London',
            'menu' => ['header' => $header, 'footer' => $footer],
        ];
    }

    /** A button for the public site, or null when it should not be shown. */
    private static function button(mixed $b, array $pages): ?array
    {
        if (!is_array($b) || trim((string) ($b['label'] ?? '')) === '') {
            return null;
        }
        $href = null;
        if ($b['link_type'] === 'page') {
            $target = $pages[(int) $b['link_page_id']] ?? null;
            // A button that points to a hidden page is hidden too.
            $href = ($target && (int) $target['is_visible']) ? self::href($target['slug']) : null;
        } elseif ($b['link_type'] === 'url') {
            $href = $b['link_url'] ?: null;
        }
        return $href ? ['label' => $b['label'], 'icon' => $b['icon'], 'href' => $href] : null;
    }

    /** A text link: always keeps its label, with a link only when one is set and its target is visible. */
    private static function link(mixed $b, array $pages): ?array
    {
        if (!is_array($b) || trim((string) ($b['label'] ?? '')) === '') {
            return null;
        }
        $button = self::button($b, $pages);
        return ['label' => $b['label'], 'href' => $button['href'] ?? null];
    }

    private static function convert(array $specs, array $values, array $pages): array
    {
        $out = [];
        foreach ($specs as $spec) {
            $v = $values[$spec['key']] ?? null;
            $out[$spec['key']] = match ($spec['type']) {
                'image' => self::image($v),
                'button' => self::button($v, $pages),
                'link' => self::link($v, $pages),
                'action' => (is_array($v) && trim((string) ($v['label'] ?? '')) !== '') ? ['label' => $v['label'], 'icon' => $v['icon']] : null,
                'toggle' => $v === '1',
                default => $v,
            };
        }
        return $out;
    }

    private static function projects(PDO $pdo, bool $featuredOnly): array
    {
        $rows = $pdo->query(
            'SELECT p.*, c.name AS category, c.icon AS category_icon, m.file_path, m.alt_text,
                    h.file_path AS hover_path, h.alt_text AS hover_alt
             FROM projects p JOIN project_categories c ON c.id = p.category_id
             LEFT JOIN media m ON m.id = p.image_media_id
             LEFT JOIN media h ON h.id = p.hover_media_id
             WHERE p.is_visible = 1' . ($featuredOnly ? ' AND p.show_on_home = 1' : '') . '
             ORDER BY p.sort_order, p.id LIMIT 24'
        )->fetchAll();
        $services = $pdo->prepare('SELECT text FROM project_services WHERE project_id = ? ORDER BY sort_order, id');
        $out = [];
        foreach ($rows as $r) {
            $services->execute([$r['id']]);
            $out[] = [
                'id' => (int) $r['id'],
                'title' => $r['title'],
                'category' => $r['category'],
                'category_icon' => $r['category_icon'],
                'image' => $r['file_path'] ? ['url' => PageService::mediaUrl($r['file_path']), 'alt' => $r['alt_text']] : null,
                'hover_image' => $r['hover_path'] ? ['url' => PageService::mediaUrl($r['hover_path']), 'alt' => $r['hover_alt']] : null,
                'year' => $r['event_year'],
                'location' => $r['location'],
                'role' => $r['our_role'],
                'services' => array_column($services->fetchAll(), 'text'),
            ];
        }
        return $out;
    }

    public static function page(PDO $pdo, string $slug): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = ? AND is_visible = 1');
        $stmt->execute([$slug]);
        $page = $stmt->fetch();
        if (!$page) {
            return null;
        }

        $pages = [];
        foreach ($pdo->query('SELECT id, slug, is_visible FROM pages')->fetchAll() as $p) {
            $pages[(int) $p['id']] = $p;
        }

        $sections = [];
        foreach (PageService::loadSections($pdo, (int) $page['id']) as $s) {
            if (!$s['is_visible']) {
                continue;
            }
            $type = Schemas::type($s['type']);
            $section = [
                'id' => $s['id'],
                'type' => $s['type'],
                'fields' => self::convert($type['fields'], (array) $s['fields'], $pages),
            ];
            if (isset($s['items'])) {
                $section['items'] = [];
                foreach ($s['items'] as $item) {
                    if ($item['is_visible']) {
                        $section['items'][] = ['id' => $item['id'], 'fields' => self::convert($type['items']['fields'], $item['fields'], $pages)];
                    }
                }
            }
            if ($s['type'] === 'project_cards') {
                $section['projects'] = self::projects($pdo, ($section['fields']['source'] ?? 'featured') !== 'all');
            }
            $sections[] = $section;
        }

        return [
            'page' => [
                'slug' => $page['slug'],
                'title' => $page['title'],
                'seo_title' => $page['seo_title'] ?: $page['title'],
                'seo_description' => $page['seo_description'] ?? '',
            ],
            'sections' => $sections,
        ];
    }
}

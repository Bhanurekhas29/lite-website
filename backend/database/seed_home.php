<?php
declare(strict_types=1);

// One-time seed: puts the Home page sections and starter text in place.
//   php backend/database/seed_home.php
// Text comes from the approved design screenshots. Images are added later
// from the admin panel. Refuses to run if Home already has sections.

if (PHP_SAPI !== 'cli') {
    exit('Run this script from the command line only.');
}

foreach (['Response', 'HttpException', 'Database', 'Schemas', 'PageService'] as $file) {
    require __DIR__ . '/../src/' . $file . '.php';
}
$config = require __DIR__ . '/../config/config.php';
$pdo = Database::connect($config['db']);

$home = $pdo->query("SELECT id FROM pages WHERE slug = 'home'")->fetchColumn();
if (!$home) {
    exit("Home page not found.\n");
}
$count = $pdo->prepare('SELECT COUNT(*) FROM sections WHERE page_id = ?');
$count->execute([$home]);
if ((int) $count->fetchColumn() > 0) {
    exit("Home already has sections. Nothing was changed.\n");
}

$ids = [];
foreach ($pdo->query('SELECT id, slug FROM pages') as $p) {
    $ids[$p['slug']] = (int) $p['id'];
}
$btn = fn (string $label, string $slug, ?string $icon = null) => [
    'label' => $label, 'icon' => $icon, 'link_type' => 'page', 'link_page_id' => $ids[$slug],
];
$none = ['label' => '', 'icon' => null, 'link_type' => 'none'];

$sections = [
    ['hero', 'Hero', [
        'eyebrow' => 'UK Event Management • Concerts • Event Partnerships',
        'eyebrow_style' => 'none',
        'heading' => 'Your Event Vision. Our Expertise.',
        'heading_accent' => 'Seamlessly Delivered.',
        'accent_new_line' => '1',
        'text1' => 'From live music concerts and entertainment events to weddings, corporate functions and cultural celebrations, E for Events helps turn event plans into well-coordinated experiences.',
        'text2' => 'We work with event organisers, promoters, businesses and private clients across the UK, offering complete event management or specialist support tailored to your requirements.',
        'button1' => $btn('Discuss Your Event', 'contact', 'arrow-right'),
        'button2' => $btn('Become a Partner', 'concert-partnerships', 'chevron-right'),
    ], []],

    ['stats', 'Stats', [], [
        ['number' => '15+', 'label' => 'Years UK Events Experience'],
        ['number' => '100+', 'label' => 'UK Events Organised'],
    ]],

    ['card_grid', 'What we do', [
        'eyebrow' => 'What We Do',
        'eyebrow_style' => 'line',
        'heading' => 'Events Delivered with Care, Coordination and Attention to Detail',
        'intro' => 'Every successful event depends on many moving parts coming together at the right time. We help coordinate the people, suppliers, venues and practical arrangements that make an event work.',
        'button' => $btn('Explore Our Services', 'event-solutions', 'arrow-right'),
    ], [
        ['icon' => 'music', 'title' => 'Concerts & Live Entertainment', 'text' => 'Practical support for concert promoters, touring productions and live entertainment organisers, from event planning and logistics to supplier coordination and on-site delivery.'],
        ['icon' => 'ring', 'title' => 'Hindu & Tamil Weddings', 'text' => 'Thoughtful planning and coordination to help bring together venues, suppliers, ceremonies, celebrations and guest arrangements with complete cultural awareness.'],
        ['icon' => 'briefcase', 'title' => 'Corporate Events & Conferences', 'text' => 'Coordinated event arrangements for business gatherings, conferences, product launches and professional corporate functions.'],
        ['icon' => 'trophy', 'title' => 'Sports Events & Hospitality', 'text' => 'Support with event logistics, guest arrangements and hospitality coordination, tailored to the requirements of each prestigious occasion.'],
        ['icon' => 'people-group', 'title' => 'Cultural & Community Events', 'text' => 'Helping bring communities together through well-organised cultural programmes, authentic celebrations and social initiatives.'],
    ]],

    ['image_cards', 'Our approach', [
        'eyebrow' => 'Our Approach',
        'eyebrow_style' => 'line',
        'heading' => 'The Right Support for Your Event',
    ], [
        ['icon' => 'calendar-days', 'tag' => 'End-to-End Delivery', 'title' => 'Full Event Management',
            'text' => 'Have an event idea but need someone to coordinate the moving parts? We can discuss your requirements and help organise the planning, suppliers, venue arrangements, logistics and event-day delivery within an agreed scope.',
            'button' => $btn('Plan an Event', 'contact', 'arrow-right')],
        ['icon' => 'user-gear', 'tag' => 'Targeted Collaboration', 'title' => 'Specialist Event Support',
            'text' => 'Already have an event team in place? We can discuss supporting specific workstreams, such as artist logistics, accommodation, local transport, venue coordination, hospitality, supplier liaison and production arrangements.',
            'button' => $btn('Discuss a Partnership', 'concert-partnerships', 'arrow-right')],
    ]],

    ['image_text', 'Why E for Events', [
        'eyebrow' => 'Why E for Events?',
        'eyebrow_style' => 'line',
        'heading' => 'A Practical Partner Behind Every Successful Event',
        'badge' => 'UK Touring & Arenas',
        'caption' => 'Flawless on-stage timing backed by experienced logistics.',
    ], [
        ['icon' => 'layer-group', 'title' => 'Flexible engagement', 'text' => 'Support for a complete event or specific work packages tailored to scale.'],
        ['icon' => 'sitemap', 'title' => 'Coordinated delivery', 'text' => 'Help bringing together suppliers, venues and operational requirements seamlessly.'],
        ['icon' => 'sliders', 'title' => 'Tailored solutions', 'text' => 'An approach shaped around the event, audience, location and unique creative brief.'],
        ['icon' => 'handshake', 'title' => 'Collaborative working', 'text' => 'The ability to work alongside your existing team and appointed suppliers with high trust.'],
        ['icon' => 'magnifying-glass', 'title' => 'Attention to detail', 'text' => 'Careful coordination of the practical arrangements that help events run smoothly.'],
    ]],

    ['project_cards', 'Our work', [
        'eyebrow' => 'Our Work',
        'eyebrow_style' => 'line',
        'heading' => 'Bringing Event Plans to Life',
        'intro' => 'Every event has its own requirements, challenges and objectives. Explore examples of our work to understand the types of events we support and the services we provide.',
        'button' => $btn('View Our Work', 'our-work', 'arrow-right'),
    ], []],

    ['cta', 'Partnership banner', [
        'eyebrow' => 'Partnership',
        'eyebrow_style' => 'none',
        'heading' => 'Planning Your Next Concert or Event?',
        'text1' => 'Whether you are an established promoter, an event agency preparing for a busy schedule or an organiser looking for additional delivery support, we would welcome a conversation.',
        'text2' => 'Tell us about your event, your requirements and where you need support. We can discuss an approach that fits your brief.',
        'button1' => $btn('Discuss Your Project', 'contact', 'arrow-right'),
        'button2' => $btn('Become a Partner', 'concert-partnerships', 'chevron-right'),
        'style' => 'faded',
        'align' => 'left',
    ], []],

    ['cta', "Let's make it happen", [
        'eyebrow' => "Let's Make It Happen",
        'eyebrow_style' => 'none',
        'heading' => "Let's Make Your Next Event Happen",
        'text1' => 'Start with an idea, a date or simply a list of things you need help with. Tell us what you are planning and we will discuss the next steps.',
        'button1' => $btn('Get in Touch', 'contact', 'paper-plane'),
        'style' => 'faded',
        'align' => 'center',
    ], []],
];

$insert = $pdo->prepare('INSERT INTO sections (page_id, type, label, sort_order) VALUES (?, ?, ?, ?)');
foreach ($sections as $i => [$type, $label, $fields, $items]) {
    $insert->execute([$home, $type, $label, $i + 1]);
    $id = (int) $pdo->lastInsertId();
    $body = ['fields' => $fields];
    if (isset(Schemas::type($type)['items'])) {
        $body['items'] = array_map(fn ($item) => ['is_visible' => true, 'fields' => $item], $items);
    }
    PageService::saveSection($pdo, $id, $body);
    echo "Added: $label\n";
}
echo "Done.\n";

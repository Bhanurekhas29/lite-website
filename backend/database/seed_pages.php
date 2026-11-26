<?php
declare(strict_types=1);

// One-time seed: sections, starter text and sample projects for the pages after Home.
//   php backend/database/seed_pages.php
// Text comes from the approved design screenshots and is fully editable in the admin.
// A page that already has sections is skipped. Images come from the media library.

if (PHP_SAPI !== 'cli') {
    exit('Run this script from the command line only.');
}

foreach (['Response', 'HttpException', 'Database', 'Schemas', 'PageService'] as $file) {
    require __DIR__ . '/../src/' . $file . '.php';
}
$config = require __DIR__ . '/../config/config.php';
$pdo = Database::connect($config['db']);

$pageIds = [];
foreach ($pdo->query('SELECT id, slug FROM pages') as $p) {
    $pageIds[$p['slug']] = (int) $p['id'];
}

$img = function (string $slug) use ($pdo): array {
    $stmt = $pdo->prepare('SELECT id FROM media WHERE file_path LIKE ?');
    $stmt->execute(['%/' . $slug . '-______.webp']);
    $id = $stmt->fetchColumn();
    if (!$id) {
        throw new RuntimeException("Image not found in the media library: $slug");
    }
    return ['id' => (int) $id];
};
$btn = fn (string $label, string $slug, ?string $icon = 'arrow-right') => [
    'label' => $label, 'icon' => $icon, 'link_type' => 'page', 'link_page_id' => $pageIds[$slug],
];
$anchor = fn (string $label, int $sectionId, ?string $icon = 'arrow-right') => [
    'label' => $label, 'icon' => $icon, 'link_type' => 'url', 'link_url' => '#section-' . $sectionId,
];

$typeOptions = "Concert or live entertainment\nPartnership enquiry\nWedding\nCorporate event\nSports or hospitality\nOther";
$attendanceOptions = "Under 50\n50 to 100\n100 to 250\n250 to 500\n500 to 1,000\nOver 1,000";
$serviceOptions = "Concert or live entertainment\nPartnership with event company\nFull event management\nHindu or Tamil wedding\nCorporate event or conference\nSports, cultural or community event\nSpecific service or subcontracting requirement\nOther";
$formItem = fn (string $field, string $label, string $placeholder, bool $required, string $column, string $options = '', string $help = '') => [
    'field' => $field, 'label' => $label, 'placeholder' => $placeholder, 'help' => $help,
    'required' => $required ? '1' : '0', 'column' => $column, 'options' => $options,
];

/**
 * Pages: slug => list of [type, label, closure(ids) => [fields, items]].
 * The closure receives the section ids by label, so buttons can point to a section.
 */
$pages = [
    'about' => [
        ['hero', 'Page banner', fn () => [[
            'heading' => 'Bringing People Together Through Memorable Experiences',
            'text1' => 'E for Events is an event management and coordination business supporting concerts, live entertainment, weddings, corporate functions, sports hospitality and cultural celebrations.',
            'text2' => 'We believe successful events come from good planning, clear communication and the careful coordination of people, suppliers and logistics. Our role is to help clients organise these moving parts and deliver an event that reflects their objectives.',
            'style' => 'dark', 'align' => 'center', 'background' => $img('pink-lit-ballroom-chandeliers-gala-dinner'),
        ], []]],
        ['image_text', 'Our story', fn () => [[
            'eyebrow' => 'Our Story', 'eyebrow_style' => 'none',
            'heading' => 'Built Around a Passion for Events',
            'text1' => 'Events bring people together, create shared experiences and give organisations and communities opportunities to connect.',
            'text2' => 'E for Events was established to help make those experiences happen through practical event planning, coordination and delivery support. Our approach draws on event experience and an understanding of the work that happens behind the scenes — from organising suppliers and venue requirements to coordinating schedules and guest arrangements.',
            'text3' => 'We recognise that not every client needs the same level of support. Some need a partner to oversee an event from beginning to end. Others already have an experienced team but require additional capacity in specific areas.',
            'text4' => 'We work to understand the brief, define the support required and coordinate the agreed arrangements with care and professionalism.',
            'image' => $img('four-men-posing-together'), 'image2' => $img('audience-seated-hall-portrait'), 'image_position' => 'left',
        ], []]],
        ['steps', 'How we work', fn () => [[
            'eyebrow' => 'Our Approach', 'eyebrow_style' => 'none', 'heading' => 'How We Work',
        ], [
            ['title' => 'Understand the brief', 'text' => "Every project starts with understanding the event's purpose, audience, timings, location and requirements."],
            ['title' => 'Coordinate the details', 'text' => 'We help bring together the people, suppliers and arrangements needed for the agreed scope of work.'],
            ['title' => 'Work as one team', 'text' => 'We collaborate with clients, venues, suppliers and existing event teams to support a coordinated delivery process.'],
        ]]],
        ['card_grid', 'Who we work with', fn () => [[
            'eyebrow' => 'Who We Work With', 'eyebrow_style' => 'lines',
            'heading' => 'Supporting Different Types of Event Clients',
            'intro' => 'Building strong partnerships with the people and businesses who share our vision for quality, innovation, and lasting success.',
            'layout' => 'icon_row', 'columns' => '6', 'align' => 'center',
        ], [
            ['icon' => 'microphone', 'title' => 'Concert promoters and live entertainment organisers'],
            ['icon' => 'briefcase', 'title' => 'Event management agencies and production companies'],
            ['icon' => 'hotel', 'title' => 'Venues and hospitality partners.'],
            ['icon' => 'building', 'title' => 'Corporate clients and organisations.'],
            ['icon' => 'ring', 'title' => 'Wedding clients and families.'],
            ['icon' => 'people-group', 'title' => 'Cultural and community event organisers.'],
        ]]],
        ['cta', 'Call to action', fn () => [[
            'heading' => 'Tell Us What You Are Planning',
            'text1' => 'Whether you need full event management or support with a specific part of a project, we would be happy to discuss your requirements.',
            'button1' => $btn('Discuss Your Event', 'contact', null),
            'background' => $img('concert-stage-lights-large-crowd'), 'style' => 'pink', 'align' => 'center',
        ], []]],
    ],

    'concert-partnerships' => [
        ['hero', 'Hero', fn ($ids) => [[
            'eyebrow' => 'Concert Partnerships', 'eyebrow_style' => 'line',
            'heading' => 'Your Partner Behind the', 'heading_accent' => 'Show',
            'subheading' => 'Concert Logistics, Coordination and Event Delivery Support',
            'text1' => 'Behind every successful live event is a network of people, suppliers and arrangements that need to work together.',
            'text2' => 'E for Events works with concert promoters, entertainment organisers and event companies to coordinate the practical requirements of live events. From artist logistics and hospitality to venue liaison and supplier coordination, we can discuss support tailored to your brief.',
            'text3' => 'Whether you need help managing a wider event or want a partner to take responsibility for specific workstreams, we aim to fit into your delivery model.',
            'button1' => $btn('Discuss a Concert', 'contact'),
            'button2' => $anchor('Explore Partnership Opportunities', $ids['Three ways to work with us'], 'chevron-right'),
            'background' => $img('concert-stage-lights-large-crowd'), 'style' => 'light',
        ], []]],
        ['image_text', 'Partnership proposition', fn () => [[
            'eyebrow' => 'The Partnership Proposition', 'eyebrow_style' => 'line',
            'heading' => 'You Lead the Production.', 'heading_accent' => 'We Help Deliver the Details.', 'accent_new_line' => '1',
            'text1' => 'Organising a concert involves much more than what the audience sees. Travel, accommodation, transport, venue arrangements, supplier schedules, hospitality and event-day coordination all need careful attention.',
            'text2' => 'If you already have a promoter, production manager or event team, E for Events can discuss taking on agreed operational responsibilities to support that team.',
            'text3' => 'We understand that every production is different. Our involvement can be shaped around your brief, timelines, location and requirements.',
            'image' => $img('concert-crowd-hands-raised-stage'), 'frame' => '1',
        ], []]],
        ['card_grid', 'How we can support', fn () => [[
            'eyebrow' => 'How We Can Support Your Event', 'eyebrow_style' => 'line', 'align' => 'center',
            'layout' => 'numbered_cards', 'columns' => '5',
        ], [
            ['icon' => 'plane', 'title' => 'Artist Logistics & Hospitality', 'text' => 'Coordination of artist travel arrangements, accommodation, local transport, hospitality and related requirements in line with the agreed brief.'],
            ['icon' => 'location-dot', 'title' => 'Venue & Site Coordination', 'text' => 'Liaison with venues and relevant suppliers on access, schedules, logistical arrangements and practical requirements.'],
            ['icon' => 'gear', 'title' => 'Production & Supplier Liaison', 'text' => 'Coordination with appointed sound, lighting, LED, staging, catering and other specialist providers as required.'],
            ['icon' => 'calendar-check', 'title' => 'Event-Day Coordination', 'text' => 'Support with agreed schedules, supplier communication, logistics and coordination of operational tasks during delivery.'],
            ['icon' => 'sitemap', 'title' => 'Project Coordination', 'text' => 'Help keep agreed tasks, timings, supplier arrangements and communication aligned across the relevant workstreams.'],
        ]]],
        ['card_grid', 'Three ways to work with us', fn () => [[
            'eyebrow' => 'Three Ways to Work With Us', 'eyebrow_style' => 'line', 'align' => 'center',
            'layout' => 'image_cards', 'columns' => '3',
            'footnote' => 'All services are subject to project scope, availability, supplier arrangements and contract.',
        ], [
            ['image' => $img('theatre-red-seating-band-on-stage'), 'icon' => 'crown', 'title' => 'Full Event Delivery', 'text' => 'For organisers looking for a partner to coordinate an event across multiple stages, we can discuss the overall brief, scope, responsibilities, suppliers and delivery requirements.'],
            ['image' => $img('musical-instruments-collage-magenta-lights'), 'icon' => 'layer-group', 'title' => 'Specialist Work Packages', 'text' => 'For promoters and event agencies with an existing team, we can discuss individual work packages such as artist transport, accommodation, venue liaison, hospitality or selected supplier coordination.'],
            ['image' => $img('stage-lighting-concert-poster-collage'), 'icon' => 'plane', 'title' => 'UK Delivery Support', 'text' => 'For international promoters or organisers planning an event in the UK, we can discuss local coordination requirements, including venue and supplier liaison, transport coordination and other agreed logistical needs.'],
        ]]],
        ['image_text', 'Who we partner with', fn () => [[
            'eyebrow' => 'Who We Partner With', 'eyebrow_style' => 'line',
            'heading' => 'Trusted by Industry Professionals',
            'image' => $img('guests-laughing-talking-event'), 'badge' => 'Great events happen together', 'badge_style' => 'script',
            'items_style' => 'checklist',
        ], [
            ['title' => 'Concert promoters and touring organisers.'],
            ['title' => 'Entertainment agencies and artist management teams.'],
            ['title' => 'Event management and production companies.'],
            ['title' => 'Venues and hospitality providers.'],
            ['title' => 'International organisers delivering UK events.'],
            ['title' => 'Businesses seeking a delivery partner for a specific event.'],
        ]]],
        ['steps', 'Partnership process', fn () => [[
            'eyebrow' => 'Our Partnership Process', 'eyebrow_style' => 'line',
        ], [
            ['icon' => 'comments', 'title' => 'Tell us about your event.', 'text' => 'Share the date, location, scale and type of support required.'],
            ['icon' => 'file-lines', 'title' => 'Define the scope.', 'text' => 'We discuss the tasks, responsibilities, timings and any external suppliers involved.'],
            ['icon' => 'circle-check', 'title' => 'Agree the delivery plan.', 'text' => 'The proposed services, costs, responsibilities and arrangements are confirmed before work begins.'],
            ['icon' => 'sitemap', 'title' => 'Coordinate the agreed work.', 'text' => 'We work with your team and relevant suppliers to deliver the agreed scope.'],
        ]]],
        ['cta', 'Call to action', fn () => [[
            'eyebrow' => "Let's Partner", 'eyebrow_style' => 'none',
            'heading' => 'Looking for a Reliable Event Delivery Partner?',
            'text1' => 'Tell us about your next concert or live event. We would welcome the opportunity to understand your requirements and explore how E for Events could support your team.',
            'button1' => $btn('Start a Partnership Conversation', 'contact'),
            'background' => $img('faded-seated-audience-background'), 'style' => 'faded', 'align' => 'center',
        ], []]],
    ],

    'hindu-tamil-weddings' => [
        ['hero', 'Hero', fn ($ids) => [[
            'eyebrow' => 'Hindu & Tamil Weddings', 'eyebrow_style' => 'line',
            'heading' => 'Hindu & Tamil Weddings,', 'heading_accent' => 'Thoughtfully Coordinated', 'accent_new_line' => '1',
            'subheading' => 'Your Traditions. Your Vision. Your Celebration.',
            'text1' => 'A wedding brings together family, traditions, rituals, suppliers and countless details. E for Events helps coordinate the practical arrangements that allow couples and families to focus on the celebration.',
            'text2' => 'From venue and supplier coordination to event schedules, production liaison and guest arrangements, we can discuss support tailored to your wedding plans.',
            'button1' => $btn('Discuss Your Wedding', 'contact'),
            'button2' => $anchor('Explore Our Services', $ids['Wedding services'], null),
            'style' => 'split', 'image' => $img('hindu-tamil-wedding-couple-floral-mandap'),
        ], []]],
        ['video', 'Wedding film', fn () => [[
            'eyebrow' => 'Cinematic Showcase', 'eyebrow_style' => 'none',
            'heading' => 'Every Wedding Has a Story',
            'intro' => 'Experience the beauty, traditions and unforgettable moments of Asian & Indian weddings, beautifully planned and seamlessly orchestrated by E for Events.',
            'poster' => $img('wedding-table-fruit-decorations'),
            'badge_left' => 'E for Events Cinematic Film', 'badge_right' => '4K Ultra HD',
            'play_label' => 'Watch Wedding Film (4K)', 'caption' => 'Dimple & Harry – London RAC Club',
        ], []]],
        ['card_grid', 'Wedding services', fn () => [[
            'eyebrow' => 'Our Wedding Services', 'eyebrow_style' => 'line', 'align' => 'left',
            'heading' => 'Wedding Services Designed Around', 'heading_accent' => 'Your Celebration.', 'accent_new_line' => '1',
            'intro' => 'We provide end-to-end support for Hindu and Tamil weddings, with a focus on coordination, communication and attention to detail. Our services can be tailored to your specific requirements and the level of support you need.',
            'layout' => 'icon_cards', 'columns' => '3',
        ], [
            ['icon' => 'calendar-days', 'title' => 'Wedding Planning & Coordination', 'text' => 'Help coordinating the event requirements, schedules, suppliers and arrangements within the agreed scope.'],
            ['icon' => 'location-dot', 'title' => 'Venue & Logistics', 'text' => 'Liaison on venue arrangements, access, timings, layouts and practical requirements.'],
            ['icon' => 'users', 'title' => 'Supplier Coordination', 'text' => 'Coordination with appointed decorators, caterers, photographers, videographers, entertainment providers and other suppliers.'],
            ['icon' => 'building', 'title' => 'Ceremony & Reception Coordination', 'text' => 'Support with the running order, timings and coordination of agreed arrangements for ceremonies and receptions.'],
            ['icon' => 'lightbulb', 'title' => 'Sound, Lighting & Production Liaison', 'text' => 'Coordination with appointed technical suppliers to help ensure the agreed production arrangements are aligned with the event schedule.'],
            ['icon' => 'user-gear', 'title' => 'Guest & Event-Day Arrangements', 'text' => 'Practical coordination of agreed guest requirements, schedules and on-the-day logistics.'],
        ]]],
        ['image_text', 'Our approach', fn () => [[
            'eyebrow' => 'Our Approach', 'eyebrow_style' => 'line',
            'heading' => 'A Celebration That Reflects', 'heading_accent' => 'You', 'accent_new_line' => '1',
            'text1' => 'Every family and wedding is different. We take the time to understand your plans, priorities and expectations before discussing the support you need.',
            'text2' => 'Whether you are looking for help with selected arrangements or wider event coordination, our aim is to bring clarity to the planning process and help the agreed details come together smoothly.',
            'image' => $img('brass-diya-flower-garlands-wedding'), 'image_position' => 'left', 'items_style' => 'pills',
        ], [
            ['icon' => 'heart', 'title' => 'Understand Your Vision'],
            ['icon' => 'hand-holding-heart', 'title' => 'Tailored Support'],
            ['icon' => 'star', 'title' => 'Smooth Execution'],
        ]]],
        ['cta', 'Call to action', fn () => [[
            'eyebrow' => 'Ready to Plan Your Wedding?', 'eyebrow_style' => 'lines',
            'heading' => "Let's Talk About Your Wedding",
            'text1' => 'Share your date, venue and plans with us. We can discuss your requirements and help you identify the level of coordination that best suits your celebration.',
            'button1' => $btn('Arrange a Wedding Consultation', 'contact'),
            'background' => $img('floral-wedding-hall-mandap-wide'), 'style' => 'plum', 'align' => 'center',
        ], []]],
    ],

    'event-solutions' => [
        ['hero', 'Hero', fn () => [[
            'eyebrow' => 'Services', 'eyebrow_style' => 'line',
            'heading' => 'Event Services Designed Around', 'heading_accent' => 'Your Requirements', 'accent_new_line' => '1',
            'subheading' => 'One Event Partner. Flexible Ways to Work Together.',
            'text1' => 'No two events are the same. Some require complete planning and coordination, while others need additional support in a few specialist areas. E for Events offers a flexible approach to event management, logistics and supplier coordination, helping clients identify the support they need for their event.',
            'button1' => $btn('Discuss Your Requirements', 'contact'),
            'background' => $img('pink-lit-ballroom-chandeliers-gala-dinner'), 'style' => 'light',
        ], []]],
        ['card_grid', 'Service categories', fn () => [[
            'eyebrow' => 'Our Service Categories', 'eyebrow_style' => 'line', 'align' => 'left',
            'heading' => 'A Wide Range of Event Services.', 'heading_accent' => 'For Every Occasion.', 'accent_new_line' => '1',
            'intro' => 'From large-scale concerts to intimate celebrations, we provide professional coordination and support across a wide range of event types. Explore our core service categories below.',
            'layout' => 'thumb_cards', 'columns' => '3',
        ], [
            ['icon' => 'calendar-days', 'image' => $img('business-conference-round-tables'), 'title' => 'Event Planning & Management', 'text' => 'Support with planning, schedules, supplier coordination, logistics and delivery across the agreed scope of an event.'],
            ['icon' => 'music', 'image' => $img('live-band-stage-drums-keyboards'), 'title' => 'Concerts & Live Entertainment', 'text' => 'Coordination support for live shows, concerts and entertainment events, including artist logistics, hospitality, venue liaison and supplier arrangements.'],
            ['icon' => 'users', 'image' => $img('men-posing-beside-event-banner'), 'title' => 'Artist & Guest Logistics', 'text' => 'Coordination of travel, accommodation, local transport, hospitality and related arrangements according to the brief.'],
            ['icon' => 'location-dot', 'image' => $img('community-arts-venue-collage'), 'title' => 'Venue & Supplier Coordination', 'text' => 'Liaison with venues and appointed suppliers to help align requirements, schedules, access and practical arrangements.'],
            ['icon' => 'gear', 'image' => $img('musical-instruments-collage-magenta-lights'), 'title' => 'Production Coordination', 'text' => 'Coordination with specialist sound, lighting, LED, staging and other technical suppliers. Technical services are delivered by appropriately appointed providers where applicable.'],
            ['icon' => 'briefcase', 'image' => $img('group-photo-event-attendees'), 'title' => 'Corporate Events & Conferences', 'text' => 'Planning and coordination support for conferences, launches, business events and professional gatherings.'],
            ['icon' => 'trophy', 'image' => $img('sports-trophies-cups-table'), 'title' => 'Sports Events & Hospitality', 'text' => 'Support with event logistics, guest arrangements and hospitality coordination.'],
            ['icon' => 'people-group', 'image' => $img('seated-audience-watching-performance'), 'title' => 'Cultural & Community Events', 'text' => 'Coordination support for cultural celebrations, community programmes and social events.'],
            ['icon' => 'heart', 'image' => $img('hindu-tamil-wedding-fire-ritual'), 'title' => 'Hindu & Tamil Weddings', 'text' => 'Planning and coordination support for wedding ceremonies, receptions, suppliers and guest arrangements.'],
        ]]],
        ['card_grid', 'Packages', fn () => [[
            'eyebrow' => 'Packages', 'eyebrow_style' => 'line',
            'heading' => 'Full Management or Individual Services',
            'intro' => 'Whether you need complete event management or support in specific areas, we can tailor our services to suit your requirements.',
            'layout' => 'intro_side', 'columns' => '3', 'image' => $img('audience-seated-hall-wide'),
        ], [
            ['image' => $img('audience-glowing-phone-lights'), 'icon' => 'crown', 'title' => 'End-to-End Event Management', 'text' => 'For clients seeking a partner to coordinate multiple elements of an event, from initial planning and supplier coordination to event-day delivery.'],
            ['image' => $img('red-theatre-stage-view'), 'icon' => 'user-gear', 'title' => 'Specialist Event Support', 'text' => 'For organisers who already have a team and need help with selected requirements, such as artist logistics, accommodation, transport, venue coordination or supplier liaison.'],
            ['image' => $img('four-men-posing-together'), 'icon' => 'handshake', 'title' => 'Partnership & Subcontracting', 'text' => 'For agencies, promoters and event companies seeking a delivery partner for defined work packages or agreed operational responsibilities.'],
        ]]],
        ['steps', 'How we work', fn () => [[
            'eyebrow' => 'Our Process', 'eyebrow_style' => 'line', 'heading' => 'How We Work',
            'intro' => 'A clear and structured approach to help us deliver your event successfully.',
        ], [
            ['icon' => 'comments', 'title' => 'Understand', 'text' => 'We discuss your event, objectives, timings and requirements.'],
            ['icon' => 'file-lines', 'title' => 'Scope', 'text' => 'We establish which services are needed and who is responsible for each element.'],
            ['icon' => 'calendar-check', 'title' => 'Plan', 'text' => 'We agree the approach, costs, timings and relevant supplier arrangements.'],
            ['icon' => 'sliders', 'title' => 'Deliver', 'text' => 'We coordinate the agreed work and communicate with the relevant parties.'],
            ['icon' => 'star', 'title' => 'Review', 'text' => 'Where appropriate, we discuss feedback and lessons for future events.'],
        ]]],
        ['cta', 'Call to action', fn () => [[
            'eyebrow' => 'Need a Specific Service?', 'eyebrow_style' => 'line',
            'heading' => 'Need a Specific Service?',
            'text1' => 'You do not need to commission a complete event-management package to speak to us. Tell us which parts of your event need support, and we can discuss a suitable scope.',
            'button1' => $btn('Request an Event Proposal', 'contact'),
            'background' => $img('audience-glowing-phone-lights'), 'style' => 'faded', 'align' => 'left',
        ], []]],
    ],

    'sports-events-hospitality' => [
        ['hero', 'Hero', fn () => [[
            'eyebrow' => 'Sports Events & Hospitality', 'eyebrow_style' => 'line',
            'heading' => 'Bringing People Together Through', 'heading_accent' => 'Sport',
            'text1' => 'From sporting occasions and corporate hospitality to sponsor events and group experiences, E for Events helps coordinate the details that make sporting events memorable.',
            'button1' => $btn('Plan Your Sports Event', 'contact'),
            'button2' => $btn('Discuss a Partnership', 'concert-partnerships', null),
            'background' => $img('sports-trophies-cups-table'), 'style' => 'light',
        ], []]],
        ['video', 'Showcase', fn () => [[
            'eyebrow' => 'Cinematic Showcase', 'eyebrow_style' => 'none',
            'heading' => 'Welcome to E for Events',
            'intro' => "Experience the glamour, precision and unforgettable atmosphere of high-profile gala dinners and luxury corporate evenings, flawlessly delivered by E for Events across London's finest ballrooms.",
            'poster' => $img('gala-dinner-speaker-presentation'),
            'badge_left' => 'E for Events 4K Showcase', 'badge_right' => 'Ultra HD',
            'caption' => 'The Dorchester Mayfair Gala Dinner & Awards',
        ], []]],
        ['image_text', 'About our sports events', fn () => [[
            'eyebrow' => 'About Our Sports Events', 'eyebrow_style' => 'none',
            'heading' => 'Great Sporting Experiences Start', 'heading_accent' => 'Behind the Scenes', 'accent_new_line' => '1',
            'text1' => 'Sport brings people together—whether supporters are enjoying a live match, colleagues are attending corporate hospitality, sponsors are connecting with guests or organisations are hosting a sporting celebration.',
            'text2' => "Behind every successful occasion are practical arrangements that need to work together. E for Events helps clients coordinate event logistics, venues, suppliers, guest arrangements and the wider experience, according to each project's requirements.",
            'text3' => 'Whether you are organising a corporate sporting event, planning a group hospitality experience or looking for additional support for an existing event, we can discuss a solution tailored to your plans.',
            'image' => $img('sports-trophies-cups-table'), 'image_position' => 'left', 'frame' => '1',
        ], []]],
        ['card_grid', 'Sports event services', fn () => [[
            'eyebrow' => 'Our Sports Event Services', 'eyebrow_style' => 'none', 'align' => 'center',
            'heading' => 'Our Sports Event Services', 'layout' => 'icon_cards', 'columns' => '3',
        ], [
            ['icon' => 'briefcase', 'title' => 'Corporate Sports Hospitality', 'text' => 'Coordination support for businesses entertaining clients, recognising employees or building relationships through sporting experiences. This may include venue coordination, guest hospitality arrangements, guest communication, schedules and supplier liaison.'],
            ['icon' => 'trophy', 'title' => 'Corporate Sporting Events', 'text' => 'Support for company sports days, networking events, client entertainment and team celebrations, tailored to the audience, venue and objectives.'],
            ['icon' => 'truck', 'title' => 'Sports Event Logistics', 'text' => 'Coordination of agreed requirements, including venue liaison, schedules, transport arrangements, supplier communication and practical event-day support.'],
            ['icon' => 'handshake', 'title' => 'Sponsor & Partner Events', 'text' => "Coordination support for sponsor gatherings, partner receptions, promotional activities and related events, working alongside the client's marketing and event teams."],
            ['icon' => 'people-group', 'title' => 'Group Sporting Experiences', 'text' => 'Support with planning group attendance at sporting occasions, including agreed guest arrangements, schedules, transport coordination and hospitality requirements.'],
            ['icon' => 'champagne-glasses', 'title' => 'Sports Dinners & Celebrations', 'text' => 'Planning and coordination support for awards evenings, sports club celebrations, presentation dinners and related social occasions.'],
        ]]],
        ['image_text', 'Who we work with', fn () => [[
            'eyebrow' => 'Who We Work With', 'eyebrow_style' => 'none',
            'heading' => 'A Flexible Partner for', 'heading_accent' => 'Sports Organisations and Businesses', 'accent_new_line' => '1',
            'image' => $img('badminton-game-sports-hall'), 'items_style' => 'iconlist',
            'note' => 'We welcome conversations about full event coordination or selected work packages, depending on your requirements.',
        ], [
            ['icon' => 'briefcase', 'title' => 'Corporate teams planning client entertainment or employee experiences.'],
            ['icon' => 'users', 'title' => 'Sports clubs and associations planning dinners, celebrations or gatherings.'],
            ['icon' => 'calendar-days', 'title' => 'Sports event organisers and event management companies.'],
            ['icon' => 'hotel', 'title' => 'Hospitality and venue partners.'],
            ['icon' => 'heart', 'title' => 'Sponsors and businesses organising partner events.'],
            ['icon' => 'flag', 'title' => 'Groups looking to coordinate a sporting occasion.'],
        ]]],
        ['image_cards', 'Support options', fn () => [[
            'eyebrow' => 'Support Options', 'eyebrow_style' => 'none',
            'heading' => 'Full Event Management or', 'heading_accent' => 'Specialist Support', 'layout' => 'stacked',
        ], [
            ['image' => $img('four-men-posing-together'), 'icon' => 'clipboard-list', 'title' => 'Complete Event Coordination', 'text' => 'For clients who want support coordinating multiple elements of a sports-related event, from initial planning and venue arrangements to supplier liaison and agreed event-day delivery.'],
            ['image' => $img('group-photo-event-attendees'), 'icon' => 'gear', 'title' => 'Specialist Operational Support', 'text' => 'For event companies, corporate teams and organisers who already have a plan but need help with specific arrangements, logistics, hospitality coordination or supplier liaison.'],
        ]]],
        ['cta', 'Call to action', fn () => [[
            'eyebrow' => "Let's Make It Happen", 'eyebrow_style' => 'none',
            'heading' => 'Planning Your Next Sports Event?',
            'text1' => 'Whether you are organising corporate hospitality, a group sporting experience or a larger sports-related event, E for Events would welcome the opportunity to discuss your plans.',
            'button1' => $btn('Discuss Your Sports Event', 'contact'),
            'button2' => $btn('Become a Partner', 'concert-partnerships', null),
            'background' => $img('badminton-game-sports-hall'), 'style' => 'pink', 'align' => 'center',
        ], []]],
    ],

    'our-work' => [
        ['hero', 'Hero', fn () => [[
            'eyebrow' => 'Our Work', 'eyebrow_style' => 'line',
            'heading' => 'Our Work. Our Approach.', 'heading_accent' => 'Our Events.', 'accent_new_line' => '1',
            'text1' => 'Every event involves its own people, timings, suppliers and logistical requirements. Our work reflects the importance of bringing those elements together around a clear plan.',
            'text2' => 'Explore examples of events and projects that demonstrate the types of coordination and support E for Events provides.',
            'background' => $img('pink-faded-concert-stage-background'), 'style' => 'light',
        ], []]],
        ['project_cards', 'Featured projects', fn () => [[
            'eyebrow' => 'Featured Event Projects', 'eyebrow_style' => 'line',
            'heading' => 'Real Events. Practical Support.', 'heading_accent' => 'Across Every Type of Occasion.', 'accent_new_line' => '1',
            'intro' => 'Each project shows how we work behind the scenes to coordinate suppliers, manage logistics and keep everything on track — so you can focus on your event.',
            'source' => 'all', 'style' => 'detailed',
        ], []]],
        ['image_text', 'Our approach', fn () => [[
            'eyebrow' => 'Our Approach', 'eyebrow_style' => 'line',
            'heading' => 'More Than What Happens on Stage',
            'text1' => 'An event is only as strong as the planning and coordination behind it. We focus on the practical requirements that help keep agreed workstreams organised and aligned.',
            'text2' => 'From communicating with suppliers to coordinating schedules and venue arrangements, our role is shaped by the specific requirements of each project.',
            'image' => $img('seated-audience-watching-performance'), 'image_position' => 'left', 'items_style' => 'pills',
        ], [
            ['icon' => 'headset', 'title' => 'Supplier Communication'],
            ['icon' => 'calendar-check', 'title' => 'Schedule Coordination'],
            ['icon' => 'location-dot', 'title' => 'Venue Arrangements'],
            ['icon' => 'truck', 'title' => 'Logistics & Transport'],
            ['icon' => 'heart', 'title' => 'On-Day Support'],
        ]]],
        ['cta', 'Call to action', fn () => [[
            'eyebrow' => 'Planning Something Similar?', 'eyebrow_style' => 'line',
            'heading' => "Let's Bring Your Event", 'heading_accent' => 'Together', 'accent_new_line' => '1',
            'text1' => 'If you are a promoter, event company, business or private client looking for event support, we would welcome the opportunity to discuss your plans.',
            'button1' => $btn('Discuss Your Project', 'contact'),
            'background' => $img('floral-wedding-hall-mandap-wide'), 'style' => 'faded', 'align' => 'left',
        ], []]],
    ],

    'contact' => [
        ['hero', 'Page banner', fn () => [[
            'eyebrow' => 'Contact', 'eyebrow_style' => 'none',
            'heading' => "Let's Talk About Your Next Event",
            'text1' => 'Whether you are planning a concert, exploring an event partnership or looking for help with a specific service, we would like to hear from you.',
            'text2' => 'Share a few details about your event and the support you need. We will use this information to understand your brief and discuss the appropriate next steps.',
            'background' => $img('red-theatre-stage-band-wide'), 'style' => 'dark',
        ], []]],
        ['contact_info', 'Get in touch', fn () => [[
            'eyebrow' => 'Get in Touch', 'eyebrow_style' => 'line', 'heading' => 'E for Events',
            'image' => $img('gala-dinner-speaker-presentation'),
        ], []]],
        ['enquiry_form', 'Enquiry form', fn () => [[
            'eyebrow' => 'Enquiry Form', 'eyebrow_style' => 'line', 'heading' => 'Tell Us About Your Event',
            'intro' => "The more you can tell us about your plans, the better we can understand the support you need. If your plans are still at an early stage, that's absolutely fine.",
            'consent_before' => 'I confirm that I have read and agree to the ',
            'privacy_link' => ['label' => 'Privacy Policy', 'icon' => null, 'link_type' => 'none'],
            'consent_after' => ' and understand how my information will be used.',
            'submit' => ['label' => 'Send Your Enquiry', 'icon' => 'paper-plane', 'link_type' => 'none'],
            'success' => "Thank you. We've received your enquiry and will be in touch soon.",
        ], [
            $formItem('name', 'Your name', 'Enter your name', true, 'left'),
            $formItem('company', 'Company / organisation', 'Enter company name (optional)', false, 'left'),
            $formItem('email', 'Email address', 'Enter your email address', true, 'left'),
            $formItem('phone', 'Telephone', 'Enter your telephone number (optional)', false, 'left'),
            $formItem('enquiry_type', 'Type of enquiry', 'Select enquiry type', true, 'left', $typeOptions),
            $formItem('timeframe', 'Event date or timeframe', 'e.g. 12th Dec 2025 or Q1 2026 (optional)', false, 'left'),
            $formItem('location', 'Event location', 'Enter event location', true, 'left'),
            $formItem('attendance', 'Approximate attendance', 'Select attendance range (optional)', false, 'right', $attendanceOptions),
            $formItem('services', 'Services required', '', true, 'right', $serviceOptions, 'Select multiple'),
            $formItem('message', 'Tell us more', 'Tell us about your event, requirements or any additional information...', false, 'right'),
        ]]],
        ['cta', 'Call to action', fn ($ids) => [[
            'eyebrow' => "Let's Create Something", 'eyebrow_style' => 'none',
            'heading' => 'Memorable Together',
            'text1' => "We're here to help you bring your vision to life.",
            'button1' => $anchor('Get in Touch', $ids['Enquiry form'], 'paper-plane'),
            'background' => $img('faded-seated-audience-background'), 'style' => 'plum', 'align' => 'center',
        ], []]],
    ],
];

$insertSection = $pdo->prepare('INSERT INTO sections (page_id, type, label, sort_order) VALUES (?, ?, ?, ?)');
$hasSections = $pdo->prepare('SELECT COUNT(*) FROM sections WHERE page_id = ?');

foreach ($pages as $slug => $sections) {
    $pageId = $pageIds[$slug];
    $hasSections->execute([$pageId]);
    if ((int) $hasSections->fetchColumn() > 0) {
        echo "Skipped $slug (already has sections)\n";
        continue;
    }

    $ids = [];
    foreach ($sections as $i => [$type, $label]) {
        $insertSection->execute([$pageId, $type, $label, $i + 1]);
        $ids[$label] = (int) $pdo->lastInsertId();
    }
    foreach ($sections as [$type, $label, $content]) {
        [$fields, $items] = $content($ids);
        $body = ['fields' => $fields];
        if (isset(Schemas::type($type)['items'])) {
            $body['items'] = array_map(fn ($item) => ['is_visible' => true, 'fields' => $item], $items);
        }
        PageService::saveSection($pdo, $ids[$label], $body);
    }
    echo "Seeded $slug (" . count($sections) . " sections)\n";
}

// ---- Sample projects (from the Our Work design). Editable in the Projects module (next step). ----
if ((int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn() === 0) {
    $category = $pdo->prepare('INSERT INTO project_categories (name, icon, sort_order) VALUES (?, ?, ?)');
    $project = $pdo->prepare('INSERT INTO projects (category_id, title, image_media_id, event_year, location, our_role, show_on_home, sort_order) VALUES (?, ?, ?, ?, ?, ?, 1, ?)');
    $service = $pdo->prepare('INSERT INTO project_services (project_id, text, sort_order) VALUES (?, ?, ?)');

    $projects = [
        ['Concert', 'music', 'Concerts & Live Events', 'seated-audience-watching-performance', 'Event coordination & supplier liaison',
            ['Artist logistics & hospitality', 'Venue liaison & production coordination', 'Supplier management', 'Event-day coordination']],
        ['Wedding', 'ring', 'Weddings & Special Occasions', 'wedding-table-fruit-decorations', 'Wedding planning & coordination',
            ['Venue & supplier coordination', 'Ceremony & reception support', 'Guest arrangements', 'On-day coordination']],
        ['Corporate & Community', 'people-group', 'Corporate, Cultural & Community Events', 'men-posing-beside-event-banner', 'Full event coordination',
            ['Venue & logistics coordination', 'Supplier management', 'Technical production support', 'Event-day coordination']],
    ];
    foreach ($projects as $n => [$catName, $catIcon, $title, $image, $role, $services]) {
        $category->execute([$catName, $catIcon, $n + 1]);
        $categoryId = (int) $pdo->lastInsertId();
        $project->execute([$categoryId, $title, $img($image)['id'], '2024', 'London, UK', $role, $n + 1]);
        $projectId = (int) $pdo->lastInsertId();
        foreach ($services as $s => $text) {
            $service->execute([$projectId, $text, $s + 1]);
        }
    }
    echo "Seeded 3 sample projects\n";
}
echo "Done.\n";

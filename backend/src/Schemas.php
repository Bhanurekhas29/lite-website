<?php
declare(strict_types=1);

/**
 * Section types and their editable fields. This is the single source of truth:
 * the admin panel builds its forms from it, and the API validates saves against it.
 *
 * Field types:
 *   text, textarea, choices (a list of short options), url, toggle, select (needs 'options'), image, icon,
 *   button (label + optional icon + link, hidden when it has no link),
 *   link (label + link, always shows its label)
 */
final class Schemas
{
    private static function eyebrow(): array
    {
        return [
            ['key' => 'eyebrow', 'label' => 'Eyebrow text', 'type' => 'text'],
            ['key' => 'eyebrow_style', 'label' => 'Eyebrow style', 'type' => 'select', 'default' => 'line',
                'options' => ['none' => 'Plain', 'line' => 'Line on the right', 'lines' => 'Lines on both sides']],
        ];
    }

    private static function heading(): array
    {
        return [
            ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
            ['key' => 'heading_accent', 'label' => 'Accent text (pink italic)', 'type' => 'text'],
            ['key' => 'accent_new_line', 'label' => 'Show accent text on a new line', 'type' => 'toggle', 'default' => '0'],
        ];
    }

    /** An optional faded image behind a section. A pale veil keeps the text readable. */
    private static function background(): array
    {
        return [
            ['key' => 'background', 'label' => 'Faded background image (optional)', 'type' => 'image'],
            ['key' => 'background_strength', 'label' => 'How visible the background is', 'type' => 'select', 'default' => 'light',
                'options' => ['light' => 'Very faint', 'soft' => 'Soft', 'medium' => 'Clearer']],
        ];
    }

    private static function iconItem(string $label = 'List item', int $max = 10): array
    {
        return [
            'label' => $label,
            'max' => $max,
            'fields' => [
                ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
            ],
        ];
    }

    public static function types(): array
    {
        return [
            'hero' => [
                'label' => 'Hero banner',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'subheading', 'label' => 'Subheading (bold line under the heading)', 'type' => 'text'],
                    ['key' => 'text1', 'label' => 'Paragraph 1', 'type' => 'textarea'],
                    ['key' => 'text2', 'label' => 'Paragraph 2', 'type' => 'textarea'],
                    ['key' => 'text3', 'label' => 'Paragraph 3', 'type' => 'textarea'],
                    ['key' => 'button1', 'label' => 'Primary button', 'type' => 'button'],
                    ['key' => 'button2', 'label' => 'Secondary button', 'type' => 'button'],
                    ['key' => 'background', 'label' => 'Background image', 'type' => 'image'],
                    ['key' => 'background_strength', 'label' => 'How visible the background is (framed style only)', 'type' => 'select', 'default' => 'light',
                        'options' => ['light' => 'Very faint', 'soft' => 'Soft', 'medium' => 'Clearer']],
                    ['key' => 'style', 'label' => 'Banner style', 'type' => 'select', 'default' => 'dark',
                        'options' => ['dark' => 'Dark image with white text', 'light' => 'Light fade with dark text', 'split' => 'Text with a framed image on the right']],
                    ['key' => 'align', 'label' => 'Text alignment', 'type' => 'select', 'default' => 'left',
                        'options' => ['left' => 'Left', 'center' => 'Centred']],
                    ['key' => 'image', 'label' => 'Side image (for the framed style)', 'type' => 'image'],
                ]),
            ],
            'stats' => [
                'label' => 'Stats strip',
                'fields' => self::background(),
                'items' => [
                    'label' => 'Stat',
                    'max' => 6,
                    'fields' => [
                        ['key' => 'number', 'label' => 'Number (for example 15+)', 'type' => 'text'],
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                    ],
                ],
            ],
            'card_grid' => [
                'label' => 'Card grid',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'intro', 'label' => 'Intro paragraph', 'type' => 'textarea'],
                    ['key' => 'align', 'label' => 'Heading alignment', 'type' => 'select', 'default' => 'center',
                        'options' => ['center' => 'Centred', 'left' => 'Left']],
                    ['key' => 'layout', 'label' => 'Card layout', 'type' => 'select', 'default' => 'image_cards',
                        'options' => [
                            'image_cards' => 'Image cards (photo on top, icon badge)',
                            'icon_cards' => 'Icon cards (icon, title, text)',
                            'numbered_cards' => 'Numbered icon cards (01, 02, 03)',
                            'icon_row' => 'Icon row (centred icon with a caption)',
                            'thumb_cards' => 'Cards with a small image at the top right',
                            'intro_side' => 'Intro card on the left, image cards on the right',
                        ]],
                    ['key' => 'columns', 'label' => 'Cards per row on large screens', 'type' => 'select', 'default' => '3',
                        'options' => ['2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6']],
                    ['key' => 'image', 'label' => 'Image for the intro card (intro-card layout only)', 'type' => 'image'],
                    ['key' => 'button', 'label' => 'Button below the cards', 'type' => 'button'],
                    ['key' => 'footnote', 'label' => 'Small note below the cards', 'type' => 'text'],
                ], self::background()),
                'items' => [
                    'label' => 'Card',
                    'max' => 12,
                    'fields' => [
                        ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                    ],
                ],
            ],
            'image_cards' => [
                'label' => 'Image cards',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'layout', 'label' => 'Card layout', 'type' => 'select', 'default' => 'side',
                        'options' => ['side' => 'Image on the left of the text', 'stacked' => 'Image on top of the text']],
                ], self::background()),
                'items' => [
                    'label' => 'Card',
                    'max' => 4,
                    'fields' => [
                        ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon'],
                        ['key' => 'tag', 'label' => 'Small label', 'type' => 'text'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                        ['key' => 'button', 'label' => 'Button', 'type' => 'button'],
                    ],
                ],
            ],
            'image_text' => [
                'label' => 'Image with text',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'text1', 'label' => 'Paragraph 1', 'type' => 'textarea'],
                    ['key' => 'text2', 'label' => 'Paragraph 2', 'type' => 'textarea'],
                    ['key' => 'text3', 'label' => 'Paragraph 3', 'type' => 'textarea'],
                    ['key' => 'text4', 'label' => 'Paragraph 4', 'type' => 'textarea'],
                    ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                    ['key' => 'image2', 'label' => 'Second image (optional)', 'type' => 'image'],
                    ['key' => 'image_position', 'label' => 'Image position', 'type' => 'select', 'default' => 'right',
                        'options' => ['right' => 'Right', 'left' => 'Left']],
                    ['key' => 'image_shape', 'label' => 'Image shape', 'type' => 'select', 'default' => 'portrait',
                        'options' => ['portrait' => 'Tall (about the same size as the text)', 'wide' => 'Wide (larger landscape photo)']],
                    ['key' => 'frame', 'label' => 'Add a pink frame behind the image', 'type' => 'toggle', 'default' => '0'],
                    ['key' => 'badge', 'label' => 'Badge on the image', 'type' => 'text'],
                    ['key' => 'badge_image', 'label' => 'Handwritten note image (optional, replaces the badge text)', 'type' => 'image'],
                    ['key' => 'badge_style', 'label' => 'Badge style', 'type' => 'select', 'default' => 'pill',
                        'options' => ['pill' => 'Pink label', 'script' => 'Handwritten note']],
                    ['key' => 'caption', 'label' => 'Caption on the image', 'type' => 'text'],
                    ['key' => 'items_style', 'label' => 'List style', 'type' => 'select', 'default' => 'features',
                        'options' => [
                            'features' => 'Icon, title and text',
                            'checklist' => 'Checklist (tick marks)',
                            'iconlist' => 'Icon list in two columns',
                            'pills' => 'Icon pills',
                            'tiles' => 'Centred icon tiles with dividers',
                        ]],
                    ['key' => 'note', 'label' => 'Note below the list', 'type' => 'textarea'],
                    ['key' => 'button', 'label' => 'Button', 'type' => 'button'],
                ], self::background()),
                'items' => self::iconItem('List item', 10),
            ],
            'steps' => [
                'label' => 'Numbered steps',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'intro', 'label' => 'Intro paragraph', 'type' => 'textarea'],
                ], self::background()),
                'items' => self::iconItem('Step', 8),
            ],
            'video' => [
                'label' => 'Video showcase',
                'note' => 'Paste a YouTube or Vimeo link, or a direct link to an MP4 file. The video loads only when a visitor presses play.',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'intro', 'label' => 'Intro paragraph', 'type' => 'textarea'],
                    ['key' => 'video_url', 'label' => 'Video link', 'type' => 'url'],
                    ['key' => 'open_mode', 'label' => 'When a visitor clicks the video', 'type' => 'select', 'default' => 'inline',
                        'options' => ['inline' => 'Play it here on the page', 'newtab' => 'Open the video link in a new tab']],
                    ['key' => 'poster', 'label' => 'Cover image', 'type' => 'image'],
                    ['key' => 'badge_left', 'label' => 'Label at the top left', 'type' => 'text'],
                    ['key' => 'badge_right', 'label' => 'Label at the top right', 'type' => 'text'],
                    ['key' => 'play_label', 'label' => 'Text beside the play button', 'type' => 'text'],
                    ['key' => 'caption', 'label' => 'Caption below the video', 'type' => 'text'],
                ], self::background()),
            ],
            'project_cards' => [
                'label' => 'Project cards',
                'note' => 'The cards come from the Projects module (built next). Projects marked "Show on Home" appear in the featured list.',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'intro', 'label' => 'Intro paragraph', 'type' => 'textarea'],
                    ['key' => 'source', 'label' => 'Which projects to show', 'type' => 'select', 'default' => 'featured',
                        'options' => ['featured' => 'Projects marked "Show on Home"', 'all' => 'All visible projects']],
                    ['key' => 'style', 'label' => 'Card style', 'type' => 'select', 'default' => 'compact',
                        'options' => ['compact' => 'Compact (category, title, role, services)', 'detailed' => 'Detailed (year, location, role, service list)']],
                    ['key' => 'button', 'label' => 'Button', 'type' => 'button'],
                ]),
            ],
            'contact_info' => [
                'label' => 'Contact details with image',
                'note' => 'The email, website, Instagram and phone number come from Settings > Contact and social.',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                ], self::background()),
            ],
            'enquiry_form' => [
                'label' => 'Enquiry form',
                'note' => 'Choose the order, labels and options of the form fields. The fields themselves are fixed. Enquiries are saved in Enquiries.',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'intro', 'label' => 'Intro paragraph', 'type' => 'textarea'],
                    ['key' => 'consent_before', 'label' => 'Consent text before the link', 'type' => 'text'],
                    ['key' => 'privacy_link', 'label' => 'Privacy policy link', 'type' => 'link'],
                    ['key' => 'consent_after', 'label' => 'Consent text after the link', 'type' => 'text'],
                    ['key' => 'submit', 'label' => 'Send button', 'type' => 'action'],
                    ['key' => 'success', 'label' => 'Message after sending', 'type' => 'textarea'],
                ], self::background()),
                'items' => [
                    'label' => 'Form field',
                    'max' => 10,
                    'fields' => [
                        ['key' => 'field', 'label' => 'Field', 'type' => 'select', 'default' => 'name', 'options' => [
                            'name' => 'Name', 'company' => 'Company', 'email' => 'Email', 'phone' => 'Telephone',
                            'enquiry_type' => 'Type of enquiry (choice)', 'timeframe' => 'Event date or timeframe',
                            'location' => 'Event location', 'attendance' => 'Approximate attendance (choice)',
                            'services' => 'Services required (tick boxes)', 'message' => 'Message',
                        ]],
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                        ['key' => 'placeholder', 'label' => 'Placeholder text', 'type' => 'text'],
                        ['key' => 'help', 'label' => 'Small hint next to the label', 'type' => 'text'],
                        ['key' => 'required', 'label' => 'Required', 'type' => 'toggle', 'default' => '0'],
                        ['key' => 'column', 'label' => 'Column on large screens', 'type' => 'select', 'default' => 'left',
                            'options' => ['left' => 'Left', 'right' => 'Right']],
                        ['key' => 'options', 'label' => 'Choices (only used by the choice and tick-box fields)', 'type' => 'choices'],
                    ],
                ],
            ],
            'cta' => [
                'label' => 'Call to action banner',
                'fields' => array_merge(self::eyebrow(), self::heading(), [
                    ['key' => 'text1', 'label' => 'Paragraph 1', 'type' => 'textarea'],
                    ['key' => 'text2', 'label' => 'Paragraph 2', 'type' => 'textarea'],
                    ['key' => 'button1', 'label' => 'Primary button', 'type' => 'button'],
                    ['key' => 'button2', 'label' => 'Secondary button', 'type' => 'button'],
                    ['key' => 'background', 'label' => 'Background image', 'type' => 'image'],
                    ['key' => 'style', 'label' => 'Banner style', 'type' => 'select', 'default' => 'faded',
                        'options' => ['faded' => 'Soft faded image', 'pink' => 'Pink overlay', 'plum' => 'Plum overlay']],
                    ['key' => 'align', 'label' => 'Text alignment', 'type' => 'select', 'default' => 'left',
                        'options' => ['left' => 'Left', 'center' => 'Centred']],
                ]),
            ],
        ];
    }

    public static function type(string $name): ?array
    {
        return self::types()[$name] ?? null;
    }

    /** Index a list of field specs by key. */
    public static function byKey(array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            $out[$field['key']] = $field;
        }
        return $out;
    }
}

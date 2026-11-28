<?php
declare(strict_types=1);

/** Branded HTML for the emails the website sends. Every value is escaped; styles are inline for email programs. */
final class EmailTemplate
{
    private const PRIMARY = '#c2185b';
    private const PLUM = '#37182b';
    private const GREY = '#6b5a64';
    private const LINE = '#f1d6e0';
    private const SERIF = "Georgia,'Times New Roman',serif";

    private static function e(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** The shared frame: a plum header with the name, a white card, and a small footer. */
    public static function layout(string $title, string $preheader, string $content): string
    {
        $font = "font-family:'Segoe UI',Helvetica,Arial,sans-serif";
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . self::e($title) . '</title></head>'
            . '<body style="margin:0;padding:0;background:#fbf1f4;' . $font . ';color:' . self::PLUM . ';">'
            . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#fbf1f4;">' . self::e($preheader) . '</div>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fbf1f4;padding:24px 12px;"><tr><td align="center">'
            . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid ' . self::LINE . ';">'
            . '<tr><td style="background:' . self::PLUM . ';padding:22px 28px;"><span style="font-family:' . self::SERIF . ';font-size:26px;color:#ffffff;letter-spacing:.3px;">E for Events</span>'
            . '<div style="font-size:11px;letter-spacing:2px;color:#f3b3cf;margin-top:4px;">CONCERTS &bull; EVENTS &bull; EXPERIENCES</div></td></tr>'
            . '<tr><td style="padding:28px;">' . $content . '</td></tr>'
            . '<tr><td style="padding:16px 28px;background:#fff7f6;border-top:1px solid ' . self::LINE . ';font-size:12px;color:' . self::GREY . ';">Sent by the E for Events website.</td></tr>'
            . '</table></td></tr></table></body></html>';
    }

    private static function heading(string $text, string $margin = '0 0 12px'): string
    {
        return '<h1 style="font-family:' . self::SERIF . ';font-weight:500;font-size:26px;line-height:1.25;margin:' . $margin . ';color:' . self::PLUM . ';">' . self::e($text) . '</h1>';
    }

    private static function button(string $href, string $label): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0;"><tr><td style="background:' . self::PRIMARY . ';border-radius:999px;">'
            . '<a href="' . self::e($href) . '" style="display:inline-block;padding:13px 26px;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;">' . self::e($label) . '</a></td></tr></table>';
    }

    private static function row(string $label, string $valueHtml): string
    {
        $cell = 'padding:10px 0;border-bottom:1px solid ' . self::LINE . ';vertical-align:top;';
        return '<tr><td style="' . $cell . 'width:170px;font-size:12px;letter-spacing:.8px;text-transform:uppercase;color:' . self::GREY . ';">' . self::e($label) . '</td>'
            . '<td style="' . $cell . 'font-size:15px;color:' . self::PLUM . ';">' . $valueHtml . '</td></tr>';
    }

    /**
     * @param array<string,string> $v
     * @param string[] $services
     * @return array{html:string,text:string}
     */
    public static function enquiry(array $v, array $services): array
    {
        $none = '<span style="color:#a89aa2;">Not given</span>';
        $plain = fn (string $k): string => trim((string) ($v[$k] ?? '')) !== '' ? self::e($v[$k]) : $none;
        $link = fn (string $href, string $text): string => '<a href="' . self::e($href) . '" style="color:' . self::PRIMARY . ';">' . self::e($text) . '</a>';
        $name = (string) ($v['name'] ?? '');
        $email = trim((string) ($v['email'] ?? ''));
        $phone = trim((string) ($v['phone'] ?? ''));

        $rows = self::row('Name', '<b>' . self::e($name) . '</b>')
            . self::row('Company', $plain('company'))
            . self::row('Email', $email !== '' ? $link('mailto:' . $email, $email) : $none)
            . self::row('Phone', $phone !== '' ? $link('tel:' . preg_replace('/\s+/', '', $phone), $phone) : $none)
            . self::row('Type of enquiry', $plain('enquiry_type'))
            . self::row('Event date or timeframe', $plain('timeframe'))
            . self::row('Event location', $plain('location'))
            . self::row('Approximate attendance', $plain('attendance'))
            . self::row('Services required', $services ? self::e(implode(', ', $services)) : $none);
        $message = trim((string) ($v['message'] ?? ''));

        $content = self::heading('New enquiry', '0 0 6px')
            . '<p style="margin:0 0 18px;font-size:15px;color:' . self::GREY . ';">Someone sent an enquiry through the website contact form.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid ' . self::LINE . ';">' . $rows . '</table>'
            . '<div style="margin-top:20px;font-size:12px;letter-spacing:.8px;text-transform:uppercase;color:' . self::GREY . ';">Message</div>'
            . '<div style="margin-top:8px;padding:16px;background:#fff7f6;border-left:3px solid ' . self::PRIMARY . ';border-radius:8px;font-size:15px;line-height:1.6;white-space:pre-wrap;">'
            . ($message !== '' ? self::e($message) : '<span style="color:#a89aa2;">No message</span>') . '</div>'
            . ($email !== '' ? self::button('mailto:' . $email . '?subject=' . rawurlencode('Re: your enquiry to E for Events'), 'Reply to ' . ($name !== '' ? $name : 'the sender')) : '')
            . '<p style="margin:0;font-size:12px;color:' . self::GREY . ';">You can also read this enquiry in the admin panel under Enquiries.</p>';
        $html = self::layout('New enquiry from ' . $name, 'New enquiry from ' . $name . ': ' . ($v['enquiry_type'] ?? ''), $content);

        $text = implode("\r\n", [
            'A new enquiry was sent from the website.', '',
            'Name: ' . $name, 'Company: ' . ($v['company'] ?? ''), 'Email: ' . $email, 'Phone: ' . $phone,
            'Type of enquiry: ' . ($v['enquiry_type'] ?? ''), 'Event date or timeframe: ' . ($v['timeframe'] ?? ''),
            'Event location: ' . ($v['location'] ?? ''), 'Approximate attendance: ' . ($v['attendance'] ?? ''),
            'Services required: ' . implode(', ', $services), '', 'Message:', $message,
        ]);
        return ['html' => $html, 'text' => $text];
    }

    /** @return array{html:string,text:string} */
    public static function passwordReset(string $name, string $link, int $minutes): array
    {
        $content = self::heading('Reset your password')
            . '<p style="margin:0 0 10px;font-size:15px;line-height:1.6;">Hello ' . self::e($name) . ',</p>'
            . '<p style="margin:0;font-size:15px;line-height:1.6;">Someone asked to reset the password for your E for Events admin account. Choose a new password with the button below. The link works for ' . $minutes . ' minutes.</p>'
            . self::button($link, 'Choose a new password')
            . '<p style="margin:0 0 6px;font-size:13px;color:' . self::GREY . ';">If the button does not work, copy this link into your browser:</p>'
            . '<p style="margin:0 0 18px;font-size:13px;word-break:break-all;"><a href="' . self::e($link) . '" style="color:' . self::PRIMARY . ';">' . self::e($link) . '</a></p>'
            . '<p style="margin:0;font-size:13px;color:' . self::GREY . ';">If you did not ask for this, you can ignore this email. Your password stays the same.</p>';
        $html = self::layout('Reset your password', 'Choose a new password for your E for Events admin account', $content);
        $text = implode("\r\n", [
            'Hello ' . $name . ',', '',
            'Someone asked to reset the password for your E for Events admin account.',
            'To choose a new password, open this link within ' . $minutes . ' minutes:', '', $link, '',
            'If you did not ask for this, you can ignore this email. Your password stays the same.',
        ]);
        return ['html' => $html, 'text' => $text];
    }

    /** @return array{html:string,text:string} */
    public static function test(): array
    {
        $content = self::heading('Your email settings work')
            . '<p style="margin:0;font-size:15px;line-height:1.6;">This is a test email from the E for Events admin panel. If you can read this, new enquiries and password reset emails will reach you.</p>';
        return [
            'html' => self::layout('Test email', 'Your email settings work', $content),
            'text' => "This is a test email from the E for Events admin panel.\r\n\r\nIf you can read this, your email settings work.",
        ];
    }
}

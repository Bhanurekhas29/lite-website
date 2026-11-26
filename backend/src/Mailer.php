<?php
declare(strict_types=1);

/** A small SMTP client: plain, SSL or STARTTLS, with optional AUTH LOGIN. Sends plain-text email. */
final class Mailer
{
    /** @param array{host:string,port:int,encryption:string,username:string,password:string,from_email:string,from_name:string} $c */
    public static function send(array $c, string $to, string $subject, string $body): void
    {
        foreach ([$to, $c['from_email']] as $address) {
            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Enter a valid email address');
            }
        }
        $clean = fn (string $s): string => trim(preg_replace('/[\r\n]+/', ' ', $s));

        $scheme = $c['encryption'] === 'ssl' ? 'ssl' : 'tcp';
        $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $fp = @stream_socket_client("$scheme://{$c['host']}:{$c['port']}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            throw new RuntimeException("Could not connect to {$c['host']} on port {$c['port']}. Check the host, port and encryption.");
        }
        stream_set_timeout($fp, 15);

        try {
            self::expect($fp, [220]);
            self::command($fp, 'EHLO localhost', [250]);

            if ($c['encryption'] === 'tls') {
                self::command($fp, 'STARTTLS', [220]);
                if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('The secure (TLS) connection could not be started.');
                }
                self::command($fp, 'EHLO localhost', [250]);
            }

            if ($c['username'] !== '') {
                self::command($fp, 'AUTH LOGIN', [334]);
                self::command($fp, base64_encode($c['username']), [334]);
                self::command($fp, base64_encode($c['password']), [235], 'The mail server rejected the username or password.');
            }

            self::command($fp, "MAIL FROM:<{$c['from_email']}>", [250]);
            self::command($fp, "RCPT TO:<$to>", [250, 251]);
            self::command($fp, 'DATA', [354]);

            $fromName = $clean($c['from_name']);
            $headers = [
                'Date: ' . gmdate('r'),
                'From: ' . ($fromName !== '' ? '=?UTF-8?B?' . base64_encode($fromName) . "?= <{$c['from_email']}>" : $c['from_email']),
                "To: <$to>",
                'Subject: =?UTF-8?B?' . base64_encode($clean($subject)) . '?=',
                'Message-ID: <' . bin2hex(random_bytes(8)) . '@' . preg_replace('/^.*@/', '', $c['from_email']) . '>',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];
            $message = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body), 76, "\r\n");
            fwrite($fp, $message . "\r\n.\r\n");
            self::expect($fp, [250]);
            @fwrite($fp, "QUIT\r\n");
        } finally {
            fclose($fp);
        }
    }

    /** Read one reply (possibly several lines). Returns [code, text]. */
    private static function reply($fp): array
    {
        $text = '';
        while (($line = fgets($fp, 1024)) !== false) {
            $text .= trim($line) . ' ';
            if (strlen($line) < 4 || $line[3] !== '-') {
                return [(int) substr($line, 0, 3), trim($text)];
            }
        }
        throw new RuntimeException('The mail server stopped responding.');
    }

    private static function expect($fp, array $codes, ?string $message = null): void
    {
        [$code, $text] = self::reply($fp);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException($message ?? "The mail server replied: $code " . mb_substr($text, 0, 200));
        }
    }

    private static function command($fp, string $line, array $codes, ?string $message = null): void
    {
        fwrite($fp, $line . "\r\n");
        self::expect($fp, $codes, $message);
    }
}

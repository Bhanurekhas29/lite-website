<?php
declare(strict_types=1);

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /** JSON request body as an array (empty array if missing or invalid). */
    public static function json(): array
    {
        $data = json_decode(file_get_contents('php://input') ?: '', true);
        return is_array($data) ? $data : [];
    }

    /**
     * Writes must be sent as JSON (or, for file uploads, with a custom header).
     * Browsers cannot send either cross-site without a CORS preflight, which
     * together with SameSite cookies blocks cross-site request forgery.
     */
    public static function requireSafeWrite(bool $isUpload): void
    {
        if (self::method() === 'GET') {
            return;
        }
        if ($isUpload) {
            if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'fetch') {
                Response::json(['error' => 'Unsupported request'], 415);
                exit;
            }
            return;
        }
        $type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!str_contains(strtolower($type), 'application/json')) {
            Response::json(['error' => 'Unsupported request'], 415);
            exit;
        }
    }
}

<?php
declare(strict_types=1);

/** An error with a message that is safe to show to the admin, and an HTTP status. */
final class HttpException extends RuntimeException
{
    public function __construct(string $message, private int $status = 422)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }
}

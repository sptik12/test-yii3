<?php

namespace App\Backend\Exception\Http;

final class ForbiddenException extends \RuntimeException
{
    public function __construct(string $message = "Forbidden", ?\Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}

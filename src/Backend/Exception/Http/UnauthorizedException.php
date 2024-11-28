<?php

namespace App\Backend\Exception\Http;

final class UnauthorizedException extends \RuntimeException
{
    public function __construct(string $message = "Unauthorized", ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}

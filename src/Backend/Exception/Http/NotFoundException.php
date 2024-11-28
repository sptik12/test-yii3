<?php

namespace App\Backend\Exception\Http;

final class NotFoundException extends \RuntimeException
{
    public function __construct(string $message = "Not found", ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}

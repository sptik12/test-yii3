<?php

namespace App\Backend\Component;

use Yiisoft\Router\CurrentRoute;

final class CurrentPanel
{
    protected ?string $id;

    public function __construct(CurrentRoute $currentRoute)
    {
        $this->id = match ($currentRoute->getHost()) {
            $_ENV['ADMIN_HOST'] => "admin",
            $_ENV['CLIENT_HOST'] => "client",
            $_ENV['DEALER_HOST'] => "dealer",
            default => null,
        };
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}

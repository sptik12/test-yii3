<?php

declare(strict_types=1);

namespace App\Frontend\EventHandler;

use Psr\Log\LoggerInterface;
use Yiisoft\User\Event\AfterLogin;

final class AfterLoginEventHandler
{
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    public function handle(AfterLogin $event): void
    {
        $identity = $event->getIdentity();
        $this->logger->info("AfterLogin");
        $this->logger->info(print_r($identity, true));
    }
}

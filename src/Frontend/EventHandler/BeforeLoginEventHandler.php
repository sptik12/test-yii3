<?php

declare(strict_types=1);

namespace App\Frontend\EventHandler;

use Yiisoft\User\Event\BeforeLogin;
use Psr\Log\LoggerInterface;

final class BeforeLoginEventHandler
{
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }


    public function handle(BeforeLogin $event): void
    {
        $identity = $event->getIdentity();
        $this->logger->info("BeforeLogin");
        $this->logger->info(print_r($identity, true));
    }
}

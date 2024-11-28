<?php

namespace App\Backend\Command\Notificator;

use App\Backend\Command\AbstractCommand;
use App\Backend\Component\Notificator\Notificator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Aliases\Aliases;

class DispatchCommand extends AbstractCommand
{
    protected $lockFile = "@runtime/notificator.lock";
    private $frequencyInSec = 2;
    private $maxExecutionTimeInSec = 300; // 5 minutes

    public function __construct(
        protected Notificator $notificator,
        protected Aliases $aliases
    ) {
        parent::__construct($aliases);

        /*
            If process was not finished correctly (server was restarted, CTRL+C, kill, etc), lock file will be in place but the process will not exists.
            In this case, lock file must be removed
        */
        $this->unlockIfProcessIsNotRunning();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Notificator')
            ->setHelp('This is notificator');
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->isLocked()) {
            return Command::SUCCESS;
        }

        $this->lock();
        $expired = strtotime("+ {$this->maxExecutionTimeInSec} seconds");

        while (time() < $expired) {
            $this->notificator->dispatch();
            sleep($this->frequencyInSec);
        }

        $this->unlock();

        return Command::SUCCESS;
    }
}

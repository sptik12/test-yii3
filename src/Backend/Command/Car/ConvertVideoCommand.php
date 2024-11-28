<?php

namespace App\Backend\Command\Car;

use App\Backend\Command\AbstractCommand;
use App\Backend\Service\CarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;

class ConvertVideoCommand extends AbstractCommand
{
    protected $lockFile = "@runtime/convert-video.lock";

    public function __construct(
        protected CarService $carService,
        protected Aliases $aliases
    ) {
        parent::__construct($aliases);
        $this->unlockIfProcessIsNotRunning();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Convert cars videos')
            ->setHelp("Convert cars videos");
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->isLocked()) {
            return Command::SUCCESS;
        }

        $this->lock();
        $mediasCount = $this->carService->convertCarsVideos();
        $this->unlock();

        return Command::SUCCESS;
    }
}

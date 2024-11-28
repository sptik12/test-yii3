<?php

namespace App\Backend\Command\Car;

use App\Backend\Command\AbstractCommand;
use App\Backend\Model\Car\CarModel;
use App\Backend\Service\CarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;

class SetMainMediaCommand extends AbstractCommand
{
    public function __construct(
        protected CarService $carService,
        protected Aliases $aliases
    ) {
        parent::__construct($aliases);
    }

    public function configure(): void
    {
        $this
            ->setDescription('Set main media for cars')
            ->setHelp("Set main media for cars");
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->carService->setCarMainMedias();

        return Command::SUCCESS;
    }
}

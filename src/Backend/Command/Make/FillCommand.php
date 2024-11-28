<?php

namespace App\Backend\Command\Make;

use App\Backend\Service\CarMakeService;
use App\Backend\Service\CarModelService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FillCommand extends Command
{
    public function __construct(
        protected CarMakeService $carMakeService,
        protected CarModelService $carModelService,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Fill Makes list in database')
            ->setHelp("Fill 'make' table");
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $makesCount = $this->carMakeService->fillMakesTable();
        echo "{$makesCount} new makes were stored in db" . PHP_EOL;

        $modelsCount = $this->carModelService->fillModelsTable();
        echo "{$modelsCount} new models were stored in db" . PHP_EOL;

        return Command::SUCCESS;
    }
}

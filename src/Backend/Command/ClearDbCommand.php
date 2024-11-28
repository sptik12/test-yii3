<?php

namespace App\Backend\Command;

use App\Backend\Service\CarSearchUrlService;
use App\Backend\Service\CarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Aliases\Aliases;

class ClearDbCommand extends Command
{
    public function __construct(
        protected CarSearchUrlService $carSearchUrlService
    ) {
        parent::__construct();
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->carSearchUrlService->clearDeletedCarSearchUrls();

        return Command::SUCCESS;
    }
}

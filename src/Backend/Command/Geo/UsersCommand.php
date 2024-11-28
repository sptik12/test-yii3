<?php

namespace App\Backend\Command\Geo;

use App\Backend\Service\GeoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersCommand extends Command
{
    public function __construct(
        protected GeoService $geoService
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Fill Geo data in database')
            ->setHelp("Fill 'user' table");
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->geoService->fillUsersTableByGeoData();

        return Command::SUCCESS;
    }
}

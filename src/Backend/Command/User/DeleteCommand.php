<?php

namespace App\Backend\Command\User;

use App\Backend\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{
    public function __construct(
        protected UserService $userService
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Delete users from database')
            ->setHelp("Delete users from database");
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userService->removeUsersByDeletedDate();

        return Command::SUCCESS;
    }
}

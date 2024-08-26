<?php

namespace App\Core\Command;

use App\Core\Handler\Installer\DatabaseConfiguratorHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:configure-database',
    description: 'Configure database',
)]
class ConfigureDatabaseCommand extends Command
{
    public function __construct(
        private readonly DatabaseConfiguratorHandler $databaseConfiguratorHandler,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->databaseConfiguratorHandler->configureDatabase($io);

        $io->success('Database configured!');
        return Command::SUCCESS;
    }
}

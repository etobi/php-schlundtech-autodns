<?php
namespace Etobi\Autodns\Command\Zone;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ListCommand extends Command {

    /**
     * Configuration
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName("zone:list")
            ->setDescription("This command prints 'Hello World!'")
            ->setDefinition(array(
                new InputOption('flag', 'f', InputOption::VALUE_NONE, 'Raise a flag'),
                new InputArgument('activities', InputArgument::IS_ARRAY, 'Space-separated activities to perform', null),
            ))
            ->setHelp("The <info>hello</info> command just prints 'Hello World!'");
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln("Hello World!");
        return self::SUCCESS;
    }
}
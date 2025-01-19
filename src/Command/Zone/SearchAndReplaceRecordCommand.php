<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SearchAndReplaceRecordCommand extends AbstractCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName("zone:record:searchandreplace")
            ->setDescription('Replace a value in *all* resource records of given type')
            ->addUsage('example.com TXT searchvalue replacevalue')
            ->addUsage('example.com A 1.2.3.4 4.3.2.1');
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
                new InputArgument('type', InputArgument::REQUIRED, 'Record type (e.g. TXT, A, AAAA)'),
                new InputArgument('search', InputArgument::REQUIRED,'Search value to be replaced'),
                new InputArgument('replace', InputArgument::REQUIRED, 'New value'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->getConfig($input);
        $autoDns = $this->getAutoDns($config);

        $zone = $input->getArgument('zone');
        $type = $input->getArgument('type');
        $search = $input->getArgument('search');
        $replace = $input->getArgument('replace');

        $response = $autoDns->searchAndReplace(
            $zone,
            $type,
            $search,
            $replace
        );

        $this->printMessages($io, $response->getMessages());
        return $response->isStatusTypeSuccess() ? self::SUCCESS : self::FAILURE;
    }
}
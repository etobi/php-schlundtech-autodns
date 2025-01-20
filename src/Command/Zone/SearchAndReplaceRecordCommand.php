<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractAutodnsCommand;
use Etobi\Autodns\Service\AutoDnsXmlResponse;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SearchAndReplaceRecordCommand extends AbstractAutodnsCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('zone:record:searchandreplace')
            ->setDescription('Replace a value in *all* resource records of given type')
            ->addUsage('example.com TXT searchvalue replacevalue')
            ->addUsage('example.com A 1.2.3.4 4.3.2.1');
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
                new InputArgument('type', InputArgument::REQUIRED, 'Record type (e.g. TXT, A, AAAA)'),
                new InputArgument('search', InputArgument::REQUIRED, 'Search value to be replaced'),
                new InputArgument('replace', InputArgument::REQUIRED, 'New value'),
            ]);
    }

    protected function perform(InputInterface $input, SymfonyStyle $io, AutoDnsXmlService $autoDns): AutoDnsXmlResponse
    {
        $zone = $input->getArgument('zone');
        $type = $input->getArgument('type');
        $search = $input->getArgument('search');
        $replace = $input->getArgument('replace');

        return $autoDns->searchAndReplace(
            $zone,
            $type,
            $search,
            $replace
        );
    }
}

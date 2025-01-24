<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractAutodnsCommand;
use Etobi\Autodns\Service\AutoDnsXmlResponse;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateSoaCommand extends AbstractAutodnsCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('zone:updatesoa')
            ->setDescription('Update SOA settings of the zone')
            ->addUsage('example.com --ttl 600');
        $this->getDefinition()
            ->addOptions([
                new InputOption('ttl', null, InputOption::VALUE_REQUIRED),
            ]);
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
            ]);
    }

    protected function perform(InputInterface $input, SymfonyStyle $io, AutoDnsXmlService $autoDns): AutoDnsXmlResponse
    {
        return $autoDns->updateSoa(
            $input->getArgument('zone'),
            $input->getOption('ttl')
        );
    }
}

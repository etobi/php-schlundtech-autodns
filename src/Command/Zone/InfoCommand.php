<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractAutodnsCommand;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InfoCommand extends AbstractAutodnsCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('zone:info')
            ->setDescription('Show detailed information about the zone')
            ->addUsage('example.com');
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
            ]);
    }

    protected function perform(InputInterface $input, SymfonyStyle $io, AutoDnsXmlService $autoDns): int
    {
        $zoneName = $input->getArgument('zone');

        $zone = $autoDns->getZones($zoneName)[0];
        $zoneInfo = $autoDns->getZoneInfo($zoneName);

        $this->printZone($io, $zone, $zoneInfo);

        return self::SUCCESS;
    }
}

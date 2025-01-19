<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InfoCommand extends AbstractCommand
{

    protected function configure(): void
    {
        parent::configure();
        $this->setName("zone:info")
            ->setDescription("Show detailed information about the zone")
            ->addUsage('example.com');
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->getConfig($input);
        $autoDns = $this->getAutoDns($config);

        $zoneName = $input->getArgument('zone');

        $zone = $autoDns->getZones($zoneName)[0];
        // TODO error handling, zone not found
        $zoneInfo = $autoDns->getZoneInfo($zoneName);

        $this->printZone($io, $zone, $zoneInfo);

        return self::SUCCESS;
    }
}
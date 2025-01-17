<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends AbstractCommand
{

    protected function configure(): void
    {
        parent::configure();
        $this->setName("zone:list")
            ->getDefinition()
            ->addOptions([
                new InputOption('list', 'l', InputOption::VALUE_NONE, 'Show zones as list'),
                new InputOption('resourcerecords', 'r', InputOption::VALUE_NONE, 'Show all resource records'),
                new InputOption('full-values', 'f', InputOption::VALUE_NONE, 'Show full value resource records'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->getConfig($input);
        $autoDns = $this->getAutoDns($config);

        $zones = $autoDns->getZones();
        $zoneRows = [];

        foreach ($zones as $zone) {
            $zoneInfo = null;
            if ($input->getOption('resourcerecords')) {
                $zoneInfo = $autoDns->getZoneInfo($zone['name']);
            }

            if ($input->getOption('list')) {
                $zoneRows[] = [
                    $zone['name'],
                    $zone['mainip'],
                ];
            } else {
                $this->printZone(
                    $io,
                    $zone,
                    $zoneInfo,
                    shortenValues: !(
                    (bool)$input->getOption('full-values')
                    )
                );
                $io->newLine();
            }
        }
        if ($input->getOption('list')) {
            $io->table(
                [
                    'Name',
                    'Mainip',
                ],
                $zoneRows
            );
        }
        return self::SUCCESS;
    }
}
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
            ->setDescription("List all zones")
            ->addUsage('-l')
            ->addUsage('-rf');
        $this->getDefinition()
            ->addOptions([
                new InputOption('list', 'l', InputOption::VALUE_NONE, 'Show zones as simple list'),
                new InputOption('resourcerecords', 'r', InputOption::VALUE_NONE, 'Show all resource records for each zone'),
                new InputOption('full-values', 'f', InputOption::VALUE_NONE, 'Show full value resource records'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (
            $input->getOption('list')
            && $input->getOption('resourcerecords')
        ) {
            throw new \RuntimeException('You cannot combine the options --list and --resourcerecords');
        }
        if (
            $input->getOption('full-values')
            && !$input->getOption('resourcerecords')
        ) {
            throw new \RuntimeException('You cannot have --full-values without --resourcerecords');
        }

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
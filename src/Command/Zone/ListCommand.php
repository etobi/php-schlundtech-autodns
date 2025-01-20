<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractAutodnsCommand;
use Etobi\Autodns\Service\AutoDnsXmlResponse;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends AbstractAutodnsCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('zone:list')
            ->setDescription('List all zones')
            ->addUsage('-l')
            ->addUsage('-rf');
        $this->getDefinition()
            ->addOptions([
                new InputOption('list', 'l', InputOption::VALUE_NONE, 'Show zones as simple list'),
                new InputOption(
                    'resourcerecords',
                    'r',
                    InputOption::VALUE_NONE,
                    'Show all resource records for each zone'
                ),
                new InputOption('full-values', 'f', InputOption::VALUE_NONE, 'Show full value resource records'),
            ]);
    }

    protected function perform(InputInterface $input, SymfonyStyle $io, AutoDnsXmlService $autoDns): AutoDnsXmlResponse
    {
        if (
            (bool)$input->getOption('list')
            && (bool)$input->getOption('resourcerecords')
        ) {
            throw new \RuntimeException('You cannot combine the options --list and --resourcerecords');
        }
        if (
            (bool)$input->getOption('full-values')
            && !(bool)$input->getOption('resourcerecords')
        ) {
            throw new \RuntimeException('You cannot have --full-values without --resourcerecords');
        }

        $result = $autoDns->getZones();
        $zoneRows = [];

        foreach ($result['zones'] as $zone) {
            $zoneInfo = null;
            if ((bool)$input->getOption('resourcerecords')) {
                $zoneInfo = $autoDns->getZoneInfo($zone['name']);
            }

            if ((bool)$input->getOption('list')) {
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
        if ((bool)$input->getOption('list')) {
            $io->table(
                [
                    'Name',
                    'Mainip',
                ],
                $zoneRows
            );
        }
        return $result['response'];
    }
}

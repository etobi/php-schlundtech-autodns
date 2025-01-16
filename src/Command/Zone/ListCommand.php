<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\ConfigLoader;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends Command
{

    public function __construct(
        private readonly ConfigLoader $configLoader,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        // TODO option single domain
        // TODO option show records

        $this->setName("zone:list")
            ->setDescription("This command prints 'Hello World!'")
            ->setDefinition(array(
                new InputOption('flag', 'f', InputOption::VALUE_NONE, 'Raise a flag'),
                new InputArgument('activities', InputArgument::IS_ARRAY, 'Space-separated activities to perform', null),
            ))
            ->setHelp("The <info>hello</info> command just prints 'Hello World!'");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $autoDnsConfig = $this->configLoader->get('autodns');
        $autoDns = new AutoDnsXmlService(
            $autoDnsConfig['gateway'],
            $autoDnsConfig['username'],
            $autoDnsConfig['password'],
            (int)$autoDnsConfig['context'],
        );

        $zones = $autoDns->getZones();
        foreach ($zones as $zone) {
            $zoneInfo = $autoDns->getZoneInfo($zone['name'], $zone['system_ns']);

            $io->section(
                $zone['name']
            );
            $io->horizontalTable(
                ['Name', 'IDN', 'Mainip', 'SystemNs', 'Primary', 'SOA TTL'],
                [
                    [
                        $zone['name'],
                        $zone['idn'] ?: '',
                        $zone['mainip'],
                        $zone['system_ns'],
                        $zone['primary'],
                        //$zone['secondary1'],
                        //$zone['created'],
                        //$zone['changed'],
                        (string)$zone['soa']['ttl'],
                    ]
                ]
            );

            $recordsTableRows = [];
            $recordsTableRows[] = [
                $zone['name'] . '. (mainip)',
                'A',
                '',
                $zoneInfo['main'],
                (string)$zone['soa']['ttl'],
            ];
            if ($zoneInfo['www_include']) {
                $recordsTableRows[] = [
                    'www.' . $zone['name'] . '. (www_include)',
                    'A',
                    '',
                    $zoneInfo['main'],
                    (string)$zone['soa']['ttl'],
                ];
            }
            foreach ($zoneInfo['rr'] as $rr) {
                $value = $rr['value'];
                if (strlen($value) > 30) {
                    $value = substr($value, 0, 30)
                        . ' <...> '
                        . substr($value, -15);
                }
                $recordsTableRows[] = [
                    ($rr['name'] ? $rr['name'] . '.' : '') . $zone['name'] . '.',
                    trim($rr['type']),
                    $rr['pref'],
                    $value,
                    $rr['ttl'],
                ];
            }

            $io->table(
                [
                    'Name',
                    'Type',
                    'Pref',
                    'Value',
                    'TTL',
                ],
                $recordsTableRows
            );

            $io->newLine();
        }
        return self::SUCCESS;
    }
}
<?php

namespace Etobi\Autodns\Command;

use Etobi\Autodns\ConfigLoader;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{

    public function __construct(
        private readonly ConfigLoader $configLoader,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $autoDns = $this->getAutoDns();

        return self::SUCCESS;
    }

    protected function getAutoDns(): AutoDnsXmlService
    {
        $autoDnsConfig = $this->configLoader->get('autodns');
        return new AutoDnsXmlService(
            $autoDnsConfig['gateway'],
            $autoDnsConfig['username'],
            $autoDnsConfig['password'],
            (int)$autoDnsConfig['context'],
        );
    }

    protected function printMessages(SymfonyStyle $io, array $messages): void
    {
        foreach ($messages as $message) {
            switch ($message['type']) {
                case 'notice':
                    if ($io->isVeryVerbose()) {
                        $io->writeln(
                            '['
                            . $message['type']
                            . '] '
                            . $message['text']
                            . ' ('
                            . $message['code']
                            . ')'
                        );
                    }
                    break;
                case 'success':
                    $io->success(
                        $message['text'] . ' ('
                        . $message['code']
                        . ')'
                    );
                    break;
                case 'error':
                    $io->error(
                        $message['text'] . ' ('
                        . $message['code']
                        . ')'
                    );
                    break;
                default:
                    $io->info(
                        '['
                        . $message['type']
                        . '] '
                        . $message['text']
                        . ' ('
                        . $message['code']
                        . ')'
                    );
            }
        }
    }

    protected function printZone(
        SymfonyStyle $io,
        array $zone,
        ?array $zoneInfo = null,
        bool $shortenValues = false
    ): void {
        $io->section(
            $zone['name']
        );

        $io->horizontalTable(
            [
                'Name',
                'IDN',
                'Mainip',
                'SystemNs',
                'Primary',
                'Secondary1',
                'Secondary2',
                'Created',
                'Changed',
                'SOA refresh',
                'SOA retry',
                'SOA expire',
                'SOA ttl',
                'SOA email',
                'SOA default',
            ],
            [
                [
                    $zone['name'],
                    $zone['idn'] ?: '',
                    $zone['mainip'],
                    $zone['system_ns'],
                    $zone['primary'],
                    $zone['secondary1'],
                    $zone['secondary2'],
                    $zone['created'],
                    $zone['changed'],
                    (string)$zone['soa']['refresh'],
                    (string)$zone['soa']['retry'],
                    (string)$zone['soa']['expire'],
                    (string)$zone['soa']['ttl'],
                    (string)$zone['soa']['email'],
                    (string)$zone['soa']['default'],
                ]
            ]
        );

        if ($zoneInfo) {
            $recordsTableRows = [];

            foreach ($zoneInfo['rr'] as $rr) {
                $value = $rr['value'];
                if (
                    $shortenValues
                    && strlen($value) > 30
                ) {
                    $value = substr($value, 0, 30)
                        . ' <...> '
                        . substr($value, -15);
                }
                $special =
                    ($rr['main'] ?? false ? ' (main)' : '')
                    . ($rr['www_include'] ?? false ? ' (www_include)' : '');
                $recordsTableRows[] = [
                    ($rr['name'] ? $rr['name'] . '.' : '') . $zone['name'] . '.',
                    trim($rr['type']) . $special,
                    $rr['pref'],
                    $rr['ttl'],
                    $value,
                ];
            }

            $io->table(
                [
                    'Name',
                    'Type',
                    'Pref',
                    'TTL',
                    'Value',
                ],
                $recordsTableRows
            );
        }
    }
}
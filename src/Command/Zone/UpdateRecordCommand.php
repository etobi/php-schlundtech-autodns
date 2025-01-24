<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractAutodnsCommand;
use Etobi\Autodns\Service\AutoDnsXmlResponse;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateRecordCommand extends AbstractAutodnsCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('zone:record:update')
            ->setDescription(
                'Updates a resource record in the zone (like a combined zone:record:remove and zone:record:add)'
            )
            ->addUsage('example.com TXT oldvalue newvalue')
            ->addUsage('example.com A 1.2.3.4 4.3.2.1 --name subdomain -ttl 300');
        $this->getDefinition()
            ->addOptions([
                new InputOption('name', null, InputOption::VALUE_REQUIRED),
                new InputOption('ttl', null, InputOption::VALUE_REQUIRED),
                new InputOption('pref', null, InputOption::VALUE_REQUIRED),
            ]);
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
                new InputArgument('type', InputArgument::REQUIRED, 'Record type (e.g. TXT, A, AAAA)'),
                new InputArgument(
                    'oldvalue',
                    InputArgument::REQUIRED,
                    'Old value to identify the record to be updated'
                ),
                new InputArgument('newvalue', InputArgument::REQUIRED, 'New value to be set'),
            ]);
    }

    protected function perform(InputInterface $input, SymfonyStyle $io, AutoDnsXmlService $autoDns): AutoDnsXmlResponse
    {
        $zone = $input->getArgument('zone');
        $type = $input->getArgument('type');
        $oldvalue = $input->getArgument('oldvalue');
        $newvalue = $input->getArgument('newvalue');
        $name = $input->getOption('name');
        $ttl = $input->getOption('ttl');
        $pref = $input->getOption('pref');
        return $autoDns->updateRecord(
            $zone,
            $type,
            $oldvalue,
            $newvalue,
            $name,
            $ttl,
            $pref
        );
    }
}

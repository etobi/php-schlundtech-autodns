<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractAutodnsCommand;
use Etobi\Autodns\Service\AutoDnsXmlResponse;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddRecordCommand extends AbstractAutodnsCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('zone:record:add')
            ->setDescription('Adds a resource record to the zone')
            ->addUsage('example.com TXT something')
            ->addUsage('example.com A 1.2.3.4 --name \'*\'')
            ->addUsage('example.com A 1.2.3.4 --name subdomain -ttl 300')
            ->addUsage('example.com AAAA 2001:0000:1111:2222:3333:4444:5555:6666 --name subdomain');

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
                new InputArgument('value', InputArgument::REQUIRED, 'Record value'),
            ]);
    }

    protected function perform(InputInterface $input, SymfonyStyle $io, AutoDnsXmlService $autoDns): AutoDnsXmlResponse
    {
        $zone = $input->getArgument('zone');
        $type = $input->getArgument('type');
        $value = $input->getArgument('value');
        $name = $input->getOption('name');
        $ttl = $input->getOption('ttl');
        $pref = $input->getOption('pref');

        return $autoDns->addRecord(
            $zone,
            $type,
            $value,
            $name,
            $ttl,
            $pref
        );
    }
}

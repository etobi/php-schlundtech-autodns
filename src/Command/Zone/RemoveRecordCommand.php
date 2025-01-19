<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveRecordCommand extends AbstractCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName("zone:record:remove")
            ->setDescription('Removes a resource record from the zone')
            ->addUsage('example.com TXT something')
            ->addUsage('example.com A 1.2.3.4 --name \'*\'')
            ->addUsage('example.com A 1.2.3.4 --name subdomain -ttl 300')
            ->addUsage('example.com AAAA 2001:0000:1111:2222:3333:4444:5555:6666 --name subdomain');
        $this->getDefinition()
            ->addOptions([
                new InputOption('name', null, InputOption::VALUE_REQUIRED),
                new InputOption('ttl', null, InputOption::VALUE_REQUIRED, default: 600),
                new InputOption('pref', null, InputOption::VALUE_REQUIRED),
            ]);
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
                new InputArgument('type', InputArgument::REQUIRED, 'Record type (e.g. TXT, A, AAAA)'),
                new InputArgument('value', InputArgument::REQUIRED, 'Record value'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->getConfig($input);
        $autoDns = $this->getAutoDns($config);

        $zone = $input->getArgument('zone');
        $type = $input->getArgument('type');
        $value = $input->getArgument('value');
        $name = $input->getOption('name');
        $ttl = $input->getOption('ttl');
        $pref = $input->getOption('pref');

        $response = $autoDns->removeRecord(
            $zone,
            $type,
            $value,
            $name,
            $ttl,
            $pref
        );

        $this->printMessages($io, $response->getMessages());
        return $response->isStatusTypeSuccess() ? self::SUCCESS : self::FAILURE;
    }
}
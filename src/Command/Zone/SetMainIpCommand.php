<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetMainIpCommand extends AbstractCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName("zone:setmainip")
            ->setDescription('Set main IP address of the zone')
            ->addUsage('example.com 1.2.3.4')
            ->addUsage('example.com 1.2.3.4 600');
        $this->getDefinition()
            ->addOptions([
                new InputOption('ttl', null, InputOption::VALUE_REQUIRED, default: 600),
            ]);
        $this->getDefinition()
            ->addArguments([
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
                new InputArgument('ip', InputArgument::REQUIRED, 'The IP address'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->getConfig($input);
        $autoDns = $this->getAutoDns($config);

        $response = $autoDns->setMainip(
            $input->getArgument('zone'),
            $input->getArgument('ip'),
            $input->getOption('ttl')
        );

        $this->printMessages($io, $response->getMessages());
        return $response->isStatusTypeSuccess() ? self::SUCCESS : self::FAILURE;
    }
}
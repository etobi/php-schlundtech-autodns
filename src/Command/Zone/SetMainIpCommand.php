<?php

namespace Etobi\Autodns\Command\Zone;

use Etobi\Autodns\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetMainIpCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName("zone:setmainip")
            ->setDefinition(array(
                new InputArgument('zone', InputArgument::REQUIRED, 'The name of the zone'),
                new InputArgument('ip', InputArgument::REQUIRED, 'The IP address'),
                new InputArgument('ttl', InputArgument::OPTIONAL, 'TTL', 600),
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $autoDns = $this->getAutoDns();
        $response = $autoDns->setMainip(
            $input->getArgument('zone'),
            $input->getArgument('ip'),
            $input->getArgument('ttl')
        );

        $this->printMessages($io, $response->getMessages());
        return $response->isStatusTypeSuccess() ? self::SUCCESS : self::FAILURE;
    }
}
<?php

namespace Etobi\Autodns\Command;

use Etobi\Autodns\Service\AutoDnsXmlResponse;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractAutodnsCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->getConfig($input);
        $io->comment('Use config "<comment>' . $config->getPath() . '</comment>"');
        
        $autoDns = $this->getAutoDns($config);
        $response = $this->perform($input, $io, $autoDns);
        if ($response instanceof AutoDnsXmlResponse) {
            $this->printMessages($io, $response->getMessages());
            return $response->isStatusTypeSuccess() ? self::SUCCESS : self::FAILURE;
        } else {
            return $response;
        }
    }

    abstract protected function perform(
        InputInterface $input,
        SymfonyStyle $io,
        AutoDnsXmlService $autoDns
    ): AutoDnsXmlResponse;
}

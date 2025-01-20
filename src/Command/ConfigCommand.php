<?php

namespace Etobi\Autodns\Command;

use Etobi\Autodns\ConfigLoader;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('config')
            ->setDescription('Helper to create a autodns.yaml config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configLoader = new ConfigLoader(allowNoConfiguartion: true);
        $configPath = $configLoader->getPath();
        if (file_exists($configPath)) {
            throw new \RuntimeException('Config file already exists: ' . $configPath);
        }

        $gateway = $io->askQuestion(
            new Question('Gateway', AutoDnsXmlService::BASEURI)
        );
        $context = $io->askQuestion(
            new Question('Context', AutoDnsXmlService::CONTEXT)
        );
        $username = $io->askQuestion(
            new Question('Username')
        );
        $password = $io->askHidden(
            'Password'
        );

        $config = [
            'autodns' => [
                'gateway' => (string)$gateway,
                'username' => (string)$username,
                'password' => (string)$password,
                'context' => (int)$context
            ]
        ];

        $configLoader->write($config);
        $io->success('Config file created: ' . $configLoader->getPath());
        return self::SUCCESS;
    }
}

<?php

namespace Etobi\Autodns\Command;

use Etobi\Autodns\ConfigLoader;
use Etobi\Autodns\Service\AutoDnsXmlService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class ConfigCommand extends AbstractCommand
{

    protected function configure(): void
    {
        $this->setName("config")
            ->setDescription("Helper to create a autodns.yaml config file");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configLoader = new ConfigLoader();
        $configPath = $configLoader->getConfigPath();
        if (file_exists($configPath)) {
            throw new \RuntimeException("Config file alread eists: $configPath");
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
                'gateway' => $gateway,
                'username' => $username,
                'password' => $password,
                'context' => $context
            ]
        ];

        file_put_contents(
            $configPath,
            Yaml::dump($config)
        );

        return self::SUCCESS;
    }
}
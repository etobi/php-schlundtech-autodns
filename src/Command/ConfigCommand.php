<?php

namespace Etobi\Autodns\Command;

use Etobi\Autodns\ConfigLoader;
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
            ->getDefinition()
            ->addArguments([
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configLoader = new ConfigLoader();
        $configPath = $configLoader->getConfigPath();
        if (file_exists($configPath)) {
            throw new \RuntimeException("Konfigurationsdatei existiert bereits: $configPath");
        }

        $gateway = $io->askQuestion(
            new Question('Gateway', 'https://gateway.schlundtech.de/')
        );
        $context = $io->askQuestion(
            new Question('Context', 10)
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
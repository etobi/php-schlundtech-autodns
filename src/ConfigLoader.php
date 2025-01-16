<?php

namespace Etobi\Autodns;

use Symfony\Component\Yaml\Yaml;
use Phar;

class ConfigLoader
{
    private array $config;

    public function __construct(string $configRelativePath = '/../config/app.yaml')
    {
        $basePath = Phar::running() ? dirname(Phar::running(false)) : __DIR__;
        $configPath = $basePath . $configRelativePath;
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Konfigurationsdatei nicht gefunden: $configPath");
        }
        $this->config = Yaml::parseFile($configPath);

        $this->config = Yaml::parseFile($configPath);
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}

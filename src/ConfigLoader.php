<?php

namespace Etobi\Autodns;

use Phar;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    private array $config;

    public function __construct(string $filename = 'autodns.yaml')
    {
        $configPath = $this->getConfigPath($filename);
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Konfigurationsdatei nicht gefunden: $configPath");
        }
        $this->config = Yaml::parseFile($configPath);
    }

    public function get(string $key, mixed $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function getConfigPath(?string $filename = null): string
    {
        if (empty($filename)) {
            $filename = 'autodns.yaml';
        }
        $basePath = Phar::running() ? dirname(Phar::running(false)) : __DIR__;
        $configPath = $basePath . '/../' . $filename;
        return realpath($configPath);
    }
}

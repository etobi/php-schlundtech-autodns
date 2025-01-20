<?php

namespace Etobi\Autodns;

use Phar;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    private ?array $config = null;

    public function __construct(string $filename = 'autodns.yaml', bool $allowNoConfiguartion = false)
    {
        $configPath = $this->getPath($filename);
        if (!file_exists($configPath)) {
            if (!$allowNoConfiguartion) {
                throw new \RuntimeException('Cant read config file: ' . $configPath);
            }
        }
    }

    protected function load()
    {
        if ($this->config === null) {
            $this->config = Yaml::parseFile($this->getPath());
        }
    }

    public function get(string $key, mixed $default = null)
    {
        $this->load();
        return $this->config[$key] ?? $default;
    }

    public function getPath(?string $filename = null): string
    {
        if ($filename === null) {
            $filename = 'autodns.yaml';
        }
        $basePath = (bool)Phar::running() ? dirname(Phar::running(false)) : __DIR__;
        $filePathsToCheck = [
            $basePath . '/../' . $filename,
            $basePath . '/' . $filename,
            '/etc/' . $filename,
        ];
        foreach ($filePathsToCheck as $value) {
            if (file_exists($value)) {
                return realpath($value) !== false ? realpath($value) : $value;
            }
        }
        return realpath($filePathsToCheck[0]) !== false ? realpath($filePathsToCheck[0]) : $filePathsToCheck[0];
    }

    public function write(array $config)
    {
        file_put_contents(
            $this->getPath(),
            Yaml::dump($config)
        );
    }
}

<?php

namespace Controllers;

class Config
{
    private array $config;

    public function __construct()
    {
        $content = file_get_contents('./config.json');
        $this->config = json_decode($content, true);
    }

    public function get(string $key): ?string
    {
        $key = mb_strtoupper($key);

        return $this->config[$key] ?? null;
    }
}
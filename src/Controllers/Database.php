<?php

namespace Controllers;

use MongoDB\Client;

class Database
{
    private Config $config;


    public function __construct()
    {
        $this->config = new Config();
    }

    public function connect(): Client
    {
        $host = $this->config->get('db_host');
        $port = $this->config->get('db_port');
        return new Client("mongodb://$host:$port");
    }
}
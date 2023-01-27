<?php

namespace src;

use Controllers\Config;
use MongoDB\Client;

class VkApi
{
    private Config $config;

    private ?string $access_token;
    private ?string $client_id;
    private ?string $client_secret;
    private ?string $code;

    public function __construct()
    {
        $this->config = new Config();
        $this->client_id = $this->config->get('vk_client_id');
        $this->client_secret = $this->config->get('vk_client_secret');
        $this->access_token = $this->config->get('vk_access_token');
        $this->code = $this->config->get('vk_code');
    }

    public function auth(): bool
    {
        if ($this->access_token) {
            return true;
        }

        if (!$this->code) {
            $this->getCodeMessage();
            return true;
        }

        $this->access_token = $this->getAccessToken() ?? null;
        if ($this->access_token) {
            return true;
        }

        return false;
    }

    private function getCodeMessage(): void
    {
        $params = [
            'client_id' => $this->client_id,
            'display' => 'page',
            'redirect_uri' => 'https://localhost:8000',
            'scope' => 'offline,stories,photos,app_widget,groups,docs,manage,wall',
            'response_type' => 'code',
        ];

        $url = 'https://oauth.vk.com/authorize?' . http_build_query($params);

        echo 'Получите code из адресной строки после подтверждения по ссылке: ' . $url;
    }

    private function getAccessToken(): ?string
    {
        $params = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => 'https://localhost:8000',
            'code' => $this->code
        ];

        $url = 'https://oauth.vk.com/access_token?' . http_build_query($params);
        $response = file_get_contents($url);
        $data = json_decode($response['content'], true);

        return $data['access_token'] ?? null;
    }

    public function getGroupMembers(int $group_id, int $offset = 0): string
    {
        $code = '
            var result = [];
            var step = 0;
            var offset = ' . $offset .';
            
            while(step < 25) {
                var response = API.groups.getMembers({
                    "group_id": ' . $group_id . ',
                    "count": 1000,
                    "offset" : ' . $offset .',
                    "fields": "lists,bdate,city,country",
                    "v": "5.131",
                });
                
                step = step + 1;
                offset = offset + 1000;
                var users = response.items;
                result.push(users);
            }
      
            return result;
        ';

        $params = [
            'code' => $code,
            'access_token' => $this->access_token,
            'v' => '5.131'
        ];

        $url = 'https://api.vk.com/method/execute?' . http_build_query($params);

        return file_get_contents($url);
    }
}
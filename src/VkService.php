<?php

namespace src;

use Controllers\Database;
use Controllers\Config;
use MongoDB\Client;

class VkService
{
    private Client $database;
    private VkApi $api;

    const SECONDS_IN_YEAR = 31536000;

    public function __construct()
    {
        $this->database = (new Database())->connect();
        $this->api = new VkApi();
    }

    public function getMembersFromGroup(): int
    {
        if (! $this->api->auth()) {
            throw new \Exception('Не удалось авторизоваться');
        }

        $group_id = (new Config())->get('VK_PARSE_GROUP_ID');
        $collection = $this->database->vkapi->members;

        $found = $collection->find(['group_id' => $group_id])->toArray();
        $offset = count($found);

        $data = $this->api->getGroupMembers($group_id, $offset);
        $data = json_decode($data, true);

        $count = 0;
        foreach ($data['response'] as $datum) {
            foreach ($datum as $user) {
                $save_user = [
                    'user_id' => $user['id'],
                    'group_id' => $group_id,
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'birth_date' => $user['bdate'] ?? '',
                    'age' => $this->ageCalculateByDate($user['bdate'] ?? ''),
                    'country' => $user['country']['title'] ?? '',
                    'city' => $user['city']['title'] ?? ''
                ];

                $updated = $collection->updateOne(
                    [
                        'user_id' => $save_user['user_id'],
                        'group_id' => $save_user['group_id'],
                    ],
                    [ '$set' => $save_user ],
                    ['upsert' => true, 'multiple' => true]
                );

                $count += $updated->getUpsertedCount();
            }
        }

        echo "Добавлено $count участников \n";
        echo "Всего сохранено участников в группе (id:$group_id) {$collection->count()} участников \n";

        return $count;
    }

    private function ageCalculateByDate(string $date): ?int
    {
        $timestamp = strtotime($date);
        if ($timestamp) {
            $dif = time() - $timestamp;
            $age = $dif / self::SECONDS_IN_YEAR;
            if ((int)$age > 1) {
                return $age;
            }
        }

        return null;
    }
}

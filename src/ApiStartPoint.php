<?php

namespace src;

use MongoDB\Client;

class ApiStartPoint
{

    public function run()
    {
        $work = 1;
        while($work) {
            $work = (new VkService())->getMembersFromGroup();
            sleep(0.5);
        }

    }
}
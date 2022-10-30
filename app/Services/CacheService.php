<?php

namespace superbot\App\Services;

use superbot\App\Storage\RedisController;

class CacheService
{
    public function __construct(RedisController $connection)
    {
        $this->redisController = $connection;
    }

    public function getStartMessage() {
        return $this->redisController->get('start_message');
    }

    public function setStartMessage($message, $expire = 30) {
        return $this->redisController->set('start_message', $message, $expire);
    }
}

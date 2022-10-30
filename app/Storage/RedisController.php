<?php

namespace superbot\App\Storage;

use \Redis;

class RedisController
{
    private $conn;
    public function __construct(Redis $connection)
    {
        $this->conn = $connection;
    }

    public function get($name)
    {
        return $this->conn->get($name);
    }

    public function set($name, $value, $ex = null)
    {
        return $this->conn->set($name, $value, ['nx', 'ex' => $ex]);
    }
}

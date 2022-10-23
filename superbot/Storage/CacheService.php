<?php
namespace superbot\Storage;
use \Redis;
class CacheService {
    public function __construct(Redis $connection)
    {
        $this->conn = $connection;
    }
}
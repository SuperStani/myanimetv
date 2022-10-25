<?php

namespace superbot\App\Controllers;

use superbot\Telegram\Query;
use superbot\Telegram\User;
use superbot\Storage\DB;
use superbot\Storage\CacheService;
use superbot\App\Logger\Log;
class QueryController extends Controller
{
    public function __construct(Query $query, DB $conn, User $user, CacheService $cacheService, Log $logger)
    {
        $this->query = $query;
        $this->conn = $conn;
        $this->user = $user;
        $this->cache = $cacheService;
        $this->logger = $logger;
    }

    public function error()
    {
        $this->query->message->delete();
        $this->query->message->reply("Il bot è in manutenzione\nLa disponibilità è prevista per le *16:00*");
    }
}

<?php

namespace superbot\App\Controllers;

use app\Logger\Log;
use superbot\Telegram\Message;
use superbot\Telegram\User;
use superbot\Storage\DB;
use superbot\Storage\CacheService;

class MessageController extends Controller{
    public function __construct(Message $message, User $user, DB $conn, CacheService $cacheService, Log $logger) {
        $this->message = $message;
        $this->conn = $conn;
        $this->user = $user;
        $this->logger = $logger;
        $this->cache = $cacheService;
    }

    public function error(){
        $this->message->reply("Il bot è in manutenzione\nLa disponibilità è prevista per le *16:00*");
    }
}
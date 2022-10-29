<?php

namespace superbot\App\Controllers;

use superbot\App\Logger\Log;
use superbot\Telegram\Message;
use superbot\App\Controllers\UserController;


class MessageController extends Controller{
    public function __construct(Message $message, UserController $user, Log $logger) {
        $this->message = $message;
        $this->user = $user;
        $this->logger = $logger;
    }

    public function error(){
        $this->message->reply("Il bot è in manutenzione\nLa disponibilità è prevista per le *16:00*");
    }
}
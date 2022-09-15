<?php

namespace superbot\App\Controllers;
use superbot\Telegram\Message;
use superbot\Telegram\User;
use superbot\Database\DB;

class MessageController extends Controller{
    public function __construct($update) {
        $this->message = new Message($update);
        $this->conn = new DB();
        $this->user = new User($update->from, $this->conn);
        $update = null;
    }

    public function error(){
        $this->message->reply("Il bot è in manutenzione\nLa disponibilità è prevista per le *16:00*");
    }
}
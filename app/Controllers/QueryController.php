<?php

namespace superbot\App\Controllers;
use superbot\Telegram\Query;
use superbot\Telegram\User;
use superbot\Database\DB;

class QueryController extends Controller{
    public function __construct($update) {
        $this->query = new Query($update);
        $this->conn = new DB();
        $this->user = new User($update->from, $this->conn);
        $update = null;
    }

    public function error(){
        $this->query->message->delete();
        $this->query->message->reply("Il bot è in manutenzione\nLa disponibilità è prevista per le *16:00*");
    }
}
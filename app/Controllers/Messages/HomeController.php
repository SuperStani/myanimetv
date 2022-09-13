<?php
namespace superbot\App\Controllers\Messages;
use superbot\App\Controllers\MessageController;
use superbot\Database\DB;
use superbot\Telegram\Client;

class HomeController extends MessageController {
    public function start($id) {
        $this->message->reply("Nice stani $id");
    }
}
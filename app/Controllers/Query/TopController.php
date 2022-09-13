<?php

namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\Database\DB;
use superbot\Telegram\Client;

class LeadershipController extends QueryController {
    public function calendar() {
        print_r("gg");
        $this->query->message->delete();
        $this->query->message->reply("gg");
    }
}
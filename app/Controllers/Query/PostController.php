<?php
namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\Database\DB;
use superbot\Telegram\Client;
use superbot\App\Config\GeneralConfigs as cfg;

class PostController extends QueryController {
    public function new() {
        $this->user->page("Post:poster");
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Home:start"]];
        $this->query->message->delete();
        $this->query->message->reply("Ok, inviami il poster:", $menu);
    }
}
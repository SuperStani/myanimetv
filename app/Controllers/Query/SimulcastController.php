<?php
namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\Database\DB;
use superbot\Telegram\Client;

class SimulcastController extends QueryController {
    public function calendar() {
        print_r("gg");
        $this->query->message->delete();
        $this->query->message->reply("gg");
    }

    public function setup($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $this->user->page("Settings:simulcastBanner|$id|{$this->query->message->id}|1");
        return $this->query->message->edit("*Ok, invia il banner del simulcast:*", $menu);
    }
}
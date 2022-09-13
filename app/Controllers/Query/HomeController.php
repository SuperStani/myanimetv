<?php
namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\Database\DB;
use superbot\Telegram\Client;
use superbot\App\Config\GeneralConfigs as cfg;
class HomeController extends QueryController {
    public function start() {
        $this->user->page();
        if($this->user->isAdmin())
            $menu[] = [["text" => "➕ ADD NEW ANIME", "callback_data" => "Post:new"]];
        $menu[] = [["text" => get_button('it', 'search'), "callback_data" => "Search:home|1"],["text" => get_button('it', 'profile'), "callback_data" => "Profile:me|1"]];
        $menu[] = [["text" => get_button('it', 'top'), "web_app" => ["url" => cfg::get('webapp')."leadership"]]];
        $total_anime = $this->conn->rquery("SELECT COUNT(*) AS tot FROM anime WHERE season < 2")->tot;
        $total_episodes = $this->conn->rquery("SELECT COUNT(*) AS tot FROM episodes")->tot;
        $total_subs = $this->conn->rquery("SELECT COUNT(*) AS tot FROM users")->tot;
        $text = get_string('it', 'home', $this->user->mention, $total_subs, $total_anime, $total_episodes);
        //$this->query->message->delete();
        return $this->query->message->edit($text, $menu);
    }
}
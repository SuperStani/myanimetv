<?php

namespace superbot\App\Controllers\Query;

use superbot\App\Controllers\QueryController;
use superbot\Database\DB;
use superbot\Telegram\Client;
use superbot\App\Config\GeneralConfigs as cfg;
use DateTime;

class ProfileController extends QueryController
{
    public function me($delete_message)
    {
        $webapp = cfg::get('webapp') . "watchlist";
        $total_anime = $this->conn->rquery("SELECT COUNT(*) AS tot FROM anime a INNER JOIN anime_watchlists aw ON a.id = aw.anime WHERE aw.user = ? AND aw.list = 1  AND a.season < 2 AND a.category <> 'Movie'", $this->user->id)->tot;
        $total_movies =  $this->conn->rquery("SELECT COUNT(*) AS tot FROM anime a INNER JOIN anime_watchlists aw ON a.id = aw.anime WHERE aw.user = ? AND aw.list = 1  AND a.season < 2 AND a.category = 'Movie'", $this->user->id)->tot;
        $episodes = $this->conn->rquery("SELECT COUNT(*) AS tot, SUM(e.duration) as tot_duration FROM episodes e INNER JOIN anime_watchlists aw ON e.anime = aw.anime WHERE aw.user = ? AND aw.list = 1", $this->user->id);
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$episodes->tot_duration");
        $ore = $dtF->diff($dtT)->format('%a giorni | %h h | %i min');
        $text = get_string('it', 'profile', $this->user->mention, $total_anime, $total_movies, $episodes->tot, $ore);
        $menu[] = [["text" => "❤️ PREFERITI", "web_app"  => ["url" => "$webapp/preferreds/{$this->user->id}"]], ["text" => "🔵 COMPLETED", "web_app"  => ["url" => "$webapp/completed/{$this->user->id}"]]];
        $menu[] = [["text" => "🟢 WATCHING", "web_app"  => ["url" => "$webapp/watching/{$this->user->id}"]], ["text" => "⚪️ PLAN TO WATCH", "web_app"  => ["url" => "$webapp/plantowatch/{$this->user->id}"]]];
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Home:start|1"]];
        if ($delete_message) {
            $this->query->message->delete();
            return $this->query->message->reply($text, $menu);
        } else {
            $this->query->alert();
            return $this->query->message->edit($text, $menu);
        }
    }
}

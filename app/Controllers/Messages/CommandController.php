<?php
namespace superbot\App\Controllers\Messages;
use superbot\App\Controllers\MessageController;
use superbot\App\Controllers\Query\AnimeController;
use superbot\App\Controllers\Query\PlayerController;
use superbot\Telegram\Client;
use superbot\App\Config\GeneralConfigs as cfg;
use superbot\Telegram\Update;
class CommandController extends MessageController {
    
    public function start($param = null) {
        if(!$param) {
            $this->user->save();
            $this->user->page();
            $webapp = cfg::get('webapp');
            if($this->user->isAdmin())
                $menu[] = [["text" => "➕ ADD NEW ANIME", "callback_data" => "Post:new"]];
            $menu[] = [["text" => get_button('it', 'search'), "callback_data" => "Search:home|0"], ["text" => get_button('it', 'profile'), "callback_data" => "Profile:me|0"]];
            $menu[] = [["text" => get_button('it', 'top'), "web_app" => ["url" => cfg::get('webapp')."leadership"]]];
            $total_anime = $this->conn->rquery("SELECT COUNT(*) AS tot FROM anime WHERE season < 2")->tot;
            $total_episodes = $this->conn->rquery("SELECT COUNT(*) AS tot FROM episodes")->tot;
            $total_subs = $this->conn->rquery("SELECT COUNT(*) AS tot FROM users")->tot;
            $text = get_string('it', 'home', $this->user->mention, $total_subs, $total_anime, $total_episodes);
            return $this->message->reply($text, $menu);
        }else {
            $param = explode("_", $param);
            if($param[0] == "anime") 
                return $this->sendAnime($param[1]);
            elseif($param[0] == "episode")
                return $this->sendEpisode($param[1], $param[2]);
            elseif($param[0] == "settings") 
                return $this->sendSettings($param[1], $param[2]);
            elseif ($param[0] == "uploadEp") 
                return $this->uploadEpisode($param[1]);
        }
    }

    public function sendAnime($id) {
        $update = Update::getFakeUpdate($this->user->id, "Anime:view|$id|-1");
        $anime = new AnimeController($update->callback_query);
        return $anime->view($id, -1);
    }

    public function sendEpisode($anime_id, $episodeNumber) {
        $update = Update::getFakeUpdate($this->user->id, "Anime:view|$anime_id|-1");
        $episode = new PlayerController($update->callback_query);
        return $episode->play($anime_id, $episodeNumber, false);
    }

    public function uploadEpisode($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $this->user->page("Settings:uploadEpisode|$id|{$this->query->message->id}");
        return $this->message->reply("*Ok, invia l'episodio:*", $menu);
    }

    public function sendSettings($id, $message_id) {
        $this->message->delete();
        $menu[] = [["text" => "✏️ TITOLO", "callback_data" => "Settings:title|$id"], ["text" => "✏️ POSTER", "callback_data" => "Settings:poster|$id"]];
        $menu[] = [["text" => "✏️ NR. STAGIONE", "callback_data" => "Settings:season|$id"], ["text" => "✏️ GRUPPO", "callback_data" => "Settings:group|$id"]];
        $menu[] = [["text" => "✏️ DURATA EP", "callback_data" => "Settings:duration|$id"], ["text" => "✏️ GENERI", "callback_data" => "Settings:genres|$id"]];
        $menu[] = [["text" => "✏️ NR.EP", "callback_data" => "Settings:episodes|$id"], ["text" => "✏️ DATA", "callback_data" => "Settings:aired_on|$id"]];
        $menu[] = [["text" => "✏️ TRAMA", "callback_data" => "Settings:|$id|synopsis"], ["text" => "✏️ TRAILER", "callback_data" => "Settings:trailer|$id"]];
        $menu[] = [["text" => "✏️ CATEGORIA", "callback_data" => "Settings:category|$id"], ["text" => "✏️ STUDIO", "callback_data" => "Settings:studio|$id"]];
        $menu[] = [["text" => "🔁 REIMPOSTA", "callback_data" => "Settings:reloadInfo|$id"], ["text" => "🗑 ELIMINA EP", "callback_data" => "Settings:deleteEpisodes|$id|0"]];
        $issimulcast = isset($this->conn->rquery("SELECT anime FROM anime_simulcasts WHERE anime = ?", $id)->anime);
        if($issimulcast) {
            $menu[] = [["text" => "🖌 TITOLO ON-GOING", "callback_data" => "Simulcast:title|$id"]];
            $menu[] = [["text" => "🖌 IMMAGINE ON-GOING", "callback_data" => "Simulcast:poster|$id"]];
            $menu[] = [["text" => "❌ RIMUOVI ON-GOING", "callback_data" => "Simulcast:remove|$id"]];
        }else{
            $menu[] = [["text" => "✳️ IMPOSTA ON-GOING", "callback_data" => "Simulcast:settup|$id"]];
        }
        $menu[] = [["text" => "➕ AGGIUNGI EPISODIO", "callback_data" => "Settings:uploadEpisode|$id"]];
        $menu[] = [["text" => "📤 INVIA", "callback_data" => "Settings:sendAnime|$id"], ["text" => "🗑 ELIMINA", "callback_data" => "Settings:removeAnime|$id"]];
        $menu[] = [["text" => "✖️ CHIUDI IMPOSTAZIONI ✖️", "callback_data" => "Settings:close"]];
        return $this->message->reply("*Seleziona un'opzione qua sotto:*", $menu, 'Markdown', false);
    }

    public function check(){
        $e = explode(" ", $this->message->text);
        $method = str_replace("/", "", $e[0]);
        unset($e[0]);
        return $this->callAction($method, $e);
    }
}
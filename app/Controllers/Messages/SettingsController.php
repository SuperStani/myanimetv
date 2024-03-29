<?php
namespace superbot\App\Controllers\Messages;
use superbot\App\Controllers\MessageController;
use superbot\App\Controllers\Query\AnimeController;
use superbot\Telegram\Client;
use superbot\Telegram\Update;
use superbot\App\Config\GeneralConfigs as cfg;
use superbot\App\Controllers\Query\SearchController;

class SettingsController extends MessageController {

    public function uploadEpisode($id, $message_id) {
        $m = Client::sendVideo(cfg::get('episodesChannel'), $this->message->video->file_id);
        $episodeNumber = $this->conn->rquery("SELECT MAX(episodeNumber) AS ep FROM episodes WHERE anime = ?", $id)->ep + 1;
        $episode_id = $this->conn->wquery("INSERT INTO episodes SET anime = ?, video_id = ?, episodeNumber = ?, duration = ?, size = ?", $id, $m->result->message_id, $episodeNumber, $this->message->video->duration, $this->message->video->file_size);
        $anime = $this->conn->rquery("SELECT a.name, a.season, s.anime AS simulcast, s.name AS sName, s.poster FROM anime a LEFT OUTER JOIN anime_simulcasts s ON a.id = s.anime WHERE a.id = ?", $id);
        $is_simulcast = isset($anime->simulcast);
        if($is_simulcast) {
            $this->user->page();
            $this->message->delete();
            $poster = "https://myanimetv.org/bots/myanimetv/resources/img/{$anime->poster}.jpg";
            $nome = $anime->sName;
            $users = $this->conn->rqueryAll("SELECT user FROM anime_simulcast_notify WHERE anime = ?", $id);
            $menu = [[["text" => "⭐️ GUARDA ORA ", "callback_data" => "Player:play|$id|$episodeNumber|1"]]];
            $keyboard["inline_keyboard"] = $menu;
            foreach($users as $u){ //Notifica a tutti coloro che seguono l'anime
                Client::sendPhoto($u->user, $poster, "<b>$nome</b> disponibile ora l'episodio <b>$episodeNumber</b>", 'html', null, true, null, null, null, $keyboard);
                usleep(300000);
            }
            /*if($episodeNumber > 1){
                $menu = [[["text" => "⭐️ GUARDA ORA ", "url" => "t.me/myanimetvbot?start=ep_{$id}_{$episodeNumber}"]]];
                Client::sendSticker(cfg::get('mainChannel'), "CAACAgQAAxkBAAECn-tePYyWjApO0tANmNtfakX-dma1EgACEwAD3VA4GM3t39MKCGhHGAQ");
                Client::sendPhoto(cfg::get('mainChannel'), $poster, "<b>$nome</b> disponibile ora l'episodio <b>$episodeNumber</b>", 'html', null, true, null, null, null, $keyboard);
            }else 
                //AnimeController::sendAnimeToChannel($id, true);*/
            $menu = null;
            $menu = [[["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]]];
            $keyboard["inline_keyboard"] = $menu;
            Client::editMessageText($this->user->id, $message_id, null, "<b>✅ EPISODIO $episodeNumber CARICATO</b>", "html", null, false, $keyboard);
        }else{
            $menu[] = [["text" => "❌ ANNULLA ❌", "callback_data" => "Anime:deleteEpisode|$episode_id|1"]];
            $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "Settings:home|$id"]];
            $this->message->reply("*✅ EPISODIO $episodeNumber CARICATO*", $menu, 'Markdown', false, $this->message->id);
        }
    }
    
    public function reloadInfo($id, $message_id, $add = 0) {
        if($this->message->find("http")) {
            $r = json_decode(file_get_contents("https://api.myanimetv.org/scrape/info?url=".$this->message->text), true);
            $mese = ["gennaio", "febbraio", "marzo", "maggio", "aprile", "giugno", "luglio", "agosto", "settembre", "ottobre", "novembre", "dicembre"];
            $uscita = $r["uscita"];
            if(strpos($uscita, "?") === 0)
                $date = null;
            else {
                $e = explode(" ", $uscita);
                $d = str_replace("??", "01", $e[0]); $m = str_replace($mese, ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"], strtolower($e[1])); $y = $e[2];
                $date = "$y-$m-$d";
            }
            
            $genres = $r["generi"];
            $synonyms = $r["alternative-title"]. ", ".$r["name"];
            $duration = (int)str_replace("??", "24", $r["durata_ep"]);
            $episodes = (int) str_replace("?", '0', $r["episodi"]);
            $synopsis = $r["trama"];
            $trailer = $r["trailer"];
            $mal_id = $r["mal_id"];
            $studios = explode(",", $r["studio"]);
            $this->conn->wquery("DELETE FROM anime_studios WHERE anime = ?", $id);
            foreach($studios as $studio){
                $studio_id = $this->conn->rquery("SELECT id FROM studios WHERE name = ?", trim($studio))->id;
                $this->conn->wquery("INSERT INTO anime_studios SET anime = ?, studio = ?", $id, $studio_id);
            }
            $this->conn->wquery("DELETE FROM anime_genres WHERE anime = ?", $id);
            foreach($genres as $genre){
                $genre = str_replace(["-", " "], ["", ""], $genre);
                $this->conn->wquery("INSERT INTO anime_genres SET anime = ?, genre = (SELECT id FROM genres WHERE name LIKE ?)", $id, "%$genre%");
            }
        
            $nodes = [
                [
                    "tag" => "p",
                    "children" => [
                        str_replace(["&quot;", "&#039;"], ["\"", "'"], $synopsis)
                    ]
                ]
            ];
            $token = "6e6bb3d16c6201fe33efdebb37bf7b912a334303d900825f0411a2f41912";
            $synopsis_url = json_decode(file_get_contents("https://api.telegra.ph/createPage?access_token=$token&title=".strtoupper(str_replace([" ", "'"],["+", ""], explode(",", $synonyms)[1]))."&author_name=MY+ANIME+TV&content=".urlencode(json_encode($nodes))."&return_content=true"), true)["result"]["url"];
           $this->conn->wquery("UPDATE anime SET synonyms = ?, aired_on = ?, episodes = ?, duration = ?, synopsis = ?, synopsis_url = ?, trailer = ?, mal_id = ? WHERE id = ?", $synonyms, $date, $episodes, $duration, $synopsis, $synopsis_url, $trailer, $mal_id, $id);
            
            if($add)
                $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Anime:view|$id|1"]];
            else
                $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
            $keyboard["inline_keyboard"] = $menu;
            $this->message->delete();
            Client::editMessageText($this->user->id, $message_id, null, "Informazioni dell'anime aggiunte con successo!", "html", null, false, $keyboard);
        }
    }

    public function title($id, $message_id) {
        $this->message->delete();
        $e = $this->message->split("+", 2);
        if(isset($e[1])) 
            $this->conn->wquery("UPDATE anime SET name = ?, synonyms = ? WHERE id = ?", $e[0], (empty($e[1])) ? 'NULL' : $e[1], $id);
        else
            $this->conn->wquery("UPDATE anime SET name = ? WHERE id = ?", $e[0], $id);
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $keyboard["inline_keyboard"] = $menu;
        Client::editMessageText($this->user->id, $message_id, null, "Modifica effettuata con successo!", "html", null, false, $keyboard);
    }

    public function poster($id, $message_id) {
        if(isset($this->message->photo)) {
            $photo_to_delete = $this->conn->rquery("SELECT poster FROM anime WHERE id = ?", $id)->poster;
            file_get_contents("https://myanimetv.org/bots/myanimetv/photoshop/?delete=1&name=$photo_to_delete");
            $photo_file_id = $this->message->photo[count($this->message->photo) - 1]->file_id;
            $photo = "https://api.telegram.org/file/bot".cfg::get("bot_token")."/".Client::getFile($photo_file_id)->result->file_path;
            file_get_contents("https://myanimetv.org/bots/myanimetv/photoshop/?img=$photo&anime=1&name=$photo_file_id");
            $anime = $this->conn->wquery("UPDATE anime SET poster = ? WHERE id = ?", $photo_file_id, $id);
            $this->user->page();
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
            $keyboard["inline_keyboard"] = $menu;
            $this->message->delete();
            Client::editMessageText($this->user->id, $message_id, null, "Modifica effettuata con successo!", "html", null, false, $keyboard);
        }
    }

    public function season($id, $message_id) {
        $this->message->delete();
        $this->conn->wquery("UPDATE anime SET season = ? WHERE id = ?", $this->message->text, $id);
        $this->user->page();
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $keyboard["inline_keyboard"] = $menu;
        Client::editMessageText($this->user->id, $message_id, null, "Modifica effettuata con successo!", "html", null, false, $keyboard);
    }

    public function episodes($id, $message_id) {
        $this->message->delete();
        $this->conn->wquery("UPDATE anime SET episodes = ? WHERE id = ?", $this->message->text, $id);
        $this->user->page();
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $keyboard["inline_keyboard"] = $menu;
        Client::editMessageText($this->user->id, $message_id, null, "Modifica effettuata con successo!", "html", null, false, $keyboard);
    }

    public function orderView($id, $message_id) {
        $this->message->delete();
        $this->conn->wquery("UPDATE anime_groups SET viewOrder = ? WHERE anime = ?", $this->message->text, $id);
        $this->user->page();
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:group|$id"]];
        $keyboard["inline_keyboard"] = $menu;
        Client::editMessageText($this->user->id, $message_id, null, "Modifica effettuata con successo!", "html", null, false, $keyboard);
    }

    public function newGroup($id, $message_id) {
        $this->message->delete();
        $group = $this->conn->wquery("INSERT INTO groups_list SET name = ?", $this->message->text);
        $this->conn->wquery("INSERT INTO anime_groups SET group_id = ?, anime = ?, viewOrder = (SELECT season FROM anime WHERE id = ?)", $group, $id, $id);
        $this->user->page();
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:group|$id"]];
        $keyboard["inline_keyboard"] = $menu;
        Client::editMessageText($this->user->id, $message_id, null, "Modifica effettuata con successo!", "html", null, false, $keyboard);
    }
}
<?php
namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\Database\DB;
use superbot\Telegram\Client;
use superbot\App\Config\GeneralConfigs as cfg;

class SettingsController extends QueryController {

    public function home($id, $delete = 0) {
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
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Anime:view|$id|1"]];
        $this->user->page();
        if($delete) {
            $this->query->message->delete();
            return $this->query->message->reply("*Seleziona un'opzione qua sotto:*", $menu);
        }else
            return $this->query->message->edit("*Seleziona un'opzione qua sotto:*", $menu);
    }

    public function uploadEpisode($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $this->user->page("Settings:uploadEpisode|$id|{$this->query->message->id}");
        return $this->query->message->edit("*Ok, invia l'episodio:*", $menu);
    }

    public function reloadInfo($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $this->user->page("Settings:reloadInfo|$id|{$this->query->message->id}");
        return $this->query->message->edit("*Ok, invia il link di animeworld per resettare le informazioni dell'anime:*", $menu);
    }

    public function title($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $title = $this->conn->rquery("SELECT name, synonyms FROM anime WHERE id = ?", $id);
        $this->user->page("Settings:title|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia il nuovo titolo dell'anime:\nTitolo attuale: <code>$title->name</code>\nAlternativi attuali: <code>$title->synonyms</code>\n\n<b>N.B.</b> <i>Per aggiungere titoli alternativi usa questa sintassi \"TITOLO_PRINCIPALE + NOME_ALT1, NOME_ALT2\"</i>", $menu, 'HTML');
    }

    public function poster($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $this->user->page("Settings:poster|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia il nuovo poster dell'anime:", $menu, 'HTML');
    }

    public function season($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $season = $this->conn->rquery("SELECT season FROM anime WHERE id = ?", $id)->season;
        $this->user->page("Settings:season|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia il numero della stagione:\nNr.Stagione attuale: <b>$season</b>\n\n<b>N.B</b> <i>Inviare 0 se l'anime è singolo (Senza altre stagioni)</i>", $menu, 'HTML');
    }

    public function group($id, $confirm = 0) {
        $group = $this->conn->rqueryAll("SELECT a.id, a.name, a.season, ag.viewOrder, ag.group_id FROM anime a INNER JOIN anime_groups ag ON a.id = ag.anime WHERE ag.group_id = (SELECT group_id FROM anime_groups WHERE anime = ?)", $id);
        if(count($group)) {
            $text = "";
            foreach($group as $anime) {
                $name = "$anime->name".(($anime->season > 0) ? " S$anime->season" : "");
                if($anime->id == $id)
                    $name = "<b>$name</b>";
                $text = $text."<a href='t.me/myanimetvbetabot?start=anime_$anime->id'>$name</a> <i>[$anime->viewOrder]</i>\n";
            }
            $menu[] = [["text" => "✏️ ORDINE DI VIEW", "callback_data" => "Settings:orderView|$id"]];
            $menu[] = [["text" => "❌ RIMUOVI DAL GRUPPO", "callback_data" => "Settings:deleteFromGroup|$id|$confirm"]];
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
            return $this->query->message->edit($text, $menu, "html");
        }else {
            $this->user->page("Search:groupForAnime|$id|{$this->query->message->id}");
            $menu[] = [["text" => "➕ NUOVO GRUPPO", "callback_data" => "Settings:newGroup|$id"]];
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
            return $this->query->message->edit("*Invia il nome del gruppo a cui vuoi aggiungerlo, oppure crearne uno nuovo:*", $menu);
        }
    }

    public function newGroup($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:group|$id"]];
        $this->user->page("Settings:newGroup|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia il nome del nuovo gruppo", $menu, 'HTML');
    }

    public function addInGroup($id, $group) {
        $this->conn->wquery("INSERT INTO anime_groups SET group_id = ?, anime = ?, viewOrder = (SELECT season FROM anime WHERE id = ?)", $group, $id, $id);
        $this->user->page();
        return $this->group($id);
    }

    public function orderView($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:group|$id"]];
        $this->user->page("Settings:orderView|$id|{$this->query->message->id}");
        return $this->query->message->edit("<b>Ok, invia l'ordine di visualizzazione dell'anime</b>", $menu, 'HTML');
    }

    public function deleteFromGroup($id, $confirm) {
        if($confirm) {
            $this->conn->wquery("DELETE FROM anime_groups WHERE anime = ?", $id);
            $this->home($id, 1);
        }else {
            $this->query->alert("Clicca un'altra volta per confermare");
            return $this->group($id, 1);
        }
    }

    public function duration($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $duration = $this->conn->rquery("SELECT duration FROM anime WHERE id = ?", $id)->duration;
        $this->user->page("Settings:duration|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia la nuova durata media degli episodi:\nDurata attuale: <code>$duration min/ep</code>", $menu, 'HTML');
    }

    public function episodes($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $episodes = $this->conn->rquery("SELECT episodes FROM anime WHERE id = ?", $id)->episodes;
        $this->user->page("Settings:episodes|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia il nuovo numero di episodi:\nEpisodi attuali: <b>$episodes</b>\n\n<b>N.B.</b> <i>quando il numero di episodi è indeterminato invia 0</i>", $menu, 'HTML');
    }

    public function aired_on($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $aired = explode("-", $this->conn->rquery("SELECT aired_on FROM anime WHERE id = ?", $id)->aired_on);
        $data = $aired[2]." ".["", "Gennaio", "Febbraio", "Marzo", "Maggio", "Aprile", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"][(int)$aired[1]]." ".$aired[0];
        $this->user->page("Settings:aired|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia la nuova data di uscita dell'anime:\nData attuale: <code>$data</code>\n\n<b>N.B.</b> <i>il formato è \"giorno mese anno\"</i>", $menu, 'HTML');
    }

    public function synopsis($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $synopsis = $this->conn->rquery("SELECT synopsis_url FROM anime WHERE id = ?", $id)->synopsis;
        $this->user->page("Settings:synopsis|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia la nuova trama:\nTrama attuale: $synopsis", $menu, 'HTML');
    }

    public function trailer($id) {
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        $trailer = $this->conn->rquery("SELECT trailer FROM anime WHERE id = ?", $id)->trailer;
        $this->user->page("Settings:trailer|$id|{$this->query->message->id}");
        return $this->query->message->edit("Ok, invia il nuovo trailer:\nTrailerattuale: $trailer", $menu, 'HTML');
    }


    public function category($id, $category = null) {
        if(!$category) {
            $q = $this->conn->rqueryAll("SELECT id, name FROM categories");
            foreach($q as $row){
                $menu[] = [["text" => $row->name, "callback_data" => "Settings:category|$id|$row->name"]];
            }
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
            return $this->query->message->edit("Seleziona la categoria", $menu, 'HTML');
        }else {
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
            $this->conn->wquery("UPDATE anime SET category = ? WHERE id = ?", $category, $id);
            return $this->query->message->edit("Modifica effettuata con successo!", $menu);
        }
    }

    public function genres($id, $genre = null) {
        if($genre != null) {
            $this->query->alert();
            $this->conn->wquery("DELETE FROM anime_genres WHERE genre = ? AND anime = ?", $genre, $id);
            $this->conn->wquery("INSERT INTO anime_genres SET genre = ?, anime = ?", $genre, $id);
        }
        $q = $this->conn->rqueryAll("SELECT id, name FROM genres");
        $x = 0; $y = 0;
        foreach($q as $g){
            $check = isset($this->conn->rquery("SELECT genre FROM anime_genres WHERE genre = ? AND anime = ?", $g->id, $id)->genre);
            if($x < 2){ $x++;}
            else { $x = 1; $y++;}
            $menu[$y][] = ["text" => "$g->name " .( ($check) ? '🔵' : '🔴' ), "callback_data" => "Settings:genres|$id|$g->id"];
        }
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:home|$id"]];
        return $this->query->message->edit("Seleziona i generi:", $menu);
    }


    public function close() {
        $this->query->message->delete();
    }
}
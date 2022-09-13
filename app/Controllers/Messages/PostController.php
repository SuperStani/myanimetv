<?php
namespace superbot\App\Controllers\Messages;
use superbot\App\Controllers\MessageController;
use superbot\Telegram\Client;
use superbot\App\Config\GeneralConfigs as cfg;

class PostController extends MessageController {
    public function poster() {
        if(isset($this->message->photo)) {
            $photo_file_id = $this->message->photo[count($this->message->photo) - 1]->file_id;
            $photo = "https://api.telegram.org/file/bot".cfg::get("bot_token")."/".Client::getFile($photo_file_id)->result->file_path;
            file_get_contents("https://myanimetv.org/bots/myanimetv/photoshop/?img=$photo&anime=1&name=$photo_file_id");
            $anime = $this->conn->wquery("INSERT INTO anime SET poster = ?", $photo_file_id);
            $this->user->page("Post:name|$anime");
            $this->message->reply("*Inviami il nome dell'anime:\n\nN.B.* _Per aggiungere titoli alternativi usa questa sintassi \"TITOLO-PRINCIPALE + NOME-ALT1, NOME-ALT2\"_");
        }
    }

    public function name($id) {
        $e = $this->message->split("+", 2);
        $name = trim($e[0]);
        $synonyms = trim($e[1]);
        $this->conn->wquery("UPDATE anime SET name = ?, synonyms = ? WHERE id = ?", $name, $synonyms, $id);
        $this->user->page("Post:season|$id");
        $this->message->reply("*Ok invia il numero della stagione:\n\nN.B* _Inviare 0 se l'anime è singolo (Senza altre stagioni)_");
    }

    public function season($id) {
        if(is_numeric($this->message->text)) {
            $this->conn->wquery("UPDATE anime SET season = ? WHERE id = ?", (int)$this->message->text, $id);
            $menu[] = [["text" => "APRI ANIME", "callback_data" => "Settings:home|$id"]];
            $m = $this->message->reply("Ok, invia il link di anime world:", $menu);
            $this->user->page("Settings:reloadInfo|$id|{$m->result->message_id}");
        }
    }
}
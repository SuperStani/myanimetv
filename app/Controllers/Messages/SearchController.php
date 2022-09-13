<?php
namespace superbot\App\Controllers\Messages;
use superbot\App\Controllers\MessageController;
use superbot\Telegram\Client;

class SearchController extends MessageController {
    public function q($message_id = null) {
        if($message_id != null)
            Client::deleteMessage($this->user->id, $message_id);
        $this->message->delete();
        $results = $this->conn->rquery("SELECT COUNT(*) AS tot FROM anime WHERE name LIKE ? OR synonyms LIKE ?", '%'.$this->message->text.'%', '%'.$this->message->text.'%');
        if($results->tot){
            $menu[] = [["text" => get_button('it', 'search_results'), "web_app" => ["url" => "https://webapp.myanimetv.org/search/q:".urlencode($this->message->text)]]];
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0"]];
            $this->message->reply(get_string('it', 'search_results', $this->message->text, $results->tot), $menu);
        }else{
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0"]];
            $this->message->reply(get_string('it', 'result_not_found'), $menu);
        }
    }

    public function groupForAnime($id, $message_id) {
        $this->message->delete();
        $groups = $this->conn->rqueryAll("SELECT id, name FROM groups_list WHERE name LIKE ? LIMIT 10", "%{$this->message->text}%");
        foreach($groups as $group) {
            $menu[] = [["text" => $group->name, "callback_data" => "Settings:addInGroup|$id|$group->id"]];
        }
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:group|$id"]];
        $keyboard["inline_keyboard"] = $menu;
        Client::editMessageText($this->user->id, $message_id, null, "Seleziona il gruppo", "html", null, false, $keyboard);
    }
}
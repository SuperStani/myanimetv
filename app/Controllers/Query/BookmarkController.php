<?php

namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\App\Config\GeneralConfigs as cfg;
use superbot\Database\DB;
use superbot\Telegram\Client;

class BookmarkController extends QueryController {
    public function home($id) {
        $this->query->alert();
        $menu[] = $this->query->message->keyboard[0];
        for($i = 0; $i < count($this->query->message->keyboard[1]) - 1; $i++) {
            $menu[1][] = $this->query->message->keyboard[1][$i];
        }
        $menu[1][] = ["text" => "📌", "callback_data" => "Bookmark:close|$id"];
        $menu[] = [["text" => "🔵 COMPLETED", "callback_data" => "Bookmark:complete|$id"]];
        $menu[] = [["text" => "⚪️ PLAN-TO-WATCH", "callback_data" => "Bookmark:plantowatch|$id"]];
        $menu[] = [["text" => "🟢 WATCHING", "callback_data" => "Bookmark:watching|$id"]];
        for($i = 2; $i < count($this->query->message->keyboard); $i++) {
            $menu[] = $this->query->message->keyboard[$i];
        }
        $this->query->editButton($menu);
    }

    public function close($id) {
        $this->query->alert();
        $menu[] = $this->query->message->keyboard[0];
        for($i = 0; $i < count($this->query->message->keyboard[1]) - 1; $i++) {
            $menu[1][] = $this->query->message->keyboard[1][$i];
        }
        $menu[1][] = ["text" => "📌", "callback_data" => "Bookmark:home|$id"];
        for($i = 5; $i < count($this->query->message->keyboard); $i++) {
            $menu[] = $this->query->message->keyboard[$i];
        }
        return $this->query->editButton($menu);
    }

    public function complete($id) {
        return $this->watchList($id, 1, "🔵 COMPLETED-LIST");
    }

    public function plantowatch($id) {
        return $this->watchList($id, 3, "⚪️ PLAN-TO-WATCH-LIST");
    }

    public function watching($id) {
        return $this->watchList($id, 2, "🟢 WATCHING-LIST");
    }

    public function watchList($id, $list, $text) {
        $check = $this->conn->rquery("SELECT list FROM anime_watchlists WHERE anime = ? AND user = ?", $id, $this->user->id)->list;
        $anime = $this->conn->rquery("SELECT name, season FROM anime WHERE id = ?", $id);
        $name = $anime->name." ".(($anime->season > 0) ? "S$anime->season" : "");
        if(isset($check)) {
            if($list == $check) {
                $this->conn->wquery("DELETE FROM anime_watchlists WHERE anime = ? AND user = ?", $id, $this->user->id);
                $this->query->alert(get_string('it', 'watchlist_remove', $name, $text), true);
            } else {
                $this->conn->wquery("UPDATE anime_watchlists SET list = ? WHERE anime = ? AND user = ?", $list, $id, $this->user->id);
                $this->query->alert(get_string('it', 'watchlist_edit', $name, $text), true);
            }
        } else {
            $this->conn->wquery("INSERT INTO anime_watchlists SET list = ?, anime = ?, user = ?", $list, $id, $this->user->id);
            $this->query->alert(get_string('it', 'watchlist_add', $name, $text), true);
        }
        return $this->close($id);
    }
}
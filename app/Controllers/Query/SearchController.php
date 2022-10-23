<?php

namespace superbot\App\Controllers\Query;

use superbot\App\Controllers\QueryController;
use superbot\App\Configs\GeneralConfigs as cfg;

use superbot\Telegram\Client;

class SearchController extends QueryController
{
    public function home($delete_message, $options = 0)
    {
        if ($options) {
            $webapp = cfg::get("webapp") . "search";
            $menu[] = [["text" => get_button('it', 'advanced_search_on'), "callback_data" => "Search:home|0"]];
            $menu[] = [["text" => get_button("it", "history"), "web_app" => ["url" => "$webapp/history/{$this->user->id}"]], ["text" => get_button("it", "category"), "callback_data" => "Search:byCategory"]];
            $menu[] = [["text" => get_button("it", "genres_search"), "callback_data" => "Search:byGenres"], ["text" => get_button("it", "a-z-list"), "callback_data" => "Search:byList"]];
            $menu[] = [["text" => get_button("it", "ep_search"), "callback_data" => "Search:byEpisodesNumber"], ["text" => get_button("it", "random"), "callback_data" => "Search:random|1"]];
            $menu[] = [["text" => get_button("it", "year_search"), "callback_data" => "Search:byYear|0"], ["text" => get_button("it", "studio_search"), "callback_data" => "Search:byStudio|0"]];
            $menu[] = [["text" => get_button("it", "back"), "callback_data" => "Home:start"]];
        } else {
            $menu[] = [["text" => get_button('it', 'advanced_search_off'), "callback_data" => "Search:home|0|1"]];
            $menu[] = [["text" => get_button("it", "back"), "callback_data" => "Home:start"]];
        }
        $text = get_string('it', 'search_home');
        if ($delete_message) {
            $this->query->message->delete();
            $m = $this->query->message->reply($text, $menu);
        } else {
            $this->query->alert();
            $m = $this->query->message->edit($text, $menu);
        }
        $this->user->page("Search:q|{$m->result->message_id}");
    }

    public function random($delete_message = 0)
    {
        $this->query->alert();
        $query = "
            SELECT 
                anime.id, 
                anime.name, 
                anime.poster,
                anime.synopsis_url
            FROM anime 
            WHERE season < 2 
            ORDER by RAND() LIMIT 1
        ";
        $anime = $this->conn->rquery($query);
        $g = $this->conn->rqueryAll("SELECT g.name FROM genres g INNER JOIN anime_genres ag ON g.id = ag.genre WHERE ag.anime = ?", $anime->id);
        $genres = "#" . implode(', #', array_column($g, 'name'));
        $menu[] = [["text" => get_button('it', 'watch_now'), "callback_data" => "Anime:view|$anime->id"]];
        $menu[] = [["text" => get_button('it', 'new_random'), "callback_data" => "Search:random"]];
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|1"]];
        $text = get_string('it', 'random_search', $anime->name, $genres, $anime->synopsis_url);
        if ($delete_message) {
            $this->query->message->delete();
            return $this->query->message->reply_photo(cfg::get('domain') . 'resources/img/' . $anime->poster . '.jpg', $text, $menu);
        } else {
            $this->query->alert();
            return $this->query->message->edit_media(cfg::get('domain') . 'resources/img/' . $anime->poster . '.jpg', $text, $menu);
        }
    }

    public function byStudio($index)
    {
        $webapp = cfg::get("webapp") . "studio";
        $next_index = $index + 18;
        $prev_index = $index - 18;
        $query = "
            SELECT 
                studios.id,
                studios.name,
                COUNT(anime_studios.anime) AS tot
            FROM studios
            LEFT JOIN anime_studios
            ON studios.id = anime_studios.studio
            GROUP by studios.id
            HAVING COUNT(anime_studios.anime) > 0
            ORDER by tot DESC, studios.name
            LIMIT ?, 19
        ";
        $studios = $this->conn->rqueryAll($query, $index);
        $x = 0;
        $y = 0;
        foreach ($studios as $key => $studio) {
            if ($key < 18) {
                if ($x < 2)
                    $x++;
                else {
                    $x = 1;
                    $y++;
                }
                $menu[$y][] = ["text" => "$studio->name", "web_app" => ["url" => "$webapp/$studio->id/$studio->name"]];
            }
        }
        if (count($studios) == 19) {
            if ($index == 0) {
                $menu[] = [["text" => "»»»", "callback_data" => "Search:byStudio|$next_index"]];
            } else {
                $menu[] = [["text" => "«««", "callback_data" => "Search:byStudio|$prev_index"], ["text" => "»»»", "callback_data" => "Search:byStudio|$next_index"]];
            }
        } else {
            if ($index > 0) {
                $menu[] = [["text" => "«««", "callback_data" => "Search:byStudio|$prev_index"]];
            }
        }
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0|1"]];
        $this->query->alert();
        return $this->query->message->edit(get_string('it', 'studio_search'), $menu);
    }

    public function byList()
    {
        $webapp = cfg::get("webapp") . "search/index";
        $menu[] = [["text" => "A", "web_app" => ["url" => "$webapp/a"]], ["text" => "B", "web_app" => ["url" => "$webapp/b"]], ["text" => "C", "web_app" => ["url" => "$webapp/c"]], ["text" => "D", "web_app" => ["url" => "$webapp/d"]]];
        $menu[] = [["text" => "E", "web_app" => ["url" => "$webapp/e"]], ["text" => "F", "web_app" => ["url" => "$webapp/f"]], ["text" => "G", "web_app" => ["url" => "$webapp/g"]], ["text" => "H", "web_app" => ["url" => "$webapp/h"]]];
        $menu[] = [["text" => "I", "web_app" => ["url" => "$webapp/i"]], ["text" => "J", "web_app" => ["url" => "$webapp/j"]], ["text" => "K", "web_app" => ["url" => "$webapp/k"]], ["text" => "L", "web_app" => ["url" => "$webapp/l"]]];
        $menu[] = [["text" => "M", "web_app" => ["url" => "$webapp/m"]], ["text" => "N", "web_app" => ["url" => "$webapp/n"]], ["text" => "O", "web_app" => ["url" => "$webapp/o"]], ["text" => "P", "web_app" => ["url" => "$webapp/p"]]];
        $menu[] = [["text" => "Q", "web_app" => ["url" => "$webapp/q"]], ["text" => "R", "web_app" => ["url" => "$webapp/r"]], ["text" => "S", "web_app" => ["url" => "$webapp/s"]], ["text" => "T", "web_app" => ["url" => "$webapp/t"]]];
        $menu[] = [["text" => "U", "web_app" => ["url" => "$webapp/u"]], ["text" => "V", "web_app" => ["url" => "$webapp/v"]], ["text" => "W", "web_app" => ["url" => "$webapp/w"]], ["text" => "X", "web_app" => ["url" => "$webapp/x"]]];
        $menu[] = [["text" => "Y", "web_app" => ["url" => "$webapp/y"]], ["text" => "Z", "web_app" => ["url" => "$webapp/z"]], ["text" => "#", "web_app" => ["url" => "$webapp/special"]]];
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0|1"]];
        $this->query->alert();
        return $this->query->message->edit("[⤵️](#) *Seleziona un indice qua sotto ⤵️*", $menu);
    }

    public function byYear($index)
    {
        $webapp = cfg::get("webapp") . "search/year";
        $next_index = $index + 10;
        $prev_index = $index - 10;
        $actual_year = date("Y");
        $year = $actual_year - $index;
        $x = 0;
        $y = 0;
        for ($i = $year; $i > $year - 12; $i--) {
            if ($x  < 3) {
                $x++;
            } else {
                $y++;
                $x = 1;
            }
            $menu[$y][] = ["text" => $i, "web_app" => ["url" => "$webapp/$i"]];
        }
        if ($actual_year == $year && $year > 1980) {
            $menu[] = [["text" => "»»»", "callback_data" => "Search:byYear|$next_index"]];
        } elseif ($year > 1991) {
            $menu[] = [["text" => "«««", "callback_data" => "Search:byYear|$prev_index"], ["text" => "»»»", "callback_data" => "Search:byYear|$next_index"]];
        } else {
            $menu[] = [["text" => "«««", "callback_data" => "Search:byYear|$prev_index"]];
        }
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0|1"]];
        $this->query->alert();
        return $this->query->message->edit("*Seleziona l'anno per la ricerca*", $menu);
    }

    public function byCategory()
    {
        $webapp = cfg::get("webapp") . "search/category";
        $categories = $this->conn->rqueryAll("SELECT name FROM categories");
        $x = 0;
        $y = 0;
        foreach ($categories as $c) {
            if ($x < 2)
                $x++;
            else {
                $y++;
                $x = 1;
            }
            $menu[$y][] = ["text" => $c->name, "web_app" => ["url" => "$webapp/$c->name"]];
        }
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0|1"]];
        return $this->query->message->edit("*Seleziona la categoria che desideri per la ricerca*", $menu);
    }

    public function byEpisodesNumber()
    {
        $webapp = cfg::get("webapp") . "search/episodes";
        $menu[] = [["text" => "1~12ep", "web_app" => ["url" => "$webapp/1-12"]], ["text" => "13~26ep", "web_app" => ["url" => "$webapp/13-26"]]];
        $menu[] = [["text" => "27~60ep", "web_app" => ["url" => "$webapp/27-63"]], ["text" => "64~120ep", "web_app" => ["url" => "$webapp/64-102"]]];
        $menu[] = [["text" => "121~300ep", "web_app" => ["url" => "$webapp/121-300"]], ["text" => "+300ep", "web_app" => ["url" => "$webapp/301-1500"]]];
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0|1"]];
        return $this->query->message->edit("*Seleziona il numero di episodi per la ricerca*", $menu);
    }

    public function byGenres($genre = null, $search_id = null)
    {
        if (!$search_id)
            $search_id = $this->conn->wquery("INSERT INTO anime_searchs SET user = ?", $this->user->id);

        if ($genre) {
            $check = isset($this->conn->rquery("SELECT genre FROM search_genres WHERE genre = ? AND s_id = ?", $genre, $search_id)->genre);
            if ($check)
                $this->conn->wquery("DELETE FROM search_genres WHERE genre = ? AND s_id = ?", $genre, $search_id);
            else
                $this->conn->wquery("INSERT INTO search_genres SET genre = ?, s_id = ?", $genre, $search_id);
        }
        $q = $this->conn->rqueryAll("SELECT id, name FROM genres");
        $x = 0;
        $y = 0;
        foreach ($q as $g) {
            $check = isset($this->conn->rquery("SELECT genre FROM search_genres WHERE genre = ? AND s_id = ?", $g->id, $search_id)->genre);
            if ($x < 2) {
                $x++;
            } else {
                $x = 1;
                $y++;
            }
            $menu[$y][] = ["text" => "$g->name " . (($check) ? '🔵' : '🔴'), "callback_data" => "Search:byGenres|$g->id|$search_id"];
        }
        if ($genre) {
            $webapp = cfg::get("webapp") . "search/genres/$search_id";
            $menu[] = [["text" => get_button('it', 'search_results'), "web_app" => ["url" => $webapp]]];
        }
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0|1"]];
        $this->query->alert();
        return $this->query->message->edit("Seleziona i generi:", $menu);
    }
}

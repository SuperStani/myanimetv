<?php

namespace superbot\App\Controllers\Query;

use superbot\App\Controllers\QueryController;
use superbot\App\Configs\GeneralConfigs as cfg;
use superbot\Telegram\Client;

class MovieController extends QueryController
{

    public function view($id, $delete_message = 0)
    {
        $query = "
            SELECT 
                anime.*,
                a1.average,
                a1.toprank,
                a1.amount,
                COUNT(aw.anime) AS total_views
            FROM (
                SELECT 
                    *,
                    RANK() OVER(ORDER By bayesan DESC) AS toprank
                FROM (
                    SELECT 
                        a.id,
                        COUNT(vote) AS amount, 
                        ROUND(AVG(vote), 1) + 0.0 AS average,
                        -- (WR) = (v ÷ (v+m)) × R + (m ÷ (v+m)) × C
                        --    * R = average for the movie (mean) = (Rating)
                        --    * v = number of anime_votes for the movie = (anime_votes)
                        --    * m = minimum anime_votes required to be listed in the Top 150 (currently 1300)
                        --    * C = the mean vote across the whole report (currently 6.8)
                        (  (COUNT(vote) + 0.0 / (COUNT(vote)+0)) * AVG(vote) + 0.0 + (0 + 0.0 / (COUNT(vote)+0)) * (6.8) ) AS bayesan
                    FROM anime AS a
                    LEFT OUTER JOIN anime_votes AS v
                    ON a.id = v.anime
                    GROUP BY a.id
                    HAVING 
                        COUNT(vote) >= 0
                ) AS aub
            ) AS a1
            INNER JOIN anime ON anime.id = a1.id
            LEFT OUTER JOIN anime_views aw ON a1.id = aw.anime
            LEFT OUTER JOIN anime_simulcasts s ON a1.id = s.anime
            WHERE a1.id = ?;
        ";
        $anime = $this->conn->rquery($query, $id);
        if (isset($anime->id)) {
            $webapp = cfg::get("webapp") . "anime";

            $this->conn->wquery("INSERT INTO anime_history SET user = ?, anime = ?", $this->user->id, $id);

            $g = $this->conn->rqueryAll("SELECT g.name FROM genres g INNER JOIN anime_genres ag ON g.id = ag.genre WHERE ag.anime = ?", $id);
            $genres = "#" . implode(', #', array_column($g, 'name'));
            $studios = $this->conn->rqueryAll("SELECT s.name FROM studios s INNER JOIN anime_studios ast ON s.id = ast.studio WHERE ast.anime = ?", $id);
            $studio_text = "";
            if (count($studios))
                foreach ($studios as $studio)
                    $studio_text = $studio_text . "[$studio->name](t.me/myanimetvbetabot?start=studio_$studio->id) ";

            $uploaded_episodes = $this->conn->rquery("SELECT COUNT(*) AS tot FROM episodes WHERE anime = ?", $id)->tot;
            if ($uploaded_episodes > 0) {
                if ($uploaded_episodes == $anime->episodes) {
                    $status = "Concluso";
                    $episodes = $anime->episodes;
                } else {
                    $status = "In Corso";
                    $episodes = $uploaded_episodes . '/' . str_replace("0", "??", $anime->episodes);
                }
            } else {
                $status = "Non rilasciato";
                $episodes = str_replace("0", "??", $anime->episodes);
            }

            if ($anime->season > 0)
                $season = "➥ _" . ["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"][$anime->season - 1] . " stagione_\n";
            else
                $season = "";
            $menu[] = [["text" => "1 ⭐️", "callback_data" => "Anime:vote|$id|1"], ["text" => "2 ⭐️", "callback_data" => "Anime:vote|$id|2"], ["text" => "3 ⭐️", "callback_data" => "Anime:vote|$id|3"], ["text" => "4 ⭐️", "callback_data" => "Anime:vote|$id|4"], ["text" => "5 ⭐️", "callback_data" => "Anime:vote|$id|5"]];
            $isSimulcast = isset($this->conn->rquery("SELECT anime FROM anime_simulcasts WHERE anime = ?", $id)->anime);
            $isPreferred = isset($this->conn->rquery("SELECT anime FROM anime_preferreds WHERE user = ? AND anime = ?", $this->user->id, $id)->anime);
            $menu[] = [
                ["text" => ($isPreferred) ? '❤️' : '💔', "callback_data" => "Anime:love|$id|" . (($isPreferred) ? '0' : '1')],
                ["text" => "📌", "callback_data" => "Bookmark:home|$id"]
            ];
            if ($this->user->isAdmin())
                $menu[1][] = ["text" => "⚙️", "callback_data" => "Settings:home|$id|1"];
            if ($isSimulcast) {
                $notifyActive = isset($this->conn->rquery("SELECT anime FROM anime_simulcast_notify WHERE anime = ? AND user = ?", $id, $this->user->id)->anime);
                array_unshift($menu[1], ["text" => ($notifyActive) ? '🔔' : '🔕', "callback_data"  => "Anime:alert|$id|" . (($notifyActive) ? '0' : '1')]);
            }
            if ($anime->category == "Movie") {
                $menu[] = [["text" => "▶️ GUARDA ORA ", "callback_data" => "Player:play|$id|1|1"]];
            } else {
                $haveView = $this->conn->rquery("SELECT episode FROM anime_views WHERE user = ? AND anime = ? AND viewed_on = (SELECT MAX(viewed_on) FROM anime_views WHERE user = ? AND anime = ?)", $this->user->id, $id, $this->user->id, $id)->episode;
                if (isset($haveView))
                    $menu[] = [["text" => "⏯ RIPRENDI", "callback_data" => "Player:play|$id|$haveView|1|1"], ["text" => "💽 EPISODI", "callback_data" => "Anime:showEpisodes|$id|0"]];
                else
                    $menu[] = [["text" => "💽 EPISODI", "callback_data" => "Anime:showEpisodes|$id|0"]];
            }
            $hasGroup = $this->conn->rquery("SELECT group_id FROM anime_groups WHERE anime = ?", $id)->group_id;
            if (isset($hasGroup))
                $menu[] = [["text" => get_button('it', 'correlated'), "web_app" => ["url" => "$webapp/$id/correlated"]], ["text" => get_button('it', 'similar1'), "web_app" => ["url" => "$webapp/$id/similar"]]];
            else
                $menu[] = [["text" => get_button('it', 'similar'), "web_app" => ["url" => "$webapp/$id/similar"]]];
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|1"]];
            $aired = explode("-", $anime->aired_on);
            $aired_on = $aired[2] . " " . ["", "Gennaio", "Febbraio", "Marzo", "Maggio", "Aprile", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"][(int)$aired[1]] . " " . $aired[0];
            $text = get_string('it', 'anime', $anime->name, $season, $anime->toprank, $status, $aired_on, $anime->category, $studio_text, $episodes, $anime->duration, $anime->synopsis_url, $anime->trailer, $genres, $anime->average, $anime->amount, $anime->total_views);
            if ($delete_message != 0) {
                if ($delete_message)
                    $this->query->message->delete();
                return $this->query->message->reply_photo(cfg::$domain . 'resources/img/' . $anime->poster . '.jpg', $text, $menu);
            } else {
                return $this->query->message->edit_media(cfg::$domain . 'resources/img/' . $anime->poster . '.jpg', $text, $menu);
            }
        } else {
            $this->query->message->reply("Anime inesistente");
        }
    }

    public function vote($id, $vote)
    {
        $this->conn->wquery("DELETE FROM anime_votes WHERE user = ? AND anime = ?", $this->user->id, $id);
        $this->conn->wquery("INSERT INTO anime_votes SET user = ?, anime = ?, vote = ?", $this->user->id, $id, $vote);
        $this->query->alert("Hai votato $vote ⭐️!", true);
        $this->view($id);
    }

    public function love($id, $vote)
    {
        if ($vote == 0) {
            $this->conn->wquery("DELETE FROM anime_preferreds WHERE user = ? AND anime = ?", $this->user->id, $id);
            $this->query->alert("💔");
        } else {
            $this->conn->wquery("INSERT INTO anime_preferreds SET user = ?, anime = ?", $this->user->id, $id);
            $this->query->alert("❤️");
        }
        $this->view($id);
    }

    public function alert($id, $type)
    {
        if ($type == 0) {
            $this->conn->wquery("DELETE FROM anime_simulcast_notify WHERE user = ? AND anime = ?", $this->user->id, $id);
            $this->query->alert("🔕");
        } else {
            $this->conn->wquery("INSERT INTO anime_simulcast_notify SET user = ?, anime = ?", $this->user->id, $id);
            $this->query->alert("Ti invierò una notifica ogni volta che uscirà un nuovo episodio di questo simulcast!", true);
        }
        $this->view($id);
    }


    public function showEpisodes($id, $delete_message)
    {
        $this->query->alert();
        $episodes = $this->conn->rqueryAll("SELECT episodeNumber FROM episodes WHERE anime = ? AND episodetype = 1 ORDER by episodeNumber", $id);
        $tot_ep = count($episodes);
        if ($tot_ep > 0) {
            $x = 0;
            $y = 0;
            if ($tot_ep > 50) {
                $end = 0;
                while ($end < $tot_ep) { //Ragruppamento episodi 1-50, 51-101 ecc...
                    $list_index = $end;
                    $start = $end + 1;
                    $end += 50;
                    if ($end <= $tot_ep)
                        $button =  ["text" => "$start-$end", "callback_data" => "Anime:showEpisodesList|$id|$list_index|$delete_message"];
                    else
                        $button =  ["text" => "$start-" . $tot_ep, "callback_data" => "Anime:showEpisodesList|$id|$list_index|$delete_message"];

                    if ($x < 4)
                        $x++;
                    else {
                        $y++;
                        $x = 1;
                    }
                    $menu[$y][] = $button;
                }
            } else { //Ragruppamento normale 1, 2, 3... in caso che abbia meno di 51 ep
                foreach ($episodes as $key => $episode) {
                    if ($key < 9) {
                        $ep = $key + 1;
                        $ep = "0$ep";
                    } else
                        $ep = $key + 1;
                    $button = ["text" => $ep, "callback_data" => "Player:play|$id|$episode->episodeNumber|1"];
                    if ($x < 4)
                        $x++;
                    else {
                        $y++;
                        $x = 1;
                    }
                    $menu[$y][] = $button;
                }
                /*if($tot_ep < 26 && $tot_ep > 1){
                    $menu[] = [["text" => "🔀 INVIA TUTTI", "callback_data" => "player:multiple_$id"]];
                }*/
            }
            /*$specials = $bot->conn->query("SELECT COUNT(fileID) as TOT FROM episodes WHERE anime_id = '$id' AND tipo = 0")->fetch()["TOT"];
            if($specials){
                $menu[] = [["text" => "✨ EPISODI SPECIALI", "callback_data" => "episodes:specials_$id"]];
            }*/
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Anime:view|$id|$delete_message"]];
            if (!$delete_message) {
                $photo = $this->query->message->photo;
                $this->query->message->edit_media($photo[array_key_last($photo)]->file_id, "*Seleziona il numero dell'episodio ⤵️*", $menu);
            } else {
                $this->query->message->delete();
                $this->query->message->reply("*Seleziona il numero dell'episodio ⤵️*", $menu);
            }
        } else {
            $this->query->alert("Al momento gli episodi di questo anime non sono disponibili!", true);
        }
    }

    public function showEpisodesList($id, $index, $delete_message)
    {
        $this->query->alert();
        $next_index = $index + 50;
        $prev_index = $index - 50;
        $episodes = $this->conn->rqueryAll("SELECT episodeNumber FROM episodes WHERE anime = ? AND episodetype = 1 ORDER by episodeNumber LIMIT $index, 51", $id);
        $x = 0;
        $y = 0;
        foreach ($episodes as $key => $episode) {
            if ($key < 50) {
                $key += $index;
                if ($key < 9) {
                    $ep = $key + 1;
                    $ep = "0$ep";
                } else
                    $ep = $key + 1;

                $button =  ["text" => $ep, "callback_data" => "Player:play|$id|$episode->episodeNumber|1"];
                if ($x < 5)
                    $x++;
                else {
                    $y++;
                    $x = 1;
                }
                $menu[$y][] = $button;
            }
        }
        if (count($episodes) == 51) {
            if ($index == 0)
                $menu[] = [["text" => "ᐅ ᐅ ᐅ", "callback_data" => "Anime:showEpisodesList|$id|$next_index|$delete_message"]];
            else
                $menu[] = [["text" => "ᐊ ᐊ ᐊ", "callback_data" => "Anime:showEpisodesList|$id|$prev_index|$delete_message"], ["text" => "ᐅ ᐅ ᐅ", "callback_data" => "Anime:showEpisodesList|$id|$next_index|$delete_message"]];
        } else
            if ($id > 0)
            $menu[] = [["text" => "ᐊ ᐊ ᐊ", "callback_data" => "Anime:showEpisodesList|$id|$prev_index|$delete_message"]];

        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "Anime:showEpisodes|$id|$delete_message"]];
        if (!$delete_message) {
            $photo = $this->query->message->photo;
            $this->query->message->edit_media($photo[array_key_last($photo)]->file_id, "*Seleziona il numero dell'episodio ⤵️*", $menu);
        } else {
            $this->query->message->delete();
            $this->query->message->reply("*Seleziona il numero dell'episodio ⤵️*", $menu);
        }
    }
}

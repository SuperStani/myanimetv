<?php

namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\Database\DB;
use superbot\Telegram\Client;

class LeadershipController extends QueryController {
    public function home($delete = 0) {
        $menu[] = [["text" => "CLASSIFICA VOTI", "callback_data" => "Leadership:byVotes|0"]];
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Home:start"]];
        if($delete){
            $this->query->message->delete();
            $this->query->message->reply("Classifica", $menu);
        }else
        $this->query->message->edit("Classifica", $menu);
        
    }


    public function byVotes($index) {
        $this->query->alert();
        $prev_index = $index - 10; $next_index = $index + 10;
        $query = '
            SELECT 
                *,
                RANK() OVER(ORDER By bayesan DESC) AS toprank
            FROM (
                SELECT 
                    v.anime,
                    a.name, 
                    a.season,
                    COUNT(vote) AS amount, 
                    ROUND(AVG(vote), 1) + 0.0 AS average,
                    -- (WR) = (v ÷ (v+m)) × R + (m ÷ (v+m)) × C
                    --    * R = average for the movie (mean) = (Rating)
                    --    * v = number of anime_votes for the movie = (anime_votes)
                    --    * m = minimum anime_votes required to be listed in the Top 150 (currently 1300)
                    --    * C = the mean vote across the whole report (currently 6.8)
                    (  (COUNT(vote) + 0.0 / (COUNT(vote)+19)) * AVG(vote) + 0.0 + (19 + 0.0 / (COUNT(vote)+19)) * (6.8) ) AS bayesan
                FROM anime_votes AS v
                LEFT OUTER JOIN anime AS a
                ON a.id = v.anime
                GROUP BY v.anime
                HAVING 
                    COUNT(vote) >= 19
            ) AS aub LIMIT ?, 11
        ';
        $anime = $this->conn->rqueryAll($query, $index);
        $text = "";
        foreach($anime as $key => $a) {
            if($key < 10)
                $text = $text."*#$a->toprank*\n ➥ [$a->name ".["S1", "S2", "S3", "S4", "S5", "S6", "S7", "S8", "S9", "S10"][$a->season - 1]."](t.me/myanimetvbot?start=animeID_$a->anime) *{$a->average}⭐️ ~ $a->amount voti*\n\n";
        }
        if(count($anime) == 11){
            if($index == 0)
                $menu[] = [["text" => "ᐅ ᐅ ᐅ", "callback_data" => "Leadership:byVotes|$next_index"]];
            else
                $menu[] = [["text" => "ᐊ ᐊ ᐊ", "callback_data" => "Leadership:byVotes|$prev_index"],["text" => "ᐅ ᐅ ᐅ", "callback_data" => "Leadership:byVotes|$next_index"]];
            
        }else
            if($index > 0)
                $menu[] = [["text" => "ᐊ ᐊ ᐊ", "callback_data" => "Leadership:byVotes|$prev_index"]];
            
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "Leadership:home|0"]];
        $this->query->message->edit($text, $menu, "Markdown", true);
    }
}
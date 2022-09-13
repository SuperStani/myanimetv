<?php
namespace superbot\App\Controllers\Query;
use superbot\App\Controllers\QueryController;
use superbot\App\Config\GeneralConfigs as cfg;
use superbot\Database\DB;
use superbot\Telegram\Client;

class PlayerController extends QueryController {

    public function play($anime, $episode, $delete) {
        $channels = $this->conn->rqueryAll("SELECT chat_id, invite_url FROM channels");
        $isFollower = true;
        $x = 0; $y = 0;
        /*$channels_menu = [];
        foreach($channels as $key => $channel){
            $cont = $key + 1;
            if(!Client::isfollower($channel->chat_id, $this->user->id)){
                $isFollower = false;
                $text = "❌ CANALE $cont ❌";
            }else
                $text = "✅ CANALE $cont ✅";
            
            if($x < 1) { $x++; } else { $y++; $x = 1; }
            $channels_menu[$y][] = ["text" => $text, "url" => $channel->invite_url];
        } */
        if($isFollower) {
            //Save view
            $this->conn->wquery("INSERT INTO anime_views SET user = ?, anime = ?, episode = ?", $this->user->id, $anime, $episode);

            $info = $this->conn->rqueryAll("SELECT a.name, a.season, a.audio, e.title, e.id, e.episodeNumber, e.video_id, ag.group_id, ag.viewOrder FROM anime a JOIN episodes e ON a.id = e.anime LEFT JOIN anime_groups ag ON a.id = ag.anime WHERE a.id = ? AND e.episodeNumber BETWEEN ? AND ? ORDER By e.episodeNumber ASC", $anime, $episode, $episode + 1);
            $animeInfo = $info[0];
            
            if($this->user->isAdmin())
                $menu[] = [["text" => "🔧 MODIFICA", "callback_data" => "episodes:edit_".$animeInfo->id],["text" => "🖊 TITOLO", "callback_data" => "episodes:title_".$animeInfo->id], ["text" => "❌ ELIMINA EP", "callback_data" => "episodes:delete_".$animeInfo->id]];
            
            if(isset($info[1])) //There is a next episode
            {
                if($episode == 1)
                    $menu[] = [["text" => "📌 ".["🔘","⚪️"][$delete], "callback_data" => "Player:pinEpisode|$anime|$episode|$delete"],["text" => "⏭", "callback_data" => "Player:play|$anime|{$info[1]->episodeNumber}|$delete"]];
                else {
                    $prev_episode = $episode - 1;
                    $menu[] = [["text" => "⏮", "callback_data" => "Player:play|$anime|$prev_episode|$delete"],["text" => "📌 ".["🔘","⚪️"][$delete], "callback_data" => "Player:pinEpisode|$anime|$episode|$delete"], ["text" => "⏭", "callback_data" => "Player:play|$anime|{$info[1]->episodeNumber}|$delete"]];
                }
            } else {
                if(isset($animeInfo->group_id)) {
                    //$this->query->message->reply($animeInfo->audio);
                    $query = "
                        SELECT a.id, a.category
                        FROM anime a INNER JOIN anime_groups ag ON a.id = ag.anime
                        WHERE ag.group_id = ? AND a.audio = ? AND ag.viewOrder = (SELECT MIN(ag2.viewOrder) FROM anime_groups ag2 WHERE ag2.group_id = ? AND ag2.viewOrder > ?)
                    ";
                    $nextCorrelatedAnime = $this->conn->rquery($query, $animeInfo->group_id, $animeInfo->audio, $animeInfo->group_id, $animeInfo->viewOrder);
                    if(isset($nextCorrelatedAnime->id)) {
                        if($nextCorrelatedAnime->category == 'Movie') //Is a movie
                            $menu[] = [["text" => "🎬 MOVIE (SEQUEL)", "callback_data" => "Anime:view|$nextCorrelatedAnime->id"]];
                        else {
                            $season = ["PRIMA", "SECONDA", "TERZA", "QUARTA", "QUINTA", "SESTA", "Settima", "OTTAVA", "NONA", "DECIMA"][$animeInfo->season];
                            $menu[] = [["text" => $season.' STAGIONE ▶️', "callback_data" => "Anime:view|$nextCorrelatedAnime->id"]];
                        }
                    }
                }
                $prev_episode = $episode - 1;
                $menu[] = [["text" => "⏮", "callback_data" => "Player:play|$anime|$prev_episode|1"]];
            }
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Anime:showEpisodes|$anime|1"]];
            if($episode < 10) 
                $episode = "0$episode";
            if(isset($animeInfo->title))
                $episode_caption = "*Ep $episode* | $animeInfo->title";
            else
                $episode_caption = "*Episodio $episode*";
            if($animeInfo->season > 0) 
                $season = "\n".["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"][$animeInfo->season - 1]." stagione\n";
            else
                $season = "";
            $caption = "*$animeInfo->name*\n━━━━━━━━━━━━━━━_$season"."_\n🔥 $episode_caption";
            if($delete) {
                $this->query->alert();
                $this->query->message->delete();
            }
                
            return Client::copyMessage($this->user->id, cfg::get("episodesChannel"), $animeInfo->video_id, $caption, $menu, 'Markdown');
        }else{
            $img = "#";
            if($delete) {
                $this->query->alert();
                $this->query->message->delete();
            }
            $text = "[✅]($img) | *SBLOCCA L' ACCESSO AL BOT*\n\n_Per sbloccare l'accesso al bot  iscriviti ai canali qui sotto. Facendo questo supporterai il nostro progetto!_\n\n🔽 | *ISCRIVITI AI CANALI QUI SOTTO*";
            $channels_menu[] = [["text" => "🔓 SBLOCCA", "callback_data" => "Player:play|$anime|$episode|1"]];
            return $this->query->message->reply($text, $channels_menu); 
        }
    }

    public function pinEpisode($anime, $episode, $type) {
        $this->query->alert();
        $next_episode = $episode + 1; $prev_episode = $episode - 1;
        if($type == 1)
            $type = 0;
        else
            $type = 1;
        if($this->user->isAdmin())
            $index = 1;
        else
            $index = 0;
        if(count($this->query->message->keyboard[$index]) == 2)
            $menu[] = [["text" => "📌 ".["🔘","⚪️"][$type], "callback_data" => "Player:pinEpisode|$anime|$episode|$type"],["text" => "⏭", "callback_data" => "Player:play|$anime|$next_episode|$type"]];
        else 
            $menu[] = [["text" => "⏮", "callback_data" => "Player:play|$anime|$prev_episode|$type"],["text" => "📌 ".["🔘","⚪️"][$type], "callback_data" => "Player:pinEpisode|$anime|$episode|$type"], ["text" => "⏭", "callback_data" => "Player:play|$anime|$next_episode|$type"]];
        $menu[] = $this->query->message->keyboard[$index + 1];
        return $this->query->editButton($menu);
    }
    
}
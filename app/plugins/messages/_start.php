<?php
if(strpos($bot->msg, "animeID_") === 0){
    $anime_id = explode("_", $bot->msg)[1];
    $bot->page("start:anime");
    $search_string = "SELECT
                            anime_info.episodi, 
                            anime_info.durata_ep,
                            anime_info.uscita, 
                            anime_info.trama_url, 
                            anime_info.trailer,
                            categorie.tipo,
                            anime.id,
                            anime.poster, 
                            anime.nome, 
                            anime.stagione,
                            COUNT(anime_views.anime_id) AS visual
                     FROM anime_info
                     INNER JOIN anime
                     ON anime.id = anime_info.anime_id 
                     LEFT JOIN anime_views
                     ON anime_info.anime_id = anime_views.anime_id
                     INNER JOIN categorie
                     ON anime_info.categoria = categorie.id
                     WHERE anime.id = :anime";
    $q = $bot->conn->prepare($search_string);
    $q->bindParam(":anime", $anime_id);
    $q->execute();
    if($q->rowCount()){
        //...Fetch anime info
        $info = $q->fetch();
        $nome = $info["nome"];
        $uscita = $info["uscita"];
        $trama = $info["trama_url"];
        $views = $info["visual"];
        $durata = str_replace(" ", "",$info["durata_ep"]);
        $episodi = $info["episodi"];
        $poster = $info["poster"];
        $stagione = $info["stagione"];
        $trailer = $info["trailer"];
        $categoria = $info["tipo"];
        $stato = "Concluso";
        //...Genres select
        $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime");
        $generi->bindParam(":anime", $anime_id);
        $generi->execute();
        $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome')); //Implode genres

        //Studios
        $studios = $bot->conn->query("SELECT studios.name, studios.id FROM studios INNER JOIN anime_studios ON studios.id = anime_studios.studio_id WHERE anime_studios.anime_id = '$anime_id'");
        if($studios->rowCount()){ 
            $studio_text = "";
            foreach($studios as $studio){
                $studio_text = $studio_text."<a href='t.me/myanimetvbot?start=studio_".$studio["id"]."'>".$studio["name"]."</a> ";
            }
        }else{
            $studio_text = "Sconosciuto";
        }

        $episodes_upload = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1")->fetch()["tot"];
        //...Anime status
        if($episodes_upload == 0){ $stato = "Non Rilasciato"; }
        elseif($episodes_upload != $episodi) {
            if($episodi == 0){
                $episodi = "$episodes_upload/".str_replace("0", "??", $episodi);
            }else{
                $episodi = "$episodes_upload/$episodi";
            }
            $stato = "In Corso";
        }
        if($stagione > 0){
            $stagioni = ["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"];
            $stagione = str_replace([1,2,3,4,5,6,7,8,9,10], $stagioni, $stagione);
            $stagione = "➥ <i>$stagione stagione</i>\n";
        }else{
            $stagione = "";
        }
        if($trailer != ''){
            $trailer = "\n📽 | <b>Trailer:</b> <a href='$trailer'>Clicca qui</a>";
        }
        if($bot->isadmin()){ $anime = "\n🆔: <code>$anime_id</>";}
        $text = "💮 <b>$nome</b>\n━━━━━━━━━━━━━\n$stagione\n🗓 | <b>Data:</b> $uscita\n🏷 | <b>Categoria:</b> $categoria\n🏢 | <b>Studio:</b> $studio_text\n➕ | <b>Episodi:</b> $episodi\n⏱️ | <b>Durata:</b> $durata\n🏮 | <b>Stato:</b> $stato\n📖 | <b>Trama:</b> <a href='$trama'>Clicca qui</a>$trailer\n\n🌟 | <b>Generi:</b> $generi\n\n👀 | <b>Visualizzazioni:</b> $views $anime";

        //...Get like/dislike
        $like = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 1 AND anime_id = '$anime_id'")->fetch()["tot"];
        $dislike = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 0 AND anime_id = '$anime_id'")->fetch()["tot"];

        $is_simulcast = $bot->conn->query("SELECT anime_id FROM anime_simulcast WHERE anime_id = '$anime_id'");
        if($is_simulcast->rowCount()){
            $notify = $bot->conn->query("SELECT anime_id FROM simulcast_notify WHERE chat_id = '$bot->userID' AND anime_id = '$anime_id'");
            if($notify->rowCount()) { $emoji = "🔔"; }
            else { $emoji = "🔕"; }
            $menu[] = [["text" => "👍 $like", "callback_data" => "vote:like_$anime_id"],["text" => "👎 $dislike", "callback_data" => "vote:dislike_$anime_id"] ,["text" => "♥️", "callback_data" => "bookmark:preferred_$anime_id"], ["text" => "$emoji", "callback_data" => "bookmark:notify_$anime_id"]];
        }else{
            $menu[] = [["text" => "👍 $like", "callback_data" => "vote:like_$anime_id"],["text" => "👎 $dislike", "callback_data" => "vote:dislike_$anime_id"] ,["text" => "♥️", "callback_data" => "bookmark:preferred_$anime_id"]];
        }

        if($episodes_upload == 1){
            $menu[] = [["text" => "🔥 VEDI EPISODIO", "callback_data" => "player:view_0_$anime_id"."_0_1"]];
        }else{
            //...Get last episode view callback
            $last_ep = $bot->conn->prepare("SELECT episode_call FROM last_view_episode WHERE chat_id = :chat_id AND anime_id = :anime");
            $last_ep->bindParam(":chat_id", $bot->userID);
            $last_ep->bindParam(":anime", $anime_id);
            $last_ep->execute();
            if($last_ep->rowCount() < 1){
                $menu[] = [["text" => "🔥 VEDI EPISODI", "callback_data" => "episodes:group_$anime_id"."_1"]];
            }else{
                $last_ep = $last_ep->fetch()["episode_call"];
                $menu[] = [["text" => "🔥 EPISODI", "callback_data" => "episodes:group_$anime_id"."_1"],["text" => "💾 RIPRENDI", "callback_data" => "player:view_$last_ep"."_$anime_id"."_0_1"]];
            }
        }
        //...Get anime group
        $group = $bot->conn->query("SELECT group_id FROM anime_groups WHERE anime_id = '$anime_id'");
        if($group->rowCount()){
            $menu[] = [["text" => "🍥 CORRELATI", "callback_data" => "view:correlati_".$group->fetch()["group_id"]."_$anime_id"], ["text" => "💮 ANIME SIMILI", "callback_data" => "view:simili_$anime_id"]];
        }else{
            $menu[] = [["text" => "💮 ANIME SIMILI", "callback_data" => "view:simili_$anime_id"]];
        }
        $menu[] = [["text" => "📌 BOOKMARK", "callback_data" => "bookmark:option_1_$anime_id"]];
        if($bot->isadmin()){
            $menu[] = [["text" => "⚙️", "callback_data" => "setting:home_$anime_id"."_1"]];
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:scroll_0"]];
        //...SAVE THIS SEARCH INTO DATABASE
        $b = $bot->conn->prepare("INSERT INTO anime_cercati SET anime_id = :anime, by_user_id = :user");
        $b->bindParam(":anime", $anime_id);
        $b->bindParam(":user", $bot->userID);
        $b->execute();
        $result = $bot->si($bot->userID, $poster, $text, $menu);
    }else{
        $bot->reply("Questo anime non è presente dentro il database!");
    }
}

elseif(strpos($bot->msg, "ep") === 0){
    $e = explode("_", $bot->msg); //[1] => episode_index, [2] => anime_id, [3] => 1 "Delete message"; 0 "Edit messsage", [4] 1 "Sponsor message"; 0 "normal"
    $episode_number = $e[1];
    $next_episode = $episode_number + 1;
    $prev_episode = $episode_number - 1;
    $anime_id = $e[2];
    $episode = $bot->conn->prepare("SELECT id, fileID, title FROM episodes WHERE anime_id = :anime AND tipo = 1 ORDER by upload_on ASC LIMIT :episode, 1");
    $episode->bindParam(":anime", $anime_id);
    $episode->bindParam(":episode", $episode_number, PDO::PARAM_INT);
    $episode->execute();
    if($episode->rowCount()){
        $bot->page("player:view_$anime_id");
        //Views increment
        //-------------------------------------------------------------------------------------------//
        $view = $bot->conn->prepare("INSERT INTO anime_views SET chat_id = :chat_id, anime_id = :anime, episode_id = :episode");
        $view->bindParam(":chat_id", $bot->userID);
        $view->bindParam(":anime", $anime_id);
        $view->bindParam(":episode", $episode_number);
        $view->execute();
    
        //----------------Save Last Episode Viewed----------------//
        $q1 = $bot->conn->prepare("SELECT anime_id FROM last_view_episode WHERE chat_id = :chat_id AND anime_id = :anime");
        $q1->bindParam(":chat_id", $bot->userID);
        $q1->bindParam(":anime", $anime_id);
        $q1->execute();
        if($q1->rowCount() < 1){
            $q2 = $bot->conn->prepare("INSERT INTO last_view_episode SET anime_id = :anime, chat_id = :chat_id, episode_call = :ep_call");
            $q2->bindParam(":anime", $anime_id);
            $q2->bindParam(":chat_id", $bot->userID);
            $q2->bindParam(":ep_call", $episode_number);
            $q2->execute();
        }else{
            $q2 = $bot->conn->prepare("UPDATE last_view_episode SET episode_call = :ep_call, view_on = CURRENT_TIMESTAMP WHERE anime_id = :anime AND chat_id = :chat_id");
            $q2->bindParam(":anime", $anime_id);
            $q2->bindParam(":chat_id", $bot->userID);
            $q2->bindParam(":ep_call", $episode_number);
            $q2->execute();
        }
        //----------------------------------------------------------------
    
        //Insert anime in watching list
        $watching = $bot->conn->prepare("SELECT anime_id, list_id FROM bookmarks WHERE anime_id = :anime AND chat_id = :user");
        $watching->bindParam(":anime", $anime_id);
        $watching->bindParam(":user", $bot->userID);
        $watching->execute();
        $list_id = $watching->fetch();
        if($watching->rowCount()){
            if($list_id["list_id"] == 0){
                $q = $bot->conn->prepare("UPDATE bookmarks SET list_id = 2 WHERE chat_id = :chat AND anime_id = :anime AND list_id <> 2");
                $q->bindParam(":chat", $bot->userID);
                $q->bindParam(":anime", $anime_id);
                $q->execute();
            }
        }else{
            $q = $bot->conn->prepare("INSERT INTO bookmarks SET list_id = 2, chat_id = :chat, anime_id = :anime");
            $q->bindParam(":chat", $bot->userID);
            $q->bindParam(":anime", $anime_id);
            $q->execute();
        }
    
        //Select episode and check if next episode is available
        $episode = $episode->fetch();
        $episode_title = trim($episode["title"]);
        $episode_file_id = $episode["fileID"];
        $b = $bot->conn->query("SELECT fileID FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1 ORDER by upload_on LIMIT $next_episode, 1")->rowCount();
    
        $isadmin = $bot->isadmin();
        //Select anime info: Name and Season
        $anime = $bot->conn->query("SELECT anime.nome, anime.stagione, anime_info.episodi FROM anime INNER JOIN anime_info ON anime.id = anime_info.anime_id WHERE id = '$anime_id'")->fetch();
        $anime_name = $anime["nome"];
        $stagione = $anime["stagione"];
        $episodi_tot = $anime["episodi"];
        if($isadmin){
            $menu[] = [["text" => "🔧 MODIFICA", "callback_data" => "episodes:edit_".$episode["id"]],["text" => "🖊 TITOLO", "callback_data" => "episodes:title_".$episode["id"]]];
        }
        if($b > 0){
            if($episode_number == 0){
                $menu[] = [["text" => "⏭", "callback_data" => "player:view_$next_episode"."_$anime_id"."_0"]];
            }
            else{
                $menu[] = [["text" => "⏮", "callback_data" => "player:view_$prev_episode"."_$anime_id"."_0"],["text" => "⏭", "callback_data" => "player:view_$next_episode"."_$anime_id"."_0"]];
            }
        }else{
            if($episode_number > 0){
                if($isadmin){
                    $menu[0][] = ["text" => "❌ ELIMINA EP", "callback_data" => "episodes:delete_".$episode["id"]];
                }
                $q4 = $bot->conn->prepare("SELECT anime_id FROM bookmarks WHERE anime_id = :anime AND chat_id = :chat AND list_id = 1");
                $q4->bindParam(":anime", $anime_id);
                $q4->bindParam(":chat", $bot->userID);
                $q4->execute();
                if($q4->rowCount() == 0){
                    $menu[] = [["text" => "✅ COMPLETATO", "callback_data" => "bookmark:complete_$anime_id"."_1"]];
                }
                $group_id = $bot->conn->query("SELECT group_id FROM anime_groups WHERE anime_id = '$anime_id'")->fetch()["group_id"];
                if($group_id){
                    $next = $bot->conn->query("SELECT anime_info.anime_id, anime_info.categoria, anime_groups.stagione FROM anime_info INNER JOIN anime_groups ON anime_info.anime_id = anime_groups.anime_id WHERE anime_groups.group_id = '$group_id' AND anime_groups.stagione > '$stagione' ORDER by anime_groups.stagione ASC LIMIT 1");
                    if($next->rowCount()){
                        $info = $next->fetch();
                        $season = $info["stagione"];
                        $sequel_id = $info["anime_id"];
                        $categoria = $info["categoria"];
                        if($categoria == 2 ){//If is sequel movie
                            $menu[] = [["text" => "🎬 MOVIE (SEQUEL)", "callback_data" => "view:anime_$sequel_id"]];
                        }else{
                            $stagioni = ["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"];
                            $stagione2 = strtoupper($stagioni[$info["stagione"] - 1]." stagione ⏭");
                            $menu[] = [["text" => $stagione2, "callback_data" => "view:anime_$sequel_id"]];
                        }
                    }
                }
                $menu[] = [["text" => "⏮", "callback_data" => "player:view_$prev_episode"."_$anime_id"."_0"]];
            }
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "episodes:group_$anime_id"."_1"]];
    
    
        //Set caption on episode
        if($next_episode < 10){
            $next_episode = "0" . $next_episode;
        }
        if($episode_title != ""){
            $episode_caption = "<b>Ep $next_episode</b> | $episode_title";
        }else{
            $episode_caption = "<b>Episodio $next_episode</>";
        }
        if($stagione > 0){
            $stagioni = ["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"];
            $stagione = "➥ <i> ".str_replace([1,2,3,4,5,6,7,8,9,10], $stagioni, $stagione)." stagione</i>\n\n";
        }else{
            $stagione = '';
        }
        $text =  "<b>$anime_name</b>\n━━━━━━━━━━━━━━━\n$stagione"."🔥 $episode_caption";
        $bot->sendVideo($bot->userID, $episode_file_id, $text, $menu);
    }
}


elseif(strpos($bot->msg, "studio") === 0){
    $studio = explode("_", $bot->msg)[1];
    $studioName = $bot->conn->prepare("SELECT name FROM studios WHERE id = :id");
    $studioName->bindParam(":id", $studio);
    $studioName->execute();
    $studioName = $studioName->fetch()["name"];
    $q = $bot->conn->prepare("SELECT anime.id, anime.nome, anime.stagione FROM anime INNER JOIN anime_studios ON anime.id = anime_studios.anime_id WHERE anime_studios.studio_id = :studio  ORDER by anime.nome, anime.stagione LIMIT 0, 11");
    $q->bindParam(":studio", $studio);
    $q->execute();
    $text = "";
    $i = 1;
    foreach($q as $ad){
        $anime_id = $ad["id"];
        $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
        $generi->bindParam(":anime", $anime_id);
        $generi->execute();
        $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
        if($i < 11){ $text = $text." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$ad["id"]."'>".$ad["nome"]." S".$ad["stagione"]."</a></b>\n$generi\n\n"; }
        $i++;
    }
    $text = str_replace("S0", "", $text)."<b>🏢 $studioName</b>";
    if($q->rowCount() == 11){
        $menu[] = [["text" => "»»»", "callback_data" => "scroll:studio_$studio"."_10"]];
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:studio_0"]];
    $bot->reply($text, $menu);
}


elseif(strpos($bot->msg, "profile") === 0){
    $user_id = explode("_", $bot->msg)[1];
    $isSubscribed = $bot->conn->prepare("SELECT chat_id FROM utenti WHERE chat_id = :chat");
    $isSubscribed->bindParam(":chat", $user_id);
    $isSubscribed->execute();
    if($isSubscribed->rowCount()){
        $bot->page("home:home");
        //COUNT TOTAL ANIME WATCHED
        $query = "SELECT 
                    COUNT(anime.id) AS tot_anime 
                  FROM anime
                  INNER JOIN bookmarks 
                  ON anime.id = bookmarks.anime_id 
                  INNER JOIN anime_info
                  ON anime.id = anime_info.anime_id
                  WHERE bookmarks.chat_id = :user 
                  AND bookmarks.list_id = 1 
                  AND anime.stagione < 2 
                  AND anime_info.categoria <> 2";
        $q = $bot->conn->prepare($query);
        $q->bindParam(":user", $user_id);
        $q->execute();
        $anime_visti = $q->fetch()["tot_anime"];
    
        //COUNT TOTAL ANIME MOVIE WATCHED
        $query = "SELECT 
                    COUNT(anime.id) AS tot_film
                  FROM anime
                  INNER JOIN bookmarks 
                  ON anime.id = bookmarks.anime_id 
                  INNER JOIN anime_info
                  ON anime.id = anime_info.anime_id
                  WHERE bookmarks.chat_id = :user 
                  AND bookmarks.list_id = 1 
                  AND anime.stagione < 2 
                  AND anime_info.categoria = 2";
        $q = $bot->conn->prepare($query);
        $q->bindParam(":user", $user_id);
        $q->execute();
        $film_visti = $q->fetch()["tot_film"];
    
        //COUNT TOTAL EPISODES WATCHED
        $query = "SELECT 
                    COUNT(episodes.id) AS episodes 
                  FROM episodes
                  INNER JOIN bookmarks
                  ON episodes.anime_id = bookmarks.anime_id 
                  WHERE bookmarks.chat_id = :user 
                  AND bookmarks.list_id = 1";
        $q = $bot->conn->prepare($query);
        $q->bindParam(":user", $user_id);
        $q->execute();
        $episodi = $q->fetch()["episodes"];
    
        //Calc total time spend with anime
        $hour = $episodi * 24 *60;
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$hour");
        $ore = $dtF->diff($dtT)->format('%a giorni | %h h | %i min');
        $img = $bot->setting["banner"]["profilo"];
        $nome = $bot->getChat($user_id)["first_name"];
        if(!isset($nome)){ $nome = "Nome Sconsciuto";}
        $text = "<a href='$img'>&#8203;</><b>ℹ️ | Informazioni profilo</>\n\n👤 | <a href='tg://user?id=$user_id'>$nome</>\n💮 | Anime visti: <b>$anime_visti</>\n🎥 | Movie visti: <b>$film_visti</b>\n🔥 | Episodi totali: <b>$episodi</>\n🕓 | <b>".$ore."</b>\n\n🔗 | Link profilo: <b>t.me/myanimetvbot?start=profile_$user_id</b>\n\n⬇️ <b>LISTE ANIME</b> ⬇️";
        $menu[] = [["text" => "❤️ PREFERITI", "callback_data" => "mylist:preferreds_$user_id"."_0"], ["text" => "🔵 COMPLETED", "callback_data" => "mylist:completed_$user_id"."_0"]];
        $menu[] = [["text" => "🟢 WATCHING", "callback_data" => "mylist:watching_$user_id"."_0"], ["text" => "⚪️ PLAN TO WATCH", "callback_data" => "mylist:plntowatch_$user_id"."_0"]];
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
        $bot->reply($text, $menu);
    }else{
        $bot->reply("Questo utente non è iscritto al bot!");
    }
}
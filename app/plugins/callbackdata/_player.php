<?php

if($bot->data("view")){
    $e = explode("_", $bot->cbdata); //[1] => episode_index, [2] => anime_id, [3] => 1 "Delete message"; 0 "Edit messsage", [4] =- 1 "Sponsor message"; 0 "normal"
    $episode_number = $e[1];
    $next_episode = $episode_number + 1;
    $prev_episode = $episode_number - 1;
    $anime_id = $e[2];
    $delete = $e[3];
    $sponsor = $e[4];
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
    //Select anime info: Name and Season
    $anime = $bot->conn->query("SELECT anime.nome, anime.stagione, anime_info.episodi FROM anime INNER JOIN anime_info ON anime.id = anime_info.anime_id WHERE id = '$anime_id'")->fetch();
    $anime_name = $anime["nome"];
    $stagione = $anime["stagione"];
    $episodi_tot = $anime["episodi"];

    //Insert anime in watching list
    $watching = $bot->conn->prepare("SELECT anime_id, list_id FROM bookmarks WHERE anime_id = :anime AND chat_id = :user");
    $watching->bindParam(":anime", $anime_id);
    $watching->bindParam(":user", $bot->userID);
    $watching->execute();
    $list_id = $watching->fetch();
    //$bot->alert($list_id["list_id"]);
    if($watching->rowCount()){
        if($list_id["list_id"] == 3){
            $q = $bot->conn->prepare("UPDATE bookmarks SET list_id = 2 WHERE chat_id = :chat AND anime_id = :anime");
            $q->bindParam(":chat", $bot->userID);
            $q->bindParam(":anime", $anime_id);
            $q->execute();
            $bot->alert("➕ ".$anime_name." ".str_replace("S0", "", "S".$stagione)." aggiunto nella watching list", false);
        }
    }else{
        $q = $bot->conn->prepare("INSERT INTO bookmarks SET list_id = 2, chat_id = :chat, anime_id = :anime");
        $q->bindParam(":chat", $bot->userID);
        $q->bindParam(":anime", $anime_id);
        $q->execute();
        $bot->alert("➕ ".$anime_name.str_replace("S0", "", "S".$stagione)." aggiunto nella watching list", false);
    }

    //Select episode and check if next episode is available
    $episode = $bot->conn->query("SELECT id, fileID, title FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1 ORDER by upload_on ASC LIMIT $episode_number, 1")->fetch();
    $episode_title = trim($episode["title"]);
    $episode_file_id = $episode["fileID"];
    $b = $bot->conn->query("SELECT fileID FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1 ORDER by upload_on LIMIT $next_episode, 1")->rowCount();

    $isadmin = $bot->isadmin();
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
        $q4 = $bot->conn->prepare("SELECT anime_id FROM bookmarks WHERE anime_id = :anime AND chat_id = :chat AND list_id = 1");
        $q4->bindParam(":anime", $anime_id);
        $q4->bindParam(":chat", $bot->userID);
        $q4->execute();
        if($q4->rowCount() == 0){
            $menu[] = [["text" => "✅ COMPLETATO", "callback_data" => "bookmark:complete_$anime_id"."_1"]];
        }
        $group_id = $bot->conn->query("SELECT group_id FROM anime_groups WHERE anime_id = '$anime_id'")->fetch()["group_id"];
        if($group_id){
            $next = $bot->conn->query("SELECT anime_info.anime_id, anime_info.categoria, anime_groups.stagione FROM anime_info INNER JOIN anime_groups ON anime_info.anime_id = anime_groups.anime_id WHERE anime_groups.group_id = '$group_id' AND anime_groups.stagione > (SELECT stagione FROM anime_groups WHERE anime_id = '$anime_id') ORDER by anime_groups.stagione ASC LIMIT 1");
            if($next->rowCount()){
                $info = $next->fetch();
                $season = $info["stagione"];
                $sequel_id = $info["anime_id"];
                $categoria = $info["categoria"];
                if($categoria == 2 ){//If is sequel movie
                    $menu[] = [["text" => "🎬 MOVIE (SEQUEL)", "callback_data" => "view:anime_$sequel_id"]];
                }else{
                    $stagioni = ["0" => "Prima", "1" => "Seconda", "2" => "Terza", "3" => "Quarta", "4" => "Quinta", "5" => "Sesta", "6" => "Settima", "7" => "Ottava", "8" => "Nona", "9" => "Decima"];
                    $stagione2 = strtoupper($stagioni[$info["stagione"] - 1]." stagione ⏭") ?? 'CONTINUA ⏭';
                    $menu[] = [["text" => $stagione2, "callback_data" => "view:anime_$sequel_id"]];
                }
            }else{
                $specials = $bot->conn->query("SELECT COUNT(id) as tot FROM episodes WHERE anime_id = '$anime_id' AND tipo = 0")->fetch()['tot'];
                if($specials){
                    $menu[] = [["text" => "✨ EPISODI SPECIALI", "callback_data" => "episodes:specials_$anime_id"."_1"]];
                }
            }
        }else{
            $specials = $bot->conn->query("SELECT COUNT(id) as tot FROM episodes WHERE anime_id = '$anime_id' AND tipo = 0")->fetch()['tot'];
            if($specials){
                $menu[] = [["text" => "✨ EPISODI SPECIALI", "callback_data" => "episodes:specials_$anime_id"]];
            }
        }
        if($episode_number > 0){
            if($isadmin){
                $menu[0][] = ["text" => "❌ ELIMINA EP", "callback_data" => "episodes:delete_".$episode["id"]];
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
    if($delete == 0){
        $bot->editMedia($bot->msgid, $episode_file_id, "video", $text, $menu);
    }else{
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $bot->sendVideo($bot->userID, $episode_file_id, $text, $menu);
    }
    /*-----------------
    SPONSOR ADVISE
    ------------------*/
    //-------------------------------------------------------------------------------------------//
    /*if($sponsor){
        $have_voted = json_decode(file_get_contents("https://api.botsarchive.com/getUserVote.php?bot_id=760&user_id=$bot->userID"), true)["result"];
        if(!$have_voted){
                $vote[] = [["text" => "⭐️ VOTACI", "url" => "https://t.me/BotsArchive/1064"]];
                $bot->sm($bot->userID, "👋 Hey, ti andrebbe di votarci su <b>BotsArchive</>?\nCi daresti un grosso supporto morale😇\nTi lascio il link qua sotto.\n<i>P.S. appena voterai questo annuncio non apparira più</>", $vote);
        }
    }*/
}

elseif($bot->data("multiple")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $episodes = $bot->conn->query("SELECT * FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1 ORDER by upload_on ASC");
    $anime = $bot->conn->query("SELECT nome, stagione FROM anime WHERE id = '$anime_id'")->fetch();
    $anime_name = $anime["nome"];
    $stagione = $anime["stagione"];
    $i = 1;
    $x = 0; $y = 0;
    foreach($episodes as $episode){
        //Set caption on episode
        if($i < 10){ $ep_nr = "0" . $i; }
        if($episode["title"] != ""){ $episode_caption = "<b>Ep $ep_nr</b> | ".$episode["title"];
        }else{ $episode_caption = "<b>Episodio $ep_nr</b>"; }
        $text =  "<b>$anime_name S$stagione</b>\n━━━━━━━━━━━━━━━\n🔥 $episode_caption";
        $text = str_replace("S0", "", $text);
        if($x < 10){ $x++; }
        else{ $x = 1; $y++; }
        $medias[$y][] = ["type" => "video", "media" => $episode["fileID"], "caption" => $text, "parse_mode" => "html"];
        $i++;
    }
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $i = 0;
    $tot_medias = $episodes->rowCount();
    foreach($medias as $media){
        if(count($media) > 1){
            $start = $i + 1;
            $result = $bot->sendAlbum($bot->userID, $media)["result"][0]["message_id"];
            if($i < 10){ $start = "0$start"; }
            if($end < 10){ 
                if(count($media) < 10) { $end = "0".count($media); }
                else { $end = count($media); }
            } else { $end = count($media) + $i; }
            if($end < $tot_medias){ $menu = null; }
            else { $menu = [[["text" => "◀️ INDIETRO", "callback_data" => "episodes:group_$anime_id"."_1"]]]; }
            $bot->reply(str_replace("S0", "", "<b>$anime_name S$stagione</b>\n━━━━━━━━━━━━━━━\n🔥 | Ep <b>$start ~ $end</b>\n\n<i>N.B. L'ordine degli episodi va da sinistra verso destra!</i>"), $menu, false, $result);
        }else{
            $menu1[] = [["text" => "◀️ INDIETRO", "callback_data" => "episodes:group_$anime_id"."_1"]];
            $bot->sendVideo($bot->userID, $media[0]["media"], $media[0]["caption"], $menu1);
        }
        $i += 10;
    }
}

elseif($bot->data("special")){
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $index = $e[2];
    $delete = $e[3];
    $next_index = $index + 1; $prev_index = $index -1;
    $episode = $bot->conn->query("SELECT id, fileID, title FROM episodes WHERE anime_id = '$anime_id' AND tipo = 0 ORDER by upload_on ASC LIMIT $index, 1")->fetch();
    $episode_title = trim($episode["title"]);
    $episode_file_id = $episode["fileID"];
    $b = $bot->conn->query("SELECT fileID FROM episodes WHERE anime_id = '$anime_id' AND tipo = 0 ORDER by upload_on LIMIT $next_index, 1")->rowCount();

    $isadmin = $bot->isadmin();
    //Select anime info: Name and Season
    $anime = $bot->conn->query("SELECT nome, stagione FROM anime WHERE id = '$anime_id'")->fetch();
    $anime_name = $anime["nome"];
    $stagione = $anime["stagione"];
    if($episode_title != ""){
        $episode_caption = "<b>Special 0$next_index</b> | $episode_title";
    }else{
        $episode_caption = "<b>Special 0$next_index</b>";
    }
    if($stagione > 0){
        $stagioni = ["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"];
        $stagione = "➥ <i> ".str_replace([1,2,3,4,5,6,7,8,9,10], $stagioni, $stagione)." stagione</i>\n\n";
    }else{
        $stagione = '';
    }
    $text =  "<b>$anime_name</b>\n━━━━━━━━━━━━━━━\n$stagione"."🔥 $episode_caption";
    if($isadmin){
        $menu[] = [["text" => "🔧 MODIFICA", "callback_data" => "episodes:edit_".$episode["id"]],["text" => "🖊 TITOLO", "callback_data" => "episodes:title_".$episode["id"]], ["text" => "❌ ELIMINA EP", "callback_data" => "episodes:delete_".$episode["id"]]];
    }
    if($b){
        if($index == 0) {
            $menu[] = [["text" => "⏭", "callback_data" => "player:special_$anime_id"."_$next_index"]];
        }else {
            $menu[] = [["text" => "⏮", "callback_data" => "player:special_$anime_id"."_$prev_index"], ["text" => "⏭", "callback_data" => "player:special_$anime_id"."_$next_index"]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "⏮", "callback_data" => "player:special_$anime_id"."_$prev_index"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "episodes:specials_$anime_id"."_1"]];
    if($delete == 1) {
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $bot->sendVideo($bot->userID, $episode_file_id, $text, $menu);
    }else{
        $bot->editMedia($bot->msgid, $episode_file_id, "video", $text, $menu);
    }
}


<?php

if($bot->data("anime")){
    $bot->page("search:result");
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $delete_msg = $e[2];
    $query_string = "SELECT
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
    $q = $bot->conn->prepare($query_string);
    $q->bindParam(":anime", $anime_id);
    $q->execute();
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
    $text = "💮 <b>$nome</b>\n━━━━━━━━━━━━━
$stagione
🗓 | <b>Data:</b> $uscita
🏷 | <b>Categoria:</b> $categoria
🏢 | <b>Studio:</b> $studio_text
➕ | <b>Episodi:</b> $episodi
⏱️ | <b>Durata:</b> $durata
🏮 | <b>Stato:</b> $stato
📖 | <b>Trama:</b> <a href='$trama'>Clicca qui</a>$trailer\n
🌟 | <b>Generi:</b> $generi\n
👀 | <b>Visualizzazioni:</b> $views $anime";

    //...Get like/dislike
    $like = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 1 AND anime_id = '$anime_id'")->fetch()["tot"];
    $dislike = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 0 AND anime_id = '$anime_id'")->fetch()["tot"];

    $is_preferred = $bot->conn->query("SELECT anime_id FROM preferreds WHERE anime_id = '$anime_id' AND chat_id = '$bot->userID'")->rowCount();
    if($is_preferred) { $heart = "❤️"; }
    else { $heart = "💔";}
    $is_simulcast = $bot->conn->query("SELECT anime_id FROM anime_simulcast WHERE anime_id = '$anime_id'");
    if($is_simulcast->rowCount()){
        $notify = $bot->conn->query("SELECT anime_id FROM simulcast_notify WHERE chat_id = '$bot->userID' AND anime_id = '$anime_id'");
        if($notify->rowCount()) { $emoji = "🔔";}
        else { $emoji = "🔕"; }
        $menu[] = [["text" => "👍 $like", "callback_data" => "vote:like_$anime_id"],["text" => "👎 $dislike", "callback_data" => "vote:dislike_$anime_id"] ,["text" => "$heart", "callback_data" => "bookmark:preferred_$anime_id"], ["text" => "$emoji", "callback_data" => "bookmark:notify_$anime_id"]];
    }else{
        $menu[] = [["text" => "👍 $like", "callback_data" => "vote:like_$anime_id"],["text" => "👎 $dislike", "callback_data" => "vote:dislike_$anime_id"] ,["text" => "$heart", "callback_data" => "bookmark:preferred_$anime_id"]];
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
    //...SAVE THIS SEARCH INTO DATABASE
    $b = $bot->conn->prepare("INSERT INTO anime_cercati SET anime_id = :anime, by_user_id = :user");
    $b->bindParam(":anime", $anime_id);
    $b->bindParam(":user", $bot->userID);
    $b->execute();
    if($delete_msg){
        $cron = $bot->conn->query("SELECT id FROM search_keys WHERE chat_id = '$bot->userID' AND searched_on > NOW() - INTERVAL 10 MINUTE")->fetch()["id"];
        if($cron){
            $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "scroll:name"]];
        }else{
            $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home_1"]];
        }
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $result = $bot->si($bot->userID, $poster, $text, $menu);
        if(!$result["ok"] && $bot->isadmin()){
            $menu2[] = [["text" => "⚙️", "callback_data" => "setting:option_$anime_id"."_1"]];
            $bot->reply("Qualcosa è andato storto con l'apertura di questo anime, modifica le informazioni:", $menu2);
        }
    }else{
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:scroll_0"]];
        $result = $bot->editMedia($bot->msgid, $poster, "photo", $text, $menu);
        if(!$result["ok"] && $bot->isadmin()){
            $menu2[] = [["text" => "⚙️", "callback_data" => "setting:option_$anime_id"."_1"]];
            $bot->reply("Qualcosa è andato storto con l'apertura di questo anime, modifica le informazioni:", $menu2);
        }
    }
}

elseif($bot->data("simili")){
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $groupid = $bot->conn->query("SELECT group_id FROM anime_groups WHERE anime_id = '$anime_id'")->fetch()["group_id"];
    $query = "SELECT 
                  anime.id, 
                  anime.nome, 
                  anime.stagione,
                  anime_info.categoria,
                  COUNT(anime_genere.anime_id) AS tot
              FROM anime 
              INNER JOIN anime_genere 
              ON anime_genere.anime_id = anime.id 
              INNER JOIN anime_info
              ON anime_info.anime_id = anime.id
              WHERE anime_genere.genere_id IN
                                            (
                                                SELECT 
                                                    genere_id 
                                                FROM anime_genere 
                                                WHERE anime_id = :anime
                                            ) 
              AND anime.id NOT IN(SELECT anime_id FROM anime_groups WHERE group_id = :group)
              AND anime.id <> :anime
              AND anime.stagione < 2
              AND anime_info.categoria = 1
              GROUP by anime_genere.anime_id 
              HAVING COUNT(anime_genere.genere_id) >= 
              (
                    SELECT ( CASE WHEN COUNT(genere_id) < 5 THEN COUNT(genere_id) ELSE 4 END ) FROM anime_genere WHERE anime_id = :anime AND genere_id
              )
              ORDER by tot, RAND() DESC LIMIT 10";
    $q = $bot->conn->prepare($query);
    $q->bindParam(":anime", $anime_id);
    $q->bindParam(":group", $groupid);
    $q->execute();
    $i = 1;
    $x = 0;
    $y = 0;
    $text[] = "<b>TI SUGGERISCO ANCHE 🔽</b>\n";
    foreach($q as $ad){
        $id = $ad["id"];
        $text[] = "➥ <b><a href='t.me/myanimetvbot?start=animeID_".$id."'>".$ad["nome"]." S".$ad["stagione"]."</a></b>";
    }
    $text = str_replace("S0","",implode("\n",$text));
    //$menu[] = [["text" => "DI PIU", "callback_data" => "search:simili_$anime_id"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "view:anime_$anime_id"]];
    $img = $bot->setting["banner"]["consigliati"];
    $msg = "<a href='$img'>&#8203;</a><b>👇 Ti suggerisco di guardare anche:</>\n\n".$text;
    $bot->editMedia($bot->msgid, $bot->update->callback_query->message->photo[2]->file_id, "photo", $text, $menu);
}


elseif($bot->data("correlati")){
    $e = explode("_", $bot->cbdata);
    $group_id = $e[1];
    $anime_id = $e[2];
    $q = $bot->conn->query("SELECT 
                                anime.id,
                                anime.nome,
                                anime.stagione
                            FROM anime
                            INNER JOIN anime_groups 
                            ON anime_groups.anime_id = anime.id
                            WHERE anime_groups.group_id = '$group_id'
                            AND anime.id <> '$anime_id'
                            ORDER by anime_groups.stagione LIMIT 19");
    $i = 1;
    $x = 0;
    $y = 0;
    foreach($q as $ad){
        $id = $ad["id"];
        $text[] = "➥ <b><a href='t.me/myanimetvbot?start=animeID_".$id."'>".$ad["nome"]." S".$ad["stagione"]."</a></b>";
    }
    $text = str_replace("S0","",implode("\n",$text));
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "view:anime_$anime_id"]];
    $img = $bot->setting["banner"]["correlati"];
    $msg = "<a href='$img'>&#8203;</a><b>👇 ANIME CORRELATI</>\n\n".$text;
    $bot->editMedia($bot->msgid, $bot->update->callback_query->message->photo[2]->file_id, "photo", $msg, $menu);
}
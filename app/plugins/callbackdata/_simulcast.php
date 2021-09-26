<?php

if($bot->data("scroll")){
    $e = explode("_", $bot->cbdata);
    $id = $e[1];
    if($id == 9){
        $new_id = 0;
    }else{
        $new_id = $id + 1;
    }
    if($id == 0){
        $last_id = 9;
    }else{
        $last_id = $id - 1;
    }
    $q = $bot->conn->query("SELECT 
                                anime_simulcast.name,
                                anime_simulcast.anime_id, 
                                anime_simulcast.poster, 
                                COUNT(anime_views.anime_id) as views 
                            FROM anime_simulcast 
                            INNER JOIN anime_views 
                            ON anime_simulcast.anime_id = anime_views.anime_id 
                            WHERE anime_simulcast.slide = 1
                            GROUP by anime_views.anime_id 
                            ORDER by views DESC
                            LIMIT $id,1")->fetch();
    $poster = $q["poster"]; 
    $menu[] = [["text" => "🎥 GUARDA ORA", "callback_data" => "view:anime_".$q["anime_id"]]];
    $menu[] = [["text" => "ᐊ ᐊ ᐊ", "callback_data" => "simulcast:scroll_$last_id"],["text" => "ᐅ ᐅ ᐅ", "callback_data" => "simulcast:scroll_$new_id"]];
    $menu[] = [["text" => "🔎 CERCA ANIME", "callback_data" => "search:home_1"],["text" => "⏏️ MENU", "callback_data" => "simulcast:option"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home_1"]];
    $text = "💮 <b>".$q["name"]."</>\n".$bot->getNav($id);
    if(isset($e[2])){
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $bot->si($bot->userID,$poster, $text, $menu);
    }else{
        $bot->editMedia($bot->msgid, $poster, "photo", $text, $menu);
    }
    $bot->page("home:simulcast");
}

elseif($bot->data("option")){
    $menu[] = $bot->menu[0];
    $menu[] = $bot->menu[1];
    if(explode("_", $bot->cbdata)[1]){
        $menu[] = [["text" => "🔎 CERCA ANIME", "callback_data" => "search:home_1"],["text" => "⏏️ MENU", "callback_data" => "simulcast:option"]];
    }else{
        $menu[] = [["text" => "🔎 CERCA ANIME", "callback_data" => "search:home_1"],["text" => "🔽 MENU", "callback_data" => "simulcast:option_1"]];
        $menu[] = [["text" => "📋 LISTA SIMULCASTS", "callback_data" => "simulcast:list_0_1"]];
        $menu[] = [["text" => "👀 ANTEPRIMA STAGIONALI", "callback_data" => "simulcast:anteprima_1"]];
        $menu[] = [["text" => "📅 CALENDARIO", "callback_data" => "simulcast:calendario"]];
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home_1"]];
    $bot->editButton($menu);
}

elseif($bot->data("list")){
    $results_max = 11;
    $e = explode("_", $bot->cbdata);
    $index = $e[1]; 
    $delete = $e[2];
    $next_index = $index + $results_max; $prev_index = $index - $results_max;
    $q = $bot->conn->query("SELECT name, anime_id FROM anime_simulcast WHERE status = 1 ORDER by name LIMIT $index, $results_max");
    $results = $q->rowCount();
    $i = 1;
    $text = "";
    foreach($q as $anime){
        if($i < $results_max){
            $anime_id = $anime["anime_id"];
            $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
            $generi->bindParam(":anime", $anime_id);
            $generi->execute();
            $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
            $text = $text." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$anime["anime_id"]."'>".$anime["name"]."</a></b>\n$generi\n\n";
            $i++;
        }
    }
    $text = str_replace("S0","", $text);
    if($results == $results_max){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "simulcast:list_$next_index"]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "simulcast:list_$prev_index"],["text" => "»»»", "callback_data" => "simulcast:list_$next_index"]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "simulcast:list_$prev_index"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:scroll_0_1"]];
    $img = $bot->setting["banner"]["simulcasts"];
    if($delete){
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $bot->reply("<a href='$img'>&#8203;</a><b>LISTA SIMULCASTS</b>\n\n$text", $menu);
    }else{
        $bot->edit("<a href='$img'>&#8203;</a><b>LISTA SIMULCASTS</b>\n\n$text", $menu);
    }
}

elseif($bot->data("calendario")){
    $giorni = ["Lunedi", "Martedi", "Mercoledi", "Giovedi", "Venerdi", "Sabato", "Domenica", "NULL"];
    for($i = 1; $i <= 7; $i++){
        $q = $bot->conn->query("SELECT name, anime_id FROM anime_simulcast WHERE day_id = '$i'");
        $txt = array();
        foreach($q as $anime){
            $txt[] = "➥ <b><a href='t.me/myanimetvbot?start=animeID_".$anime["anime_id"]."'>".$anime["name"]."</a></b>";
        }
        $text[$i] = "📆 <b>".strtoupper($giorni[$i - 1])."</>\n".str_repeat("━", strlen($giorni[$i - 1]) + 2)."\n".implode("\n", $txt)."\n";
    }
    $img = $bot->setting["banner"]["calendario"];
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:scroll_0_1"]];
    $bot->reply("<a href='$img'>&#8203</>".implode("\n", $text), $menu);
}

elseif($bot->data("anteprima")){
    $delete = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "❄️ INVERNO", "callback_data" => "simulcast:stagione_1_0"], ["text" => "🌸 PRIMAVERA", "callback_data" => "simulcast:stagione_2_0"]];
    $menu[] = [["text" => "☀️ ESTATE", "callback_data" => "simulcast:stagione_3_0"], ["text" => "🍁 AUTUNNO", "callback_data" => "simulcast:stagione_4_0"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:scroll_0_1"]];
    $img = $bot->setting["banner"]["anteprima"];
    if($delete){
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $bot->reply("<a href='$img'>&#8203</><b>👇 Seleziona la stagione:</b>", $menu);
    }else{
        $bot->edit("<a href='$img'>&#8203</><b>👇 Seleziona la stagione:</b>", $menu);
    }
}

elseif($bot->data("stagione")){
    $e = explode("_", $bot->cbdata);
    $st = $e[1];
    $results_max = 11;
    $index = $e[2]; 
    $next_index = $index + 10; $prev_index = $index - 10;
    switch($st){
        case 1:
            $stagione = "❄️ ANTEPRIMA INVERNO";
            $mese1 = "12"; $mese2 = "01"; $mese3 = "02";
        break;
        case 2:
            $stagione = "🌸 ANTEPRIMA PRIMAVERA";
            $mese1 = "03"; $mese2 = "04"; $mese3 = "05";
        break;
        case 3:
            $stagione = "☀️ ANTEPRIMA ESTATE";
            $mese1 = "06"; $mese2 = "07"; $mese3 = "08";
        break;
        case 4:
            $stagione = "🍁 ANTEPRIMA AUTUNNO";
            $mese1 = "09"; $mese2 = "10"; $mese3 = "11";
        break;
    }
    $query = "  SELECT 
                    anime.id,
                    anime.nome,
                    anime.stagione,
                    anime_info.uscita
                FROM anime
                INNER JOIN anime_info
                ON anime.id = anime_info.anime_id
                WHERE anime_info.aired_on IS NOT NULL 
                AND anime_info.aired_on > NOW() 
                AND date_format(anime_info.aired_on, '%m') IN ('$mese1', '$mese2', '$mese3')
                ORDER by anime_info.aired_on ASC, anime.nome
                LIMIT $index, $results_max";
    $q = $bot->conn->query($query);
    $results = $q->rowCount();
    if($results){
        $i = 1;
        $text = "<b>$stagione</b>\n\n";
        foreach($q as $anime){
            if($i < $results_max){
                $anime_id = $anime["id"];
                $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
                $generi->bindParam(":anime", $anime_id);
                $generi->execute();
                $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
                $text = $text." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$anime["id"]."'>".$anime["nome"]." S".$anime["stagione"]."</a></b> <i>(".$anime["uscita"].")</i>\n$generi\n\n";
                $i++;
            }
        }
        $text = str_replace("S0","", $text);
        if($results == $results_max){
            if($index == 0){
                $menu[] = [["text" => "»»»", "callback_data" => "simulcast:stagione_$st"."_".$next_index]];
            }else{
                $menu[] = [["text" => "«««", "callback_data" => "simulcast:stagione_$st"."_".$prev_index],["text" => "»»»", "callback_data" => "simulcast:stagione_$st"."_".$next_index]];
            }
        }else{
            if($index > 0){
                $menu[] = [["text" => "«««", "callback_data" => "simulcast:stagione_$st"."_".$prev_index]];
            }
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:anteprima_0"]];
        $bot->edit($text, $menu);
    }else{
        $bot->alert("Non ci sono ancora anime previsti per questa stagione!");
    }
}


elseif($bot->data("title")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $title = $bot->conn->query("SELECT name FROM anime_simulcast WHERE anime_id = '$anime_id'")->fetch()["name"];
    $bot->edit("Ok, invia il nuovo titolo del simulcast:\nTitolo attuale: <b>$title</b>", $menu);
    $bot->page("setting:simulcastTitle_$anime_id");
}

elseif($bot->data("poster")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->edit("Ok, invia il nuovo banner del simulcast:", $menu);
    $bot->page("setting:simulcastBanner_$anime_id");
}

elseif($bot->data("settup")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->edit("Ok, invia il banner del simulcast:", $menu);
    $bot->page("setting:simulcastBanner_$anime_id"."_1");
}

elseif($bot->data("remove")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $bot->conn->query("DELETE FROM simulcast_notify WHERE anime_id = '$anime_id'");
    $bot->conn->query("DELETE FROM anime_simulcast WHERE anime_id = '$anime_id'");
    $bot->alert("Simulcast rimosso!");
    $menu[] = [["text" => "⏏️ MODIFICA INFO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $menu[] = [["text" => "➕ AGGIUNGI EPISODIO", "callback_data" => "episodes:upload_$anime_id"]];
    $menu[] = [["text" => "📤 INVIA", "callback_data" => "admin:sendAnime_$anime_id"], ["text" => "🗑 ELIMINA", "callback_data" => "admin:removeAnime_$anime_id"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "view:anime_$anime_id"."_1"]];
    $bot->edit("<b>⚜️ Seleziona un'opzione </b>", $menu);
}
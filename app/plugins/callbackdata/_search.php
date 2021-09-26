<?php


if($bot->data("home")){
    $menu[] = [["text" => "⏏️ RICERCA AVANZATA", "callback_data" => "search:option"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:scrollName_0_1"]];
    $q = $bot->conn->prepare("SELECT 
                                    anime.id, 
                                    MAX(anime_cercati.search_on) AS search_on,
                                    anime.nome,
                                    anime.stagione
                                FROM anime
                                INNER JOIN anime_cercati
                                ON anime_cercati.anime_id = anime.id
                                WHERE anime_cercati.by_user_id = :user
                                GROUP by anime_cercati.anime_id 
                                ORDER by search_on DESC
                                LIMIT 4");
    $q->bindParam(":user", $bot->userID);
    $q->execute();
    $i = 0;
    $emoji = ["🕑", "🕑", "🕒", "🕓", "🕔"];
    foreach($q as $ad){
        $anime_id = $ad["id"];
        $nome = $ad["nome"];
        $txt[] = " ➥ <a href = 't.me/myanimetvbot?start=animeID_$anime_id'>$nome S".$ad["stagione"]."</> $emoji[$i]";
        $i++;
    }
    $img = $bot->setting["banner"]["ricerca"];
    $text = str_replace("S0", "", "<a href='$img'>&#8203;</a>🔎 <b>CERCA ANIME</>\n\n<i>💾 Cronologia:</>\n".implode("\n", $txt))."\n\n <b>PERFETTO, INVIAMI IL NOME\n DELL' ANIME CHE DESIDERI GUARDARE!</>";
    if(explode("_", $bot->cbdata)[1]){
        $bot->deleteMessage($bot->chatID,$bot->msgid);
        $bot->reply($text, $menu);
    }else{
        $bot->edit($text, $menu);
    }
    $bot->page("search:q");
}

elseif($bot->data("option")){
    if(explode("_", $bot->cbdata)[1]){
        $menu[] = [["text" => "⏏️ RICERCA AVANZATA", "callback_data" => "search:option"]];
    }else{
        $menu[] = [["text" => "🔽 RICERCA AVANZATA", "callback_data" => "search:option_1"]];
        $menu[] = [["text" => "🌟 X GENERI", "callback_data" => "search:genres"], ["text" => "🔡 LISTA A-Z", "callback_data" => "search:list"]];
        $menu[] = [["text" => "📆 X ANNO", "callback_data" => "search:anno_0"], ["text" => "🏢 X STUDIO", "callback_data" => "search:studio_0"]];
        $menu[] = [["text" => "🔢 X NR.EP", "callback_data" => "search:nrepisodi"], ["text" => "🎲 CASUALE", "callback_data" => "search:random_1"]];
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "simulcast:scrollName_0_1"]];
    $bot->editButton($menu);
}

elseif($bot->data("list")){
    $bot->page("search:list");
    $menu[] = [["text" => "A", "callback_data" => "scroll:list_0_a"],["text" => "B", "callback_data" => "scroll:list_0_b"],["text" => "C", "callback_data" => "scroll:list_0_c"],["text" => "D", "callback_data" => "scroll:list_0_d"]];
    $menu[] = [["text" => "E", "callback_data" => "scroll:list_0_e"],["text" => "F", "callback_data" => "scroll:list_0_f"],["text" => "G", "callback_data" => "scroll:list_0_g"],["text" => "H", "callback_data" => "scroll:list_0_h"]];
    $menu[] = [["text" => "I", "callback_data" => "scroll:list_0_i"],["text" => "J", "callback_data" => "scroll:list_0_j"],["text" => "K", "callback_data" => "scroll:list_0_k"],["text" => "L", "callback_data" => "scroll:list_0_l"]];
    $menu[] = [["text" => "M", "callback_data" => "scroll:list_0_m"],["text" => "N", "callback_data" => "scroll:list_0_n"],["text" => "O", "callback_data" => "scroll:list_0_o"],["text" => "P", "callback_data" => "scroll:list_0_p"]];
    $menu[] = [["text" => "Q", "callback_data" => "scroll:list_0_q"],["text" => "R", "callback_data" => "scroll:list_0_r"],["text" => "S", "callback_data" => "scroll:list_0_s"],["text" => "T", "callback_data" => "scroll:list_0_t"]];
    $menu[] = [["text" => "U", "callback_data" => "scroll:list_0_u"],["text" => "V", "callback_data" => "scroll:list_0_v"],["text" => "W", "callback_data" => "scroll:list_0_w"],["text" => "X", "callback_data" => "scroll:list_0_x"]];
    $menu[] = [["text" => "Y", "callback_data" => "scroll:list_0_y"],["text" => "Z", "callback_data" => "scroll:list_0_z"],["text" => "#", "callback_data" => "scroll:list_0_#"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
    $bot->edit("<a href='$img'>&#8203;</><b>Seleziona un indice qua sotto ⤵️</>", $menu);
}

elseif($bot->data("studio")){
    $bot->page("search:studio");
    $index = explode("_", $bot->cbdata)[1];
    $next_index = $index + 20; $prev_index = $index - 20;
    $query = 
      " SELECT 
            studios.id,
            studios.name,
            COUNT(anime_studios.anime_id) AS tot
        FROM studios
        LEFT JOIN anime_studios
        ON studios.id = anime_studios.studio_id
        GROUP by studios.id
        HAVING COUNT(anime_studios.anime_id) > 0
        ORDER by tot DESC, studios.name
        LIMIT $index, 21";
    $q = $bot->conn->query($query);
    $text = "<b>LISTA STUDIO</b>\n\n";
    $i = 0;
    foreach($q as $row){
        $text = $text."» <a href='t.me/myanimetvbot?start=studio_".$row["id"]."'>".$row["name"]." (".$row["tot"].")</a>\n";
        $i++;
        if($i == 20){break;}
    }
    if($q->rowCount() == 21){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "search:studio_".$next_index]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "search:studio_".$prev_index],["text" => "»»»", "callback_data" => "search:studio_".$next_index]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "search:studio_".$prev_index]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
    $bot->edit($text, $menu);
}

elseif($bot->data("genres")){
    $bot->page("search:genres");
    $e = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT id, nome FROM generi");
    $x = 0; 
    $y = 0;
    foreach($q as $ad){
        $genres_id = $ad["id"];
        $row = $bot->conn->prepare("SELECT genres_id FROM generi_cercati WHERE genres_id = :id AND by_user_id = :user");
        $row->bindParam(":id", $genres_id);
        $row->bindParam(":user", $bot->userID);
        $row->execute();
        if($row->rowCount()){
            $txt = $ad["nome"]." 🔵";
        }else{
            $txt = $ad["nome"]." 🔴";
        }
        if($x < 2){
            $menu[$y][] = ["text" => $txt, "callback_data" => "search:srcgenere_".$ad["id"]];
            $x++;
        }else{
            ++$y;
            $x = 1;
            $menu[$y][] = ["text" => $txt, "callback_data" => "search:srcgenere_".$ad["id"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
    if($e){
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $bot->sm($bot->userID, "<a href='http://simplebot.ml/bots/stani/myanimetv/img/CERCAANIME(banner).png'>&#8203;</><b>Seleziona i generi che vuoi cercare:</>\n<b>Generi selezionati:\n ➥</> ", $menu);
    }else{
        $bot->edit("<a href='http://simplebot.ml/bots/stani/myanimetv/img/CERCAANIME(banner).png'>&#8203;</><b>Seleziona i generi che vuoi cercare:</>\n<b>Generi selezionati:\n ➥</> ", $menu);
    }
}

//...UPDATE SEARCH GENRES TAB
elseif($bot->data("srcgenere")){
    $id = explode("_", $bot->cbdata)[1];
    //...ADD/REMOVE genres
    $row = $bot->conn->prepare("SELECT genres_id FROM generi_cercati WHERE genres_id = :id AND by_user_id = :user");
    $row->bindParam(":id", $id);
    $row->bindParam(":user", $bot->userID);
    $row->execute();
    if($row->rowCount()){ //...Remove
        $delete = $bot->conn->prepare("DELETE FROM generi_cercati WHERE genres_id = :id AND by_user_id = :user");
        $delete->bindParam(":id", $id);
        $delete->bindParam(":user", $bot->userID);
        $delete->execute();
    }else{ //...ADD
        $add = $bot->conn->prepare("INSERT INTO generi_cercati SET genres_id = :id, by_user_id = :user");
        $add->bindParam(":id", $id);
        $add->bindParam(":user", $bot->userID);
        $add->execute();
    }
    $q = $bot->conn->query("SELECT id, nome FROM generi");
    $x = 0;
    $y = 0;
    $selezionati = [];
    foreach($q as $ad){
        $genres_id = $ad["id"];
        $row = $bot->conn->prepare("SELECT genres_id FROM generi_cercati WHERE genres_id = :id AND by_user_id = :user");
        $row->bindParam(":id", $genres_id);
        $row->bindParam(":user", $bot->userID);
        $row->execute();
        if($row->rowCount()){
            $txt = $ad["nome"]." 🔵";
            $selezionati[] = "#".$ad["nome"];
        }else{
            $txt = $ad["nome"]." 🔴";
        }
        if($x < 2){
            $menu[$y][] = ["text" => $txt, "callback_data" => "search:srcgenere_".$ad["id"]];
            $x++;
        }else{
            ++$y;
            $x = 1;
            $menu[$y][] = ["text" => $txt, "callback_data" => "search:srcgenere_".$ad["id"]];
        }
    }
    $menu[] = [["text" => "✅ AVVIA RICERCA", "callback_data" => "scroll:genre_0"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
    $bot->edit("<b>Seleziona i generi che vuoi cercare</>\n<b>Generi selezionati:\n ➥</> ".implode(" ", $selezionati), $menu);
}


elseif($bot->data("nrepisodi")){
    $bot->page("search:nrepisodi");
    $menu[] = [["text" => "1~12ep", "callback_data" => "scroll:episodi_1-12_0"], ["text" => "13~26ep", "callback_data" => "scroll:episodi_13-26_0"]];
    $menu[] = [["text" => "27~60ep", "callback_data" => "scroll:episodi_27-63_0"], ["text" => "64~120ep", "callback_data" => "scroll:episodi_64-102_0"]];
    $menu[] = [["text" => "121~300ep", "callback_data" => "scroll:episodi_121-300_0"], ["text" => "+300ep", "callback_data" => "scroll:episodi_300-1500_0"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
    $bot->edit("<b>Seleziona il numero di episodi per la ricerca</b>", $menu);
}


elseif($bot->data("anno")){
    $bot->page("search:anno");
    $index = explode("_", $bot->cbdata)[1]; 
    $next_index = $index + 10; $prev_index = $index - 10;
    $actual_year = date("Y"); $year = $actual_year - $index;
    $x = 0; $y = 0;
    for($i = $year; $i > $year - 12; $i--){
        if($x  < 3){ $x++; }
        else{ $y++; $x = 1; }
        $buttons[$y][] = ["text" => $i, "callback_data" => "scroll:anno_0_$i"];
    }
    if($actual_year == $year && $year > 1980){
        $buttons[] = [["text" => "»»»", "callback_data" => "search:anno_$next_index"]];
    }elseif($year > 1991){
        $buttons[] = [["text" => "«««", "callback_data" => "search:anno_$prev_index"],["text" => "»»»", "callback_data" => "search:anno_$next_index"]];
    }else{
        $buttons[] = [["text" => "«««", "callback_data" => "search:anno_$prev_index"]];
    }
    $buttons[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
    $bot->edit("<b>Seleziona l'anno per la ricerca</b>", $buttons);
}

elseif($bot->data("random")){
    $option = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT 
                                anime.id, 
                                anime.nome, 
                                anime.poster,
                                anime_info.trama_url
                            FROM anime 
                            INNER JOIN anime_info
                            ON anime.id = anime_info.anime_id
                            WHERE stagione < 2 
                            ORDER by RAND() LIMIT 1");
    $info = $q->fetch();
    $anime_id = $info["id"];
    $nome = $info["nome"];
    $generi = $bot->conn->query("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id INNER JOIN anime ON anime.id = anime_genere.anime_id WHERE anime.id = '$anime_id'")->fetchAll();
    $generi = "#".implode(" #", array_column($generi, 'nome'));
    $poster = $info["poster"];
    $trama = $info["trama_url"];
    $text = "<b>✦ $nome</>\n━━━━━━━━━━━━\n☯ | Generi:\n$generi\n<b>📖 | Trama:\n</b> $trama";
    $menu[] = [["text" => "🎥 GUARDA ORA", "callback_data" => "view:anime_$anime_id"]];
    $menu[] = [["text" => "🎲 NUOVO RANDOM", "callback_data" => "search:random_1"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home_1"]];
    if(!$option){
        $bot->editMedia($bot->msgid, $poster, "photo", $text, $menu);
    }else{
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $bot->si($bot->userID, $poster, $text, $menu);
    }
}
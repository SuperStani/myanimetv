<?php
include "/var/www/html/bots/myanimetv/app/queries/search.php";
if($bot->data("option")){
    $e = explode("_", $bot->cbdata);
    if($e[1] == 1){
        $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option"]];
        for($i = 2; $i < count($bot->menu); $i++){
            $menu[] = $bot->menu[$i];
        }
    }else{
        $menu[] = [["text" => "🔽 ORDINE", "callback_data" => "scroll:option_1"]];
        $user_search = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
        $orders = $bot->conn->query("SELECT id, name, emoji FROM search_orders");
        foreach($orders as $ad){
            if($ad["id"] == $user_search){
                $text = "🔘 ".$ad["name"];
            }else{
                $text = $ad["emoji"]." ".$ad["name"];
            }
            $menu[1][] = ["text" => $text, "callback_data" => "scroll:set_".$ad["id"]];
        }
        for($i = 1; $i < count($bot->menu); $i++){
            $menu[] = $bot->menu[$i];
        }
    }
    $bot->editButton($menu);
}

elseif($bot->data("set")){
    $id = explode("_", $bot->cbdata)[1];
    $bot->conn->query("UPDATE utenti SET srcOrder = '$id' WHERE chat_id = '$bot->userID'");
    $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option"]];
    for($i = 2; $i < count($bot->menu); $i++){
        $menu[] = $bot->menu[$i];
    }
    $bot->alert("🤓 Scorri avanti o indietro per vedere le modifiche!");
    $bot->editButton($menu);
}

//Name
elseif($bot->data("name")){
    $e = explode("_", $bot->cbdata);
    $key_info = $bot->conn->query("SELECT id, text, index_point FROM search_keys WHERE chat_id = '$bot->userID' ORDER by searched_on DESC LIMIT 1")->fetch();
    $search = '%'.$key_info["text"].'%';
    if(isset($e[1])){ //Index search
        $index = $e[1];
        $id_search = $key_info["id"];
        $bot->conn->query("UPDATE search_keys SET index_point = '$index' WHERE id = '$id_search'");
    }else{
        $del = 1;
        $index = $key_info["index_point"];
        $bot->alert("$index");
    }
    $next_index = $index + 10; $prev_index = $index - 10;
    $results_max = 11;
    $order = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $q = $bot->conn->prepare($query["name"][$order - 1]);
    $q->bindParam(":search", $search);
    $q->bindParam(":searchIndex", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $results_max, PDO::PARAM_INT);
    $q->execute();
    $results = $q->rowCount();
    $i = 1; $x = 0; $y = 0;
    if($results == $results_max){ $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]]; $y = 1; }
    $text = "🔎 Ecco i risultati della ricerca: <b>\"".$key_info["text"]."\"</b>\n\n";
    foreach($q as $ad){
        if($i < $results_max){
            $anime_id = $ad["id"];
            $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
            $generi->bindParam(":anime", $anime_id);
            $generi->execute();
            $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
            $nr = str_replace(["10","1","2","3","4","5","6","7","8","9"],["🔟","1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣"],$i);
            $text = $text.$nr." ➥ <b>".$ad["nome"]." S".$ad["stagione"]."</>\n$generi\n";
            if($x < 5){
                $x++;
            }else{
                $y++;
                $x = 1;
            }
            $menu[$y][] = ["text" => $nr, "callback_data" => "view:anime_".$ad["id"]."_1"];
            $i++;
        }
    }
    $text = str_replace("S0","", $text);
    if($results == $results_max){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "scroll:name_".$next_index]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "scroll:name_".$prev_index],["text" => "»»»", "callback_data" => "scroll:name_".$next_index]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "scroll:name_".$prev_index]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
    if($del){
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $bot->sm($bot->userID, $text, $menu);
    }else{
        $bot->edit($text, $menu);
    }
}

//Episodi
elseif($bot->data("episodi")){
    $e = explode("_", $bot->cbdata);
    $index = $e[2];
    $e = explode("-", $e[1]);
    $prev_index = $index - 10; $next_index = $index + 10;
    $start = $e[0]; $limit = $e[1];
    $results_max = 11;
    $order = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $q = $bot->conn->prepare($query["episodi"][$order - 1]);
    $q->bindParam(":start_index", $start);
    $q->bindParam(":limit_index", $limit);
    $q->bindParam(":searchIndex", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $results_max, PDO::PARAM_INT);
    $q->execute();
    $results = $q->rowCount();
    if($results){
        $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]];
        $i = 1; $x = 0; $y = 1;
        $text = "🔎 Ecco i risultati della ricerca:\n\n";
        foreach($q as $ad){
            if($i < $results_max){
                $anime_id = $ad["id"];
                $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
                $generi->bindParam(":anime", $anime_id);
                $generi->execute();
                $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
                $nr = str_replace(["10","1","2","3","4","5","6","7","8","9"],["🔟","1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣"],$i);
                $text = $text.$nr." ➥ <b>".$ad["nome"]." S".$ad["stagione"]."</b> <i>(".$ad["episodi"]."ep)</i>\n$generi\n";
                if($x < 5){
                    $x++;
                }else{
                    $y++;
                    $x = 1;
                }
                $menu[$y][] = ["text" => $nr, "callback_data" => "view:anime_".$ad["id"]."_1"];
            }
            $i++;
        }
        $text = str_replace("S0","", $text);
        if($results == $results_max){
            if($index == 0){
                $menu[] = [["text" => "»»»", "callback_data" => "scroll:episodi_$start-$limit"."_$next_index"]];
            }else{
                $menu[] = [["text" => "«««", "callback_data" => "scroll:episodi_$start-$limit"."_$prev_index"],["text" => "»»»", "callback_data" => "scroll:episodi_$start-$limit"."_$next_index"]];
            }
        }else{
            if($index > 0){
                $menu[] = [["text" => "«««", "callback_data" => "scroll:episodi_$start-$limit"."_$prev_index"]];
            }
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:nrepisodi"]];
        $bot->edit($text, $menu);
    }else{
        $bot->alert("Non ho trovato anime");
    }
}

//Anno
elseif($bot->data("anno")){
    $e = explode("_", $bot->cbdata);
    $index = $e[1];
    $next_index = $index + 10; $prev_index = $index - 10;
    $year = $e[2];
    $search = "%".$year."%";
    $order = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $results_max = 11;
    $q = $bot->conn->prepare($query["anno"][$order - 1]);
    $q->bindParam(":search", $search);
    $q->bindParam(":searchIndex", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $results_max, PDO::PARAM_INT);
    $q->execute();
    $results = $q->rowCount();
    if($results){
        $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]];
        $i = 1; $x = 0; $y = 1;
        $text = "🔎 Ecco i risultati della ricerca: \"<b>Anno $year\"</b>\n\n";
        foreach($q as $ad){
            if($i < $results_max){
                $anime_id = $ad["id"];
                $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
                $generi->bindParam(":anime", $anime_id);
                $generi->execute();
                $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
                $nr = str_replace(["10","1","2","3","4","5","6","7","8","9"],["🔟","1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣"],$i);
                $text = $text.$nr." ➥ <b>".$ad["nome"]." S".$ad["stagione"]."</b>\n$generi\n";
                if($x < 5){
                    $x++;
                }else{
                    $y++;
                    $x = 1;
                }
                $menu[$y][] = ["text" => $nr, "callback_data" => "view:anime_".$ad["id"]."_1"];
            }
            $i++;
        }
        $text = str_replace("S0","", $text);
        if($results == $results_max){
            if($index == 0){
                $menu[] = [["text" => "»»»", "callback_data" => "scroll:anno_".$next_index."_$year"]];
            }else{
                $menu[] = [["text" => "«««", "callback_data" => "scroll:anno_".$prev_index."_$year"],["text" => "»»»", "callback_data" => "scroll:anno_".$next_index."_$year"]];
            }
        }else{
            if($index > 0){
                $menu[] = [["text" => "«««", "callback_data" => "scroll:anno_".$prev_index."_$year"]];
            }
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:anno_0"]];
        $bot->edit($text, $menu);
    }else{
        $bot->alert("Non ho trovato anime dell'anno \"$year\"");
    }
}

elseif($bot->data("list")){
    //$bot->alert("$bot->cbdata");
    $e = explode("_", $bot->cbdata);
    $index = $e[1];
    $next_index = $index + 15; $prev_index = $index - 15;
    $results_max = 16;
    $word = strtoupper($e[2]);
    if($word == "#"){
        $key_word = '^[0-9\.\_\#]';
        $query = "SELECT id, nome, stagione FROM anime WHERE nome REGEXP :nome ORDER by nome, stagione LIMIT :searchIndex, :limite";
    }else{
        $key_word = $word.'%';
        $query = "SELECT id, nome, stagione FROM anime WHERE nome LIKE :nome ORDER by nome, stagione LIMIT :searchIndex, :limite";
    }
    $q = $bot->conn->prepare($query);
    $q->bindParam(":nome", $key_word);
    $q->bindParam(":searchIndex", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $results_max, PDO::PARAM_INT);
    $q->execute();
    $results = $q->rowCount();
    if($results){
        //$menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]];
        $text = "🔎 <b>INDICE $word</b>\n\n";
        $i = 1;
        foreach($q as $ad){
            $anime_id = $ad["id"];
            $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
            $generi->bindParam(":anime", $anime_id);
            $generi->execute();
            $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
            if($i < $results_max){ $text = $text." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$ad["id"]."'>".$ad["nome"]." S".$ad["stagione"]."</a></b>\n$generi\n\n"; }
            $i++;
        }
        $text = str_replace("S0","", $text);
        if($results == $results_max){
            if($index == 0){
                $menu[] = [["text" => "»»»", "callback_data" => "scroll:list_".$next_index."_$word"]];
            }else{
                $menu[] = [["text" => "«««", "callback_data" => "scroll:list_".$prev_index."_$word"],["text" => "»»»", "callback_data" => "scroll:list_".$next_index."_$word"]];
            }
        }else{
            if($index > 0){
                $menu[] = [["text" => "«««", "callback_data" => "scroll:list_".$prev_index."_$word"]];
            }
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:list"]];
        $bot->edit($text, $menu);
    }else{
        $bot->alert("Non sono disponibili anime che iniziano con la lettera \"$word\"");
    }
}

elseif($bot->data("studio")){
    $e = explode("_", $bot->cbdata);
    $studio = $e[1];
    $index = $e[2];
    $next_index = $index + 10; $prev_index = $index - 10;
    $results_max = 11;
    $studioName = $bot->conn->prepare("SELECT name FROM studios WHERE id = :id");
    $studioName->bindParam(":id", $studio);
    $studioName->execute();
    $studioName = $studioName->fetch()["name"];
    $q = $bot->conn->prepare("SELECT anime.id, anime.nome, anime.stagione FROM anime INNER JOIN anime_studios ON anime.id = anime_studios.anime_id WHERE anime_studios.studio_id = :studio ORDER by anime.nome, anime.stagione LIMIT $index, 11");
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
        if($i < $results_max){ $text = $text." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$ad["id"]."'>".$ad["nome"]." S".$ad["stagione"]."</a></b>\n$generi\n\n"; }
        $i++;
    }
    
    $text = str_replace("S0","", $text)."<b>🏢 $studioName</b>";
    if($q->rowCount() == $results_max){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "scroll:studio_$studio"."_$next_index"]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "scroll:studio_$studio"."_$prev_index"],["text" => "»»»", "callback_data" => "scroll:studio_$studio"."_$next_index"]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "scroll:studio_$studio"."_$prev_index"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:studio_0"]];
    $bot->edit($text, $menu);
}

//Generi
elseif($bot->data("genre")){
    $e = explode("_", $bot->cbdata);
    $index = $e[1];
    $next_index = $index + 10; $prev_index = $index - 10;
    $results_max = 11;
    $query = "SELECT 
  	              anime.id, 
                  anime.nome, 
                  anime.stagione 
              FROM anime 
              INNER JOIN anime_genere 
              ON anime_genere.anime_id = anime.id 
              WHERE anime_genere.genere_id IN(SELECT 
                                              genres_id 
                                              FROM generi_cercati 
                                              WHERE by_user_id = '$bot->userID'
                                          ) 
              AND anime.stagione < 2
              GROUP by anime_genere.anime_id 
              HAVING COUNT(anime_genere.genere_id) >= (SELECT 
                                                      COUNT(genres_id) 
                                                      FROM generi_cercati 
                                                      WHERE by_user_id = '$bot->userID' 
                                                      )
              ORDER by anime.nome LIMIT $index, $results_max";  
    $q = $bot->conn->prepare($query);
    $results = $q->execute();
    if($results){
        //$menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]];
        $i = 1; $x = 0; $y = 0;
        foreach($q as $ad){
            if($i < $results_max){
                $anime_id = $ad["id"];
                $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
                $generi->bindParam(":anime", $anime_id);
                $generi->execute();
                $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
                $nr = str_replace(["10","1","2","3","4","5","6","7","8","9"],["🔟","1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣"],$i);
                $text[] = $nr." ➥ <b>".$ad["nome"]." S".$ad["stagione"]."</>\n$generi";
                if($x < 5){  $x++; }
                else{ $y++; $x = 1; }
                $menu[$y][] = ["text" => $nr, "callback_data" => "view:anime_".$ad["id"]."_1"];
            }
            $i++;
        }
        $text = str_replace("S0","",implode("\n",$text));
        if($results == $results_max){
            if($index == 0){
                $menu[] = [["text" => "»»»", "callback_data" => "scroll:genre_".$next_index]];
            }else{
                $menu[] = [["text" => "«««", "callback_data" => "scroll:genre_".$prev_index],["text" => "»»»", "callback_data" => "scroll:genre_".$next_index]];
            }
        }else{
            if($index > 0){
                $menu[] = [["text" => "«««", "callback_data" => "scroll:genre_".$prev_index]];
            }
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:genres"]];
        $bot->edit("<b>🔎 Ecco i risultati della ricerca:</>\n\n$text", $menu);
    }else{
        $bot->alert(":( Non sono riuscito a trovare dei risultati!");
    }
}
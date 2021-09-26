<?php

if($bot->data("home")){
    $menu[] = [["text" => "❤️ I PIU VOTATI", "callback_data" => "top:votes_0"],["text" => "🗣 I PIU POPOLARI", "callback_data" => "top:popolari_0"]];
    $menu[] = [["text" => "👥 CLASSIFICA UTENTI", "callback_data" => "top:usersAnime"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $img = $bot->setting["banner"]["classifiche"];
    $bot->edit("<a href='$img'>&#8203;</a><b>🔝 CLASSIFICHE</b>\n\n<i>👇 Seleziona una qua sotto:</>", $menu);
}

elseif($bot->data("votes")){
    $index = explode("_", $bot->cbdata)[1];
    $next_index = $index + 15; $prev_index = $index - 15;
    $results_max = 11;
    $query = "  SELECT 
                    anime.id, 
                    anime.nome,
                    anime.stagione, 
                    COUNT(votes.anime_id) AS votes 
                FROM anime 
                INNER JOIN votes
                ON anime.id = votes.anime_id 
                GROUP by votes.anime_id 
                ORDER by votes DESC 
                LIMIT $index, $results_max";
    $q = $bot->conn->query($query);
    $results = $q->rowCount();
    $i = 1;
    foreach($q as $anime){
        if($i < $results_max){
            $anime_id = $anime["id"];
            $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
            $generi->bindParam(":anime", $anime_id);
            $generi->execute();
            $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
            $text = $text." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$anime["id"]."'>".$anime["nome"]."</a></b> ~ <b>".$anime["votes"]." voti</b>\n$generi\n\n";
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    if($results == $results_max){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "top:votes_$next_index"]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "top:votes_$prev_index"],["text" => "»»»", "callback_data" => "top:votes_$next_index"]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "top:votes_$prev_index"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "top:home"]];
    $bot->edit($text, $menu);
}

else if($bot->data("popolari")){
    $index = explode("_", $bot->cbdata)[1];
    $next_index = $index + 15; $prev_index = $index - 15;
    $results_max = 11;
    $query = "  SELECT 
                    anime.id, 
                    anime.nome, 
                    anime.stagione, 
                    COUNT(anime_views.anime_id) as views 
                FROM anime 
                LEFT JOIN anime_views 
                ON anime.id = anime_views.anime_id 
                GROUP by anime.id
                ORDER by views DESC 
                LIMIT $index, $results_max";
    $q = $bot->conn->query($query);
    $results = $q->rowCount();
    $i = 1;
    foreach($q as $anime){
        if($i < $results_max){
            $anime_id = $anime["id"];
            $generi = $bot->conn->prepare("SELECT generi.nome FROM generi INNER JOIN anime_genere ON anime_genere.genere_id = generi.id WHERE anime_genere.anime_id = :anime ORDER by generi.id DESC LIMIT 3");
            $generi->bindParam(":anime", $anime_id);
            $generi->execute();
            $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
            $text = $text." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$anime["id"]."'>".$anime["nome"]."</a></b>\n$generi\n\n";
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    if($results == $results_max){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "top:popolari_$next_index"]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "top:popolari_$prev_index"],["text" => "»»»", "callback_data" => "top:popolari_$next_index"]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "top:popolari_$prev_index"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "top:home"]];
    $bot->edit($text, $menu);
}

elseif($bot->data("usersAnime")){
    $query = "  SELECT 
                    COUNT(anime.id) AS anime, 
                    utenti.chat_id,
                    utenti.username
                FROM anime
                INNER JOIN bookmarks
                ON anime.id = bookmarks.anime_id 
                INNER JOIN utenti
                ON bookmarks.chat_id = utenti.chat_id
                WHERE anime.stagione < 2
                AND utenti.username <> ''
                AND bookmarks.list_id = 1
                GROUP by utenti.chat_id, utenti.username
                ORDER By anime DESC";
    $q = $bot->conn->query($query);
    $text[] = "💮 <b>Classifica utenti</>:\n";
    $i = 1;
    $break = false;
    $posizione = "??";
    foreach($q as $row){
        $nr = str_replace(["10","1","2","3","4","5","6","7","8","9"],["🔟","1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣"], $i);
        if($i <= 10){
            $text[] = "\n$nr <a href='t.me/myanimetvbot?start=profile_".$row["chat_id"]."'>".$row["username"]."</a> <b>[".$row["anime"]."]</>";
            if($row["chat_id"] == $bot->userID){
                $posizione = $i;
            }
        }else{
            if($row["chat_id"] == $bot->userID){
                $posizione = $i;
                break;
            }
        }
        $i++;
    }
    $text[] = "\n\nLa tua posizione: <b>#$posizione</>";
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "top:home"]];
    $bot->edit(implode($text), $menu);
}

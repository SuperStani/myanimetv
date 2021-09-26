<?php

if($bot->checkPage("poster") && isset($bot->photo)){
    $msgid = $bot->si(-1001328992269, $bot->file_id)["result"]["message_id"];
    $bot->conn->query("INSERT INTO anime SET poster = '$bot->file_id', poster_msgid = '$msgid'");
    $anime_id = $bot->conn->lastInsertId();
    $bot->conn->query("INSERT INTO anime_info SET anime_id = '$anime_id'");
    $bot->reply("<b>Inviami il nome dell'anime:</b>\n\n<b>N.B.</b> <i>Per aggiungere titoli alternativi usa questa sintassi \"TITOLO_PRINCIPALE + NOME_ALT1, NOME_ALT2\"</i>");
    $bot->page("post:name_$anime_id");
}

elseif($bot->checkPage("name")){
    $anime_id = explode("_", $bot->page)[1];
    $e = explode("+", $bot->msg);
    $title = trim($e[0]);
    if(isset($e[1])){
        $alternative = trim($e[1]);
        $q = $bot->conn->prepare("UPDATE anime SET nome = :nome, nomi_alternativi = :alternative WHERE id = :id");
        $q->bindParam(":nome", $title);
        $q->bindParam(":alternative", $alternative);
        $q->bindParam(":id", $anime_id);
        $q->execute();
    }else{
        $q = $bot->conn->prepare("UPDATE anime SET nome = :nome WHERE id = :id");
        $q->bindParam(":nome", $title);
        $q->bindParam(":id", $anime_id);
        $q->execute();
    }
    $bot->reply("<b>Ok invia il numero della stagione:</b>\n\n<b>N.B</b> <i>Inviare 0 se l'anime è singolo (Senza altre stagioni)</i>");
    $bot->page("post:stagione_$anime_id");
}

elseif($bot->checkPage("stagione")) {
    $anime_id = explode("_", $bot->page)[1];
    if(is_numeric($bot->msg)){
        $bot->conn->query("UPDATE anime SET stagione = '$bot->msg' WHERE id = '$anime_id'");
        $menu[] = [["text" => "✏️ INSERIMENTO MANUALE", "callback_data" => "setting:option_$anime_id"."_1"]];
        $bot->reply("<b>Ok adesso invia il link dell'anime</b> (animeworld):", $menu);
        $bot->page("post:url_$anime_id");
    }
}

elseif($bot->checkPage("url")){
    $anime_id = explode("_", $bot->page)[1];
    if(strstr($bot->msg, "https:")){
        $info = json_decode(file_get_contents("https://api.myanimetv.org/scrape/info?url=$url$bot->msg"), true);
        $mese = ["gennaio", "febbraio", "marzo", "maggio", "aprile", "giugno", "luglio", "agosto", "settembre", "ottobre", "novembre", "dicembre"];
        $uscita = $info["uscita"];
        $e = explode(" ", $uscita);
        $d = str_replace("??", "01", $e[0]); $m = str_replace($mese, ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"], strtolower($e[1])); $y = $e[2];
        $date = "$y-$m-$d";
        $uscita = $info["uscita"];
        $generi = $info["generi"];
        $nomi_alternativi = $info["alternative-title"]. ", ".$info["name"];
        $durata_ep = $info["durata_ep"];
        $episodi = (int) $info["episodi"];
        $trama = $info["trama"];
        $trailer = $info["trailer"];
        $studio = explode(", ", $info["studio"]);
        foreach($studio as $row){
            $q = $bot->conn->prepare("SELECT * FROM studios WHERE name LIKE :nome");
            $q->bindParam(":nome", $row);
            $q->execute();
            $studio_id = $q->fetch()["id"];
            $bot->conn->query("INSERT INTO anime_studios SET anime_id = '$anime_id', studio_id = '$studio_id'");
        }
        $mal_id = $info["mal_id"];
    
        $nodes = [
            [
                "tag" => "p",
                "children" => [
                    str_replace(["&quot;", "&#039;"], ["\"", "'"], $trama)
                ]
            ]
        ];
        $token = "6e6bb3d16c6201fe33efdebb37bf7b912a334303d900825f0411a2f41912";
        $trama_url = json_decode(file_get_contents("https://api.telegra.ph/createPage?access_token=$token&title=TRAMA+".strtoupper(str_replace([" ", "'"],["+", ""], explode(",", $nomi_alternativi)[1]))."&author_name=MY+ANIME+TV&content=".urlencode(json_encode($nodes))."&return_content=true"), true)["result"]["url"];
        
        $q = $bot->conn->prepare("UPDATE anime_info SET trama_url = :url, trailer = :trailer, episodi = :episodi, trama = :trama, uscita = :uscita, durata_ep = :durata, aired_on = :aired WHERE anime_id = :anime_id");
        $q->bindParam(":anime_id", $anime_id);
        $q->bindParam(":episodi", $episodi);
        $q->bindParam(":url", $trama_url);
        $q->bindParam(":trailer", $trailer);
        $q->bindParam(":trama", $trama);
        $q->bindParam(":uscita", $uscita);
        $q->bindParam(":durata", $durata_ep);
        $q->bindParam(":aired", $date);
        $q->execute();
    
        $q = $bot->conn->prepare("UPDATE anime SET nomi_alternativi = :altern, mal_id = :mal_id WHERE id = :id");
        $q->bindParam(":altern", $nomi_alternativi);
        $q->bindParam(":mal_id", $mal_id);
        $q->bindParam(":id", $anime_id);
        $q->execute();
        foreach($generi as $ad){
            $ad = str_replace(["-", " "], ["", ""], $ad);
            $bot->conn->query("INSERT INTO anime_genere SET anime_id = '$anime_id', genere_id = (SELECT id FROM generi WHERE nome LIKE '%$ad%')");
        }
        $menu[] = [["text" => "↗️ APRI SCHEDA ANIME", "callback_data" => "view:anime_$anime_id"."_1"]];
        $menu[] = [["text" => "◀️ TORNA ALLA HOME", "callback_data" => "home:home"]];
        $bot->sendAnimeToChannel($anime_id, -1001246689088);
        $bot->reply("<b>Anime caricato con successo!</b>\nAdesso inviami gli episodi:", $menu);
        $bot->page("setting:upload_$anime_id");
    }
}
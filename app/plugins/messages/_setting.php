<?php

//Upload new episode
if($bot->checkPage("upload") && isset($bot->video)){
    $anime_id = explode("_", $bot->page)[1];
    $q = $bot->conn->prepare("INSERT INTO episodes SET anime_id = :anime, fileID = :fileID");
    $q->bindParam(":anime", $anime_id);
    $q->bindParam(":fileID", $bot->file_id);
    $q->execute();
    $episode_id = $bot->conn->lastInsertId();
    $episode_number = $bot->conn->query("SELECT id FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1")->rowCount();
    //Save video in anime log channel
    $anime_info = $bot->conn->query("SELECT nome, stagione FROM anime WHERE id = '$anime_id'")->fetch();
    $bot->sendVideo(-1001367628065, $bot->file_id, "Anime: ".$anime_info["nome"].str_replace("\nStagione: 0","","\nStagione: ".$anime_info["stagione"])."\nEpisodio: ".$episode_number);
    $is_simulcast = $bot->conn->query("SELECT poster, name FROM anime_simulcast WHERE anime_id = '$anime_id'");
    if($is_simulcast->rowCount()) {
        $bot->page("home:home");
        $bot->deleteMessage($bot->userID, $bot->msgid - 1);
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $simulcast = $is_simulcast->fetch();
        $poster = $simulcast["poster"];
        $nome = $simulcast["name"];
        $q = $bot->conn->query("SELECT chat_id FROM simulcast_notify WHERE anime_id = '$anime_id'");
        $episode_offset = $episode_number - 1;
        $menu1[] = [["text" => "⭐️ GUARDA ORA ", "callback_data" => "player:view_$episode_offset"."_$anime_id"."_0"]];
        foreach($q as $ad){ //Notifica a tutti coloro che seguono l'anime
            $bot->si($ad["chat_id"], $poster, "<b>$nome</> disponibile ora l'episodio <b>$episode_number</b>", $menu1);
            usleep(300000);
        }
        if($episode_number > 1){
            $menu[] = [["text" => "⭐️ GUARDA ORA ", "url" => "t.me/myanimetvbot?start=ep_$episode_offset"."_$anime_id"]];
            $bot->sendStick(-1001196557835, "CAACAgQAAxkBAAECn-tePYyWjApO0tANmNtfakX-dma1EgACEwAD3VA4GM3t39MKCGhHGAQ");
            $bot->si(-1001196557835, $poster, "<b>$nome</> disponibile ora l'episodio <b>$episode_number</b>", $menu);
        }else {
            $bot->conn->query("UPDATE anime_simulcast SET status = 1 WHERE anime_id = '$anime_id'");
            $bot->sendAnimeToChannel($anime_id, -1001196557835, "\n\n<b>🔔 NUOVO SIMULCAST</b>");
        }
        $menu2[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:home_$anime_id"."_1"]];
        $bot->reply("<b>✅ EPISODIO $episode_number CARICATO</b>", $menu2);
    }else{
        $menu[] = [["text" => "❌ ANNULLA ❌", "callback_data" => "episodes:cancell_$episode_id"]];
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:home_$anime_id"."_0"]];
        $bot->reply("<b>✅ EPISODIO $episode_number CARICATO</b>", $menu, false, $bot->msgid);
    }
}

//Edit info
elseif($bot->checkPage("title")){
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
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->reply("Modifica effettuata con successo!", $menu);
    $bot->page("setting:home");
}

elseif($bot->checkPage("simulcastTitle")){
    $anime_id = explode("_", $bot->page)[1];
    $q = $bot->conn->prepare("UPDATE anime_simulcast SET name = :nome WHERE anime_id = :id");
    $q->bindParam(":nome", $bot->msg);
    $q->bindParam(":id", $anime_id);
    $q->execute();
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->reply("Modifica effettuata con successo!", $menu);
    $bot->page("setting:home");
}

elseif($bot->checkPage("simulcastBanner") && isset($bot->photo)){
    $e = explode("_", $bot->page);
    $anime_id = $e[1];
    $option = $e[2];
    $name = "banner_".date("ymdhis").".png";
    file_get_contents("https://bots.myanimetv.org/myanimetv/photoshop/?img=".$bot->getFile($bot->file_id)."&name=$name");
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    if(!$option){ 
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:home_$anime_id"."_1"]]; 
        $caption = "<b>Anteprima immagine</b>";
        $query = "UPDATE anime_simulcast SET poster = :poster WHERE anime_id = :id";
        $bot->page("setting:home");
    } 
    else { 
        $caption = "Ok, adesso invia il titolo del simulcast:"; $menu = null;
        $query = "INSERT INTO anime_simulcast SET poster = :poster, anime_id = :id";
        $bot->page("setting:simulcastTitle_$anime_id");
    }
    $photo = $bot->si($bot->userID,"https://bots.myanimetv.org/myanimetv/resources/img/$name", $caption, $menu)["result"]["photo"];
    $file_id = $photo[count($photo) - 1]["file_id"];
    unlink("/var/www/html/bots/myanimetv/resources/img/$name");
    $q = $bot->conn->prepare($query);
    $q->bindParam(":poster", $file_id);
    $q->bindParam(":id", $anime_id);
    $q->execute();
}

elseif($bot->checkPage("poster") && isset($bot->photo)){
    $anime_id = explode("_", $bot->page)[1];
    $msgid = $bot->si(-1001328992269, $bot->file_id)["result"]["message_id"];
    $bot->conn->query("UPDATE anime SET poster = '$bot->file_id', poster_msgid = '$msgid' WHERE id = '$anime_id'");
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->reply("Modifica effettuata con successo!", $menu);
    $bot->page("setting:home");
}

elseif($bot->checkPage("durata")){
    $anime_id = explode("_", $bot->page)[1];
    $q = $bot->conn->prepare("UPDATE anime_info SET durata_ep = :durata WHERE anime_id = :id");
    $q->bindParam(":durata", $bot->msg);
    $q->bindParam(":id", $anime_id);
    $q->execute();
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->reply("Modifica effettuata con successo!", $menu);
    $bot->page("setting:home");
}

elseif($bot->checkPage("stagione")){
    $anime_id = explode("_", $bot->page)[1];
    if(is_numeric($bot->msg)){
        $bot->conn->query("UPDATE anime SET stagione = '$bot->msg' WHERE id = '$anime_id'");
        $bot->deleteMessage($bot->userID, $bot->msgid - 1);
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
        $bot->reply("Modifica effettuata con successo!", $menu);
        $bot->page("setting:home");
    }
}
                                         
elseif($bot->checkPage("ordine")){
    $anime_id = explode("_", $bot->page)[1];
    if(is_numeric($bot->msg)){
        $bot->conn->query("UPDATE anime_groups SET stagione = '$bot->msg' WHERE anime_id = '$anime_id'");
        $bot->deleteMessage($bot->userID, $bot->msgid - 1);
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
        $bot->reply("Modifica effettuata con successo!", $menu);
        $bot->page("setting:home");
    }
}

elseif($bot->checkPage("episodiNr")){
    $anime_id = explode("_", $bot->page)[1];
    if(is_numeric($bot->msg)){
        $bot->conn->query("UPDATE anime_info SET episodi = '$bot->msg' WHERE anime_id = '$anime_id'");
        $bot->deleteMessage($bot->userID, $bot->msgid - 1);
        $bot->deleteMessage($bot->userID, $bot->msgid);
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
        $bot->reply("Modifica effettuata con successo!", $menu);
        $bot->page("setting:home");
    }
}

elseif($bot->checkPage("aired")){
    $anime_id = explode("_", $bot->page)[1];
    $mese = ["gennaio", "febbraio", "marzo", "maggio", "aprile", "giugno", "luglio", "agosto", "settembre", "ottobre", "novembre", "dicembre"];
    $e = explode(" ", $bot->msg);
    $d = $e[0]; $m = str_replace($mese, ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"], strtolower($e[1])); $y = $e[2];
    $date = "$y-$m-$d";
    $bot->conn->query("UPDATE anime_info SET aired_on = '$date', uscita = '$bot->msg' WHERE anime_id = '$anime_id'");
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->reply("Modifica effettuata con successo!", $menu);
    $bot->page("setting:home");
}

elseif($bot->checkPage("trama")){
    $anime_id = explode("_", $bot->page)[1];
    $nodes = [
        [
            "tag" => "p",
            "children" => [
                str_replace(["&quot;", "&#039;"], ["\"", "'"], $bot->msg)
            ]
        ]
    ];
    $title = $bot->conn->query("SELECT nome FROM anime WHERE id = '$anime_id'")->fetch()["nome"];
    $token = "6e6bb3d16c6201fe33efdebb37bf7b912a334303d900825f0411a2f41912";
    $trama_url = json_decode(file_get_contents("https://api.telegra.ph/createPage?access_token=$token&title=TRAMA+".strtoupper(str_replace([" ", "'"],["+", ""], $title))."&author_name=MY+ANIME+TV&content=".urlencode(json_encode($nodes))."&return_content=true"), true)["result"]["url"];
    $q = $bot->conn->prepare("UPDATE anime_info SET trama_url = :trama_url, trama = :trama WHERE anime_id = :anime_id");
    $q->bindParam(":trama_url", $trama_url);
    $q->bindParam(":trama", $bot->msg);
    $q->bindParam(":anime_id", $anime_id);
    $q->execute();
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->reply("Modifica effettuata con successo! $trama_url", $menu);
    $bot->page("setting:home");
}

elseif($bot->checkPage("trailer")){
    $anime_id = explode("_", $bot->page)[1];
    $q = $bot->conn->prepare("UPDATE anime_info SET trailer = :trailer WHERE anime_id = :id");
    $q->bindParam(":trailer", $bot->msg);
    $q->bindParam(":id", $anime_id);
    $q->execute();
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->reply("Modifica effettuata con successo!", $menu);
    $bot->page("setting:home");
}



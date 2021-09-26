<?php

if($bot->data("group")){
    $e = explode("_", $bot->cbdata);
    $check = $e[2];
    if(isset($check)){
        if($bot->isfollower(-1001196557835)){
            $verified = true;
            $q = $bot->conn->prepare("DELETE FROM antiflood WHERE chat_id = :chat");
            $q->bindParam(":chat", $bot->userID);
            $q->execute();
        }else{
            $q = $bot->conn->prepare("SELECT COUNT(chat_id) AS tot FROM antiflood WHERE chat_id = :user AND update_on > NOW() - 1 DAY");
            $q->bindParam(":user", $bot->userID);
            $q->execute();
            $control = $q->fetch()["tot"];
            if($control > 2){
                $verified = false;
            }else{
                $verified = true;
                $q = $bot->conn->prepare("INSERT INTO antiflood SET chat_id = :user");
                $q->bindParam(":user", $bot->userID);
                $q->execute();
                $q2 = $bot->conn->prepare("DELETE FROM antiflood WHERE chat_id = :user AND update_on < NOW() - INTERVAL 1 DAY");
                $q2->bindParam(":user", $bot->userID);
                $q2->execute();
            }
        }
    }else{
        $verified = true;
    }
    if($verified){
        $anime_id = $e[1];
        $q = $bot->conn->query("SELECT id FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1 ORDER by upload_on");
        $tot_ep = $q->rowCount();
        if($tot_ep > 0){
            $x = 0;
            $y = 0;
            if($tot_ep > 50){
                $end = 0;
                while($end < $tot_ep){ //Ragruppamento episodi 1-50, 51-101 ecc...
                    $list_index = $end;
                    $start = $end + 1;
                    $end += 50;
                    if($end <= $tot_ep){
                        $button =  ["text" => "$start-$end", "callback_data" => "episodes:list_$list_index"."_$anime_id"];
                    }else{
                        $button =  ["text" => "$start-".$tot_ep, "callback_data" => "episodes:list_$list_index"."_$anime_id"];
                    }
                    if($x < 4){
                        $x++;
                    }else{
                        ++$y;
                        $x = 1;
                    } 
                    $menu[$y][] = $button;
                }
            }else{ //Ragruppamento normale 1, 2, 3... in caso che abbia meno di 51 ep
                $i = 0;
                foreach($q as $ad){
                    if($i < 9){
                        $ep = $i + 1;
                        $ep = "0$ep";
                    }else{
                        $ep = $i + 1;
                    }
                    $button = ["text" => $ep, "callback_data" => "player:view_$i"."_$anime_id"."_1_1"];
                    if($x < 4){
                        $x++;
                    }else{
                        ++$y;
                        $x = 1;
                    } 
                    $menu[$y][] = $button;
                    $i++;
                }
                if($tot_ep < 26 && $tot_ep > 1){
                    $menu[] = [["text" => "🔀 INVIA TUTTI", "callback_data" => "player:multiple_$anime_id"]];
                }
            }
            $specials = $bot->conn->query("SELECT COUNT(fileID) as TOT FROM episodes WHERE anime_id = '$anime_id' AND tipo = 0")->fetch()["TOT"];
            if($specials){
                $menu[] = [["text" => "✨ EPISODI SPECIALI", "callback_data" => "episodes:specials_$anime_id"]];
            }
            $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "view:anime_$anime_id"."_1"]];
            if(!$check){
                $bot->edit("<b>Seleziona il numero dell'episodio ⤵️</b>", $menu);
            }else{
                $bot->deleteMessage($bot->chatID, $bot->msgid);
                $bot->reply("<b>Seleziona il numero dell'episodio ⤵️</b>", $menu);
            }
        }else{
            $bot->alert("Al momento gli episodi di questo anime non sono disponibili!");
        }
    }else{
        $menu2[] = [["text" => "🔓 SBLOCCA UTILIZZO ILLIMITATO", "url" => "t.me/myanimetv"]];
        $img = $bot->setting["banner"]["errore"];
        $bot->reply("<a href='$img'>&#8203;</>❗️<b>ERRORE: Permesso Negato.</>\n\n⚠️ Per oggi hai raggiunto l'utilizzo massimo di questa sezione\n\n❔ Riprova domani, oppure iscriviti al canale ufficiale del bot per sbloccare l'utilizzo illimitato!", $menu2);
    }
}


elseif($bot->data("list")){
    $i = explode("_", $bot->cbdata);
    $anime_id = $i[2];
    $id = $i[1];
    $new_id = $id + 50;
    $last_id = $id - 50;
    $q = $bot->conn->query("SELECT id FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1  ORDER by upload_on LIMIT $id, 50");
    $b = $bot->conn->query("SELECT id FROM episodes WHERE anime_id = '$anime_id' AND tipo = 1  ORDER by upload_on LIMIT $new_id, 1")->rowCount();
    $i = $id;
    $x = 0;
    $y = 0;
    foreach($q as $ad){
        if($i < 9){
            $ep = $i + 1;
            $ep = "0$ep";
        }else{
            $ep = $i + 1;
        }
        $button =  ["text" => $ep, "callback_data" => "player:view_".$i."_$anime_id"."_1"];
        if($x < 5){
            $x++;
        }else{
            ++$y;
            $x = 1;
        }
        $menu[$y][] = $button;
        $i++;
    }
    if($b > 0){
        if($id == 0){
            $menu[] = [["text" => "ᐅ ᐅ ᐅ", "callback_data" => "episodes:list_".$new_id."_$anime_id"]];
        }else{
            $menu[] = [["text" => "ᐊ ᐊ ᐊ", "callback_data" => "episodes:list_".$last_id."_$anime_id"],["text" => "ᐅ ᐅ ᐅ", "callback_data" => "episodes:list_".$new_id."_$anime_id"]];
        }
    }else{
        if($id > 0){
            $menu[] = [["text" => "ᐊ ᐊ ᐊ", "callback_data" => "episodes:list_$last_id"."_$anime_id"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "episodes:group_$anime_id"]];
    $bot->edit("<b>Seleziona il numero dell'episodio ⤵️</>", $menu);
}

elseif($bot->data("specials")){
    $bot->page("search:specials");
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $delete = $e[2];
    $q = $bot->conn->query("SELECT title FROM episodes WHERE anime_id = '$anime_id' AND tipo = 0");
    $i = 1;
    $call = 0;
    foreach($q as $row){
        if(empty($row["title"])) { $title = "SPECIAL 0$i"; }
        else {$title = $row["title"];}
        $menu[] = [["text" => $title, "callback_data" => "player:special_$anime_id"."_$call"."_1"]];
        $i++; $call++;
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "episodes:group_$anime_id"]];
    if($delete == 1){
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $bot->reply("<b>Seleziona l'episodio speciale che vuoi guardare:</b>", $menu);
    }else{
        $bot->edit("<b>Seleziona l'episodio speciale che vuoi guardare:</b>", $menu);
    }
}

//EDIT EPISODE
elseif($bot->data("edit")){
    $id = explode("_", $bot->cbdata)[1];
    $bot->page("admin:editEpisodeVideo_$id");
    $bot->alert("Ok, invia il nuovo episodio:");
}

elseif($bot->data("title")){
    $id = explode("_", $bot->cbdata)[1];
    $bot->page("admin:editEpisodeTitle_$id");
    $bot->alert("Ok, invia il titolo dell'episodio:");
}

elseif($bot->data("delete")){
    $bot->cPage("");
    $id = explode("_", $bot->cbdata)[1];
    if($bot->checkPage("admin:delete")){
        $bot->conn->query("DELETE FROM episodes WHERE id = '$id'");
        $bot->alert("Episodio eliminato!");
        $bot->page("search:home");
    }else{
        $bot->alert("Clicca un'altra volta per confermare");
        $bot->page("admin:delete");
    }
}

elseif($bot->data("upload")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $bot->page("setting:upload_$anime_id");
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:home_$anime_id"]];
    $bot->edit("Ok, inviami l'episodio:", $menu);
}

elseif($bot->data("allRemove")) {
    $anime_id = explode("_", $bot->cbdata)[1];
    if($bot->cPage("episodes:allRemove")){
        $bot->page("search:results");
        $bot->conn->query("DELETE FROM episodes WHERE anime_id = '$anime_id'");
        $bot->alert("Episodi rimossi!");
    }else{
        $bot->page("episodes:allRemove");
        $bot->alert("Clicca un'altra volta per confermare");
    }
}

elseif($bot->data("cancell")){
    $id = explode("_", $bot->cbdata)[1];
    $bot->conn->query("DELETE FROM episodes WHERE id = '$id'");
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:home_$anime_id"."_0"]];
    $bot->edit("<b>EPISODIO RIMOSSO</b>", $menu);
}
<?php


if($bot->checkPage("import") && isset($bot->document)){
    $extension = pathinfo($bot->document->file_name)["extension"];
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "profile:home_$bot->userID"]];
    if($extension == 'xml'){
        $obj = simplexml_load_string(file_get_contents($bot->getFile($bot->document->file_id)));
        $i = 0;
        if(isset($obj->anime)){
            $bot->conn->query("DELETE FROM bookmarks WHERE chat_id = '$bot->userID'");
            foreach($obj as $anime){
                if(isset($anime->series_animedb_id) && isset($anime->my_status)){
                    $exists = $bot->conn->prepare("SELECT id FROM anime WHERE mal_id = :id LIMIT 1");
                    $exists->bindParam(":id", $anime->series_animedb_id);
                    $exists->execute();
                    if($exists->rowCount()){
                        $anime_id = $exists->fetch()["id"];
                        $list = $anime->my_status;
                        switch($list){
                            case "Completed": $list_id = 1; break;
                            case "Watching": $list_id = 2; break;
                            case "Plan to Watch": $list_id = 3; break;
                        }
                        $q = $bot->conn->prepare("INSERT INTO bookmarks SET anime_id = :anime, list_id = :list, chat_id = :chat");
                        $q->bindParam(":anime", $anime_id);
                        $q->bindParam(":list", $list_id);
                        $q->bindParam(":chat", $bot->userID);
                        $q->execute();
                        $i++;
                    }
                }
            }
            $tot = count($obj) - 1;
            $bot->reply("✅ <b>$i/$tot</b> anime sono stati aggiunti con successo!", $menu);
        }else{
            $bot->reply("⚠️ Qualcosa è andato storto durante la lettura del file!", $menu);
        }
    }else{
        $bot->reply("⚠️ Il file deve avere come estensione \"<b>.xml</b>\"!\nRiprova:", $menu);
    }
}
<?php

if($bot->data("option")) {
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[2];
    $option = $e[1];
    for($i = 0; $i < 3; $i++) {
        $menu[] = $bot->menu[$i];
    }
    if($option == 1){
        $menu[] = [["text" => "📌 🔽 BOOKMARK", "callback_data" => "bookmark:option_0_$anime_id"]];
        $menu[] = [["text" => "✅ COMPLETED", "callback_data" => "bookmark:complete_$anime_id"]];
        $menu[] = [["text" => "🗓 PLAN TO WATCH", "callback_data" => "bookmark:plntowatch_$anime_id"]];
        $menu[] = [["text" => "👀 WATCHING", "callback_data" => "bookmark:watching_$anime_id"]];
        for($i = 4; $i < count($bot->menu); $i++) {
            $menu[] = $bot->menu[$i];
        }
    }else{
        $menu[] = [["text" => "📌 BOOKMARK", "callback_data" => "bookmark:option_1_$anime_id"]];
        for($i = 7; $i < count($bot->menu); $i++) {
            $menu[] = $bot->menu[$i];
        }
    }
    $bot->editButton($menu);
}

elseif($bot->data("complete")){
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $option = $e[2];
    $bot->conn->query("DELETE FROM bookmarks WHERE anime_id = '$anime_id' AND chat_id = '$bot->userID'");
    $bot->conn->query("INSERT INTO bookmarks SET anime_id = '$anime_id', chat_id = '$bot->userID', list_id = 1");
    if(isset($option)){
        if($bot->isadmin()){
            $menu[] = $bot->menu[0];
            for($i = 2; $i < count($bot->menu); $i++){
                $menu[] = $bot->menu[$i];
            }
        }else{
            for($i = 1; $i < count($bot->menu); $i++){
                $menu[] = $bot->menu[$i];
            }
        }
    }else{
        $i = 0;
        while($i < count($bot->menu)){
            if($i < 4 || $i > 6){
                $menu[] = $bot->menu[$i];
            }
            $i++;
        }
        $menu[3] = [["text" => "📌 BOOKMARK", "callback_data" => "bookmark:option_1_$anime_id"]];
    }
    $bot->editButton($menu);
    $bot->alert("🔵 Aggiunto alla tua \"Completed\" list!");
}


elseif($bot->data("watching")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $bot->conn->query("DELETE FROM bookmarks WHERE anime_id = '$anime_id' AND chat_id = '$bot->userID'");
    $bot->conn->query("INSERT INTO bookmarks SET anime_id = '$anime_id', chat_id = '$bot->userID', list_id = 2");
    $i = 0;
    while($i < count($bot->menu)){
        if($i < 4 || $i > 6){
            $menu[] = $bot->menu[$i];
        }
        $i++;
    }
    $menu[3] = [["text" => "📌 BOOKMARK", "callback_data" => "bookmark:option_1_$anime_id"]];
    $bot->editButton($menu);
    $bot->alert("🟢 Aggiunto alla tua \"Watching\" list!");
}

elseif($bot->data("plntowatch")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $bot->conn->query("DELETE FROM bookmarks WHERE anime_id = '$anime_id' AND chat_id = '$bot->userID'");
    $bot->conn->query("INSERT INTO bookmarks SET anime_id = '$anime_id', chat_id = '$bot->userID', list_id = 3");
    $i = 0;
    while($i < count($bot->menu)){
        if($i < 4 || $i > 6){
            $menu[] = $bot->menu[$i];
        }
        $i++;
    }
    $menu[3] = [["text" => "📌 BOOKMARK", "callback_data" => "bookmark:option_1_$anime_id"]];
    $bot->editButton($menu);
    $bot->alert("⚪️ Aggiunto alla tua \"Plan-to-watch\" list!");
}

elseif($bot->data("preferred")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT anime_id FROM preferreds WHERE anime_Id = '$anime_id' AND chat_id = '$bot->userID'");
    $menu[] = [$bot->menu[0][0], $bot->menu[0][1]];
    if($q->rowCount()){
        $bot->conn->query("DELETE FROM preferreds WHERE anime_id = '$anime_id' AND chat_id = '$bot->userID'");
        $bot->alert("💔 Rimosso dalla tua \"Preferred\" list!");
        $menu[0][2] = ["text" => "💔", "callback_data" => "bookmark:preferred_$anime_id"];
    }else{
        $bot->conn->query("INSERT INTO preferreds SET anime_id = '$anime_id', chat_id = '$bot->userID'");
        $bot->alert("❤️ Aggiunto alla tua \"Preferred\" list!");
        $menu[0][2] = ["text" => "❤️", "callback_data" => "bookmark:preferred_$anime_id"];
    }
    if(isset($bot->menu[0][3])){
        $menu[0][] = $bot->menu[0][3];
    }
    for($i = 1; $i < count($bot->menu); $i++){
        $menu[] = $bot->menu[$i];
    }
    $bot->editButton($menu);
}

elseif($bot->data("notify")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT anime_id FROM simulcast_notify WHERE anime_Id = '$anime_id' AND chat_id = '$bot->userID'");
    $menu[] = [$bot->menu[0][0], $bot->menu[0][1], $bot->menu[0][2]];
    if($q->rowCount()){
        $bot->conn->query("DELETE FROM simulcast_notify WHERE anime_id = '$anime_id' AND chat_id = '$bot->userID'");
        $bot->alert("🔕 Le notifiche di questo simulcast sono silenziate");
        $menu[0][3] = ["text" => "🔕", "callback_data" => "bookmark:notify_$anime_id"];
    }else{
        $bot->conn->query("INSERT INTO simulcast_notify SET anime_id = '$anime_id', chat_id = '$bot->userID'");
        $bot->alert("🔔 Le notifiche di questo simulcast sono state attivate!");
        $menu[0][3] = ["text" => "🔔", "callback_data" => "bookmark:notify_$anime_id"];
    }
    for($i = 1; $i < count($bot->menu); $i++){
        $menu[] = $bot->menu[$i];
    }
    $bot->editButton($menu);
}
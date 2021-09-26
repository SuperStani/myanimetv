<?php

if($bot->data('home')){
    $menu[] = [["text" => '➕ NUOVO GRUPPO', 'callback_data' => 'group:newgroup']];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $bot->edit("Digita il nome del gruppo che vuoi cercare, oppure creane uno nuovo!", $menu);
    $bot->page("group:q");
}

elseif($bot->data("show")){
    $group_id = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT anime.nome, anime.id, anime_groups.stagione FROm anime INNER JOIN anime_groups ON anime.id = anime_groups.anime_id WHERE anime_groups.group_id = '$group_id' ORDER by anime_groups.stagione ASC");
    $text = "<b>Lista:</b>\n\n";
    foreach($q as $anime){
        if($anime["stagione"] == 0){ $stagione = "";} else{$stagione = "S".$anime["stagione"];}
        $text = $text."<a href='t.me/myanimetvbot?start=animeID_".$anime["id"]."'>".$anime["nome"]." $stagione</a>\n";
    }
    $menu[] = [["text" => '➕ AGGIUNGI ANIME', 'callback_data' => 'group:newanime_'.$group_id]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "group:home"]];
    $bot->edit($text, $menu);
    $bot->page("");
}

elseif($bot->data("newanime")){
    $group_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "group:show_$group_id"]];
    $bot->edit("OK, inviami l'ID del nuovo anime:", $menu);
    $bot->page("group:newanime_$group_id");
}

elseif($bot->data("newgroup")){
    $group_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "group:home"]];
    $bot->edit("OK, inviami il nome del gruppo:", $menu);
    $bot->page("group:newgroup");
}
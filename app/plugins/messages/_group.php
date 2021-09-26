<?php

if($bot->checkPage("q")){
    $search = '%'.$bot->msg.'%';
    $q = $bot->conn->prepare("SELECT * FROM group_names WHERE name LIKE :s");
    $q->bindParam(':s', $search);
    $q->execute();
    $i = 1; $x = 0; $y = 0;
    $text = "<b>RISULTATI:</b>\n\n";
    foreach($q as $group){
        $text = $text."$i\t| <b>".$group["name"]."</b>\n";
        if($x < 5){
            $x++;
        }else{
            $x = 1;
            $y++;
        }
        $menu[$y][] = ['text' => $i, 'callback_data' => 'group:show_'.$group["id"]];
        $i++;
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "group:home"]];
    $bot->deleteMessage($bot->chatID,$bot->msgid);
    $bot->reply($text, $menu);
}

elseif($bot->checkPage("newanime")){
    $group_id = explode("_", $bot->page)[1];
    $bot->deleteMessage($bot->chatID,$bot->msgid);
    if(is_numeric($bot->msg)){
        $anime = $bot->conn->query("SELECT nome, stagione FROM anime WHERE id = '$bot->msg'")->fetch();
        $stagione = $anime['stagione'];
        $bot->conn->query("INSERT INTO anime_groups SET anime_id = '$bot->msg', group_id = '$group_id', stagione = '$stagione'");
        $menu[] = [["text" => '➕ AGGIUNGI ANIME', 'callback_data' => 'group:newanime_'.$group_id]];
        $menu[] = [["text" => "◀️ TORNA AL GRUPPO", "callback_data" => "group:show_".$group_id]];
        $bot->reply("<b>".$anime["nome"]." S$stagione</b> aggiunto con successo!", $menu);
        $bot->page("");
    }
    
}

elseif($bot->checkPage("newgroup")){
    $q = $bot->conn->prepare("INSERT INTO group_names SET name = :name");
    $q->bindParam(':name', $bot->msg);
    $q->execute();
    $id = $bot->conn->lastInsertId();
    $bot->deleteMessage($bot->chatID,$bot->msgid);
    $menu[] = [["text" => '➕ AGGIUNGI ANIME', 'callback_data' => 'group:newanime_'.$id]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "group:home"]];
    $bot->reply("Gruppo \"<b>$bot->msg\"</b> creato con successo!", $menu);
    $bot->page("");
}
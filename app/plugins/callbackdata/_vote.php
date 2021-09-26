<?php

if($bot->data("like")){
    //$bot->alert("gg");
    $anime_id = explode("_", $bot->cbdata)[1];
    $verify = $bot->conn->prepare("SELECT type FROM votes WHERE anime_id = :anime AND chat_id = :chat_id");
    $verify->bindParam(":anime", $anime_id);
    $verify->bindParam(":chat_id", $bot->userID);
    $verify->execute();
    if($verify->rowCount() < 1){ //New Vote
        $q = $bot->conn->prepare("INSERT INTO votes SET anime_id = :anime, type = 1, chat_id = :chat_id");
        $q->bindParam(":anime", $anime_id);
        $q->bindParam(":chat_id", $bot->userID);
        $q->execute();
        $bot->alert("👍", false);
    }else{
        if($verify->fetch()["type"] == 1){ //Remove Vote
            $q = $bot->conn->prepare("DELETE FROM votes WHERE anime_id = :anime AND chat_id = :chat_id");
            $q->bindParam(":anime", $anime_id);
            $q->bindParam(":chat_id", $bot->userID);
            $q->execute();
            $bot->alert("🚫 Il tuo voto è stato rimosso", false);
        }else{ //Change Vote
            $q = $bot->conn->prepare("UPDATE votes SET type = 1 WHERE anime_id = :anime AND chat_id = :chat_id");
            $q->bindParam(":anime", $anime_id);
            $q->bindParam(":chat_id", $bot->userID);
            $q->execute();
            $bot->alert("👍", false);
        }
    }
    //...Get like/dislike
    $like = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 1 AND anime_id = '$anime_id'")->fetch()["tot"];
    $dislike = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 0 AND anime_id = '$anime_id'")->fetch()["tot"];
    $menu[] = [["text" => "👍 $like", "callback_data" => "vote:like_$anime_id"], ["text" => "👎 $dislike", "callback_data" => "vote:dislike_$anime_id"]];
    for($i = 2; $i < count($bot->menu[0]); $i++){
        $menu[0][] = $bot->menu[0][$i];
    }
    for($i = 1; $i < count($bot->menu); $i++){
        $menu[] = $bot->menu[$i];
    }
    $bot->editButton($menu);
}

elseif($bot->data("dislike")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $verify = $bot->conn->prepare("SELECT type FROM votes WHERE anime_id = :anime AND chat_id = :chat_id");
    $verify->bindParam(":anime", $anime_id);
    $verify->bindParam(":chat_id", $bot->userID);
    $verify->execute();
    if($verify->rowCount() < 1){ //New Vote
        $q = $bot->conn->prepare("INSERT INTO votes SET anime_id = :anime, type = 0, chat_id = :chat_id");
        $q->bindParam(":anime", $anime_id);
        $q->bindParam(":chat_id", $bot->userID);
        $q->execute();
        $bot->alert("👎", false);
    }else{
        if($verify->fetch()["type"] == 0){ //Remove Vote
            $q = $bot->conn->prepare("DELETE FROM votes WHERE anime_id = :anime AND chat_id = :chat_id");
            $q->bindParam(":anime", $anime_id);
            $q->bindParam(":chat_id", $bot->userID);
            $q->execute();
            $bot->alert("🚫 Il tuo voto è stato rimosso", false);
        }else{ //Change Vote
            $q = $bot->conn->prepare("UPDATE votes SET type = 0 WHERE anime_id = :anime AND chat_id = :chat_id");
            $q->bindParam(":anime", $anime_id);
            $q->bindParam(":chat_id", $bot->userID);
            $q->execute();
            $bot->alert("👎", false);
        }
    }
    //...Get like/dislike
    $like = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 1 AND anime_id = '$anime_id'")->fetch()["tot"];
    $dislike = $bot->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 0 AND anime_id = '$anime_id'")->fetch()["tot"];
    $menu[] = [["text" => "👍 $like", "callback_data" => "vote:like_$anime_id"], ["text" => "👎 $dislike", "callback_data" => "vote:dislike_$anime_id"]];
    for($i = 2; $i < count($bot->menu[0]); $i++){
        $menu[0][] = $bot->menu[0][$i];
    }
    for($i = 1; $i < count($bot->menu); $i++){
        $menu[] = $bot->menu[$i];
    }
    $bot->editButton($menu);
}
<?php

if($bot->msg == "/start"){
    $bot->page("home:start");
    $query = "SELECT 
	            (SELECT COUNT(id) FROM anime WHERE stagione < 2) AS tot_anime, 
                COUNT(episodes.anime_id) AS tot
              FROM episodes";
    $info = $bot->conn->query($query);
    $info = $info->fetch();
    $episodi_totali = $info["tot"];
    $anime_online = $info["tot_anime"];
    if($bot->isadmin()){
        $menu[] = [["text" => "ā NUOVO ANIME", "callback_data" => "post:newanime"]];
        $menu[] = [["text" => "š LOGIN", "login_url" => ["url" => "https://dash.myanimetv.org/check_auth/", "bot_username" => "myanimetvtestbot"]], ["text" => "āļø GROUPS", "callback_data" => "group:home"]];
        $menu[] = [["text" => "ā© GUARDA ANIME", "callback_data" => "simulcast:scroll_0_1"]];
        $menu[] = [["text" => "š¤ PROFILO", "callback_data" => "profile:home_$bot->userID"],["text" => "š STATS", "callback_data" => "admin:stats"]];
        $menu[] = [["text" => "š CLASSIFICHE", "callback_data" => "top:home"]];
    }else{
        $menu[] = [["text" => "ā© GUARDA ANIME", "callback_data" => "simulcast:scroll_0_1"]];
        $menu[] = [["text" => "š¤ PROFILO", "callback_data" => "profile:home_$bot->userID"],["text" => "š£ SUPPORTO", "callback_data" => "home:support"]];
        $menu[] = [["text" => "š CLASSIFICHE", "callback_data" => "top:home"],["text" => "š„ RICHIEDI", "callback_data" => "home:richiedi"]];
        $menu[] = [["text" => "š MATV MARKET", "callback_data" => "market:home"]];
    }
    $img = $bot->setting["banner"]["benvenuto"];
    $text = "<a href='$img'>&#8203;</a>š Benvenuto/a <b>$bot->nome</b>!\n\nš„ | Iscritti: <b>".$bot->getFollower()."</b>\nš® | Anime online: <b>$anime_online</b>\nš„ | Episodi totali: <b>$episodi_totali</b>\nšØāš» | <b>Developed by @SuperStani</b>\n\n<i>š Seleziona un'opzione qua sotto:</i>";
    $bot->reply($text, $menu);
}

elseif($bot->page == "richiedi"){
    $nome = "$bot->msg%";
    $q = $bot->conn->prepare("SELECT nome FROM anime WHERE nome LIKE :nome OR nomi_alternativi LIKE :nome");
    $q->bindParam(":nome", $nome);
    $q->execute();
    $menu[] = [["text" => "āļø INDIETRO", "callback_data" => "home:home"]];
    if($q->rowCount() < 1) {
        $q2 = $bot->conn->prepare("INSERT INTO richieste SET by_user_id = :user, nome = :nome");
        $q2->bindParam(":user", $bot->userID);
        $q2->bindParam(":nome", $bot->msg);
        $q2->execute();
        $id = $bot->conn->lastInsertId();
        $menu1[] = [["text" => "ā APPROVA", "callback_data" => "admin:approva_$id"], ["text" => "ā RIFIUTA", "callback_data" => "admin:rifiuta_$id"]];
        $text = "#RICHIESTA\nš¤ <b>Utente:</> <a href='tg://user?id=$bot->userID'>$bot->nome</> [<code>$bot->userID</>]\nš® Anime: <code>$bot->msg</>";
        $msgid = $bot->sm(-1001373617365, $text, $menu1)["result"]["message_id"];
        $bot->conn->query("UPDATE richieste SET msgid = '$msgid' WHERE id = '$id'");
        $bot->conn->query("UPDATE utenti SET richieste = richieste - 1 WHERE chat_id = '$bot->userID'");
        $bot->reply("š§ Richiesta inviata!\n\nā¤ļø <b>Grazie per il supporto</b>", $menu);
    }else {
        $bot->reply("š¾ <b>".strtoupper($q->fetch()["nome"])."</b> ĆØ gia presente nella mia collezione!\nCercalo nell'apposita sezione!", $menu);
    }
    $bot->page("home:home");
}


elseif($bot->checkPage("chat")){
    if($bot->photo){
        $message_id = $bot->si(-1001373617365, $bot->file_id)["result"]["message_id"];
        $bot->sm(-1001373617365, "#SUPPORTO\nš¤ <b>Utente:</> <a href='tg://user?id=$bot->userID'>$bot->nome</> [<code>$bot->userID</>]\n\n<i>Rispondi a questo messaggio per parlare con l'utente!</>", $menu, "html",false, null, $message_id);
    }
    if($bot->msg){
        $bot->sm(-1001373617365,"#SUPPORTO\nš¤ <b>Utente:</> <a href='tg://user?id=$bot->userID'>$bot->nome</> [<code>$bot->userID</>]\n\nš© <b>Messaggio:</> $bot->msg");
    }
}

else{
    $bot->reply("Vuoi cercare un anime?\nDai /start al bot e cercalo nell'apposita sezione!");
}
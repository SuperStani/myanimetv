<?php

if($bot->data("home")){
    $bot->page("home:start");
    $query = "SELECT 
	            (SELECT COUNT(id) FROM anime WHERE stagione < 2) AS tot_anime, 
                COUNT(episodes.anime_id) AS tot
              FROM episodes";
    $info = $bot->conn->prepare($query);
    $info->bindParam(":chat", $bot->userID);
    $info->execute();
    $info = $info->fetch();
    $episodi_totali = $info["tot"];
    $anime_online = $info["tot_anime"];
    if($bot->isadmin()){
        $menu[] = [["text" => "➕ NUOVO ANIME", "callback_data" => "post:newanime"]];
        $menu[] = [["text" => "🔐 LOGIN", "login_url" => ["url" => "https://dash.myanimetv.org/check_auth/", "bot_username" => "myanimetvtestbot"]], ["text" => "⚙️ GROUPS", "callback_data" => "group:home"]];
        $menu[] = [["text" => "⛩ GUARDA ANIME", "callback_data" => "simulcast:scroll_0_1"]];
        $menu[] = [["text" => "👤 PROFILO", "callback_data" => "profile:home_$bot->userID"],["text" => "📊 STATS", "callback_data" => "admin:stats"]];
        $menu[] = [["text" => "🔝 CLASSIFICHE", "callback_data" => "top:home"]];
    }else{
        $menu[] = [["text" => "⛩ GUARDA ANIME", "callback_data" => "simulcast:scroll_0_1"]];
        $menu[] = [["text" => "👤 PROFILO", "callback_data" => "profile:home_$bot->userID"], ["text" => "🗣 SUPPORTO", "callback_data" => "home:support"]];
        $menu[] = [["text" => "🔝 CLASSIFICHE", "callback_data" => "top:home"], ["text" => "📥 RICHIEDI", "callback_data" => "home:richiedi"]];
        $menu[] = [["text" => "🛒 MATV MARKET", "callback_data" => "market:home"]];
    }
    $img = $bot->setting["banner"]["benvenuto"];
    $text = "<a href='$img'>&#8203;</a>👋 Benvenuto/a <b>$bot->nome</>!\n\n👥 | Iscritti: <b>".$bot->getFollower()."</b>\n💮 | Anime online: <b>$anime_online</>\n🔥 | Episodi totali: <b>$episodi_totali</>\n👨‍💻 | <b>Developed by @SuperStani</b>\n\n<i>👇 Seleziona un'opzione qua sotto:</>";
    if(explode("_", $bot->cbdata)[1]){
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $bot->sm($bot->userID, $text, $menu);
    }else{
        $bot->edit($text, $menu);
    }
}

//Richieste
elseif($bot->data("richiedi")){
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $warns = $bot->conn->query("SELECT COUNT(chat_id) AS tot FROM warn_users WHERE chat_id = '$bot->userID'")->fetch()["tot"];
    if($warns < 3){
        $richieste_disponibili = $bot->conn->query("SELECT richieste FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["richieste"];
        if($richieste_disponibili) {
            $img = $bot->setting["banner"]["richiedi"];
            $text = "<a href='$img'>&#8203;</a><b>📥 RICHIEDI</b>\n\n⚠️ <b>ATTENZIONE!</b>\n Prima controlla che l'anime non sia gia\n stato caricato sul bot, e assicurati di\n <b><u>scrivere bene il nome dell'anime</u>\n <u>che vuoi richiedere</u></b>\n\n📨 Hai a disposizione <b>$richieste_disponibili</b> richieste!\n<b>P.S. Non accettiamo richieste di cartoni animati!</b>\n\n<b>Ok, inviami il nome dell'anime che desideri:</b>";
            $bot->page("home:richiedi");
        }else{
            $img = $bot->setting["banner"]["errore2"];
            $text = "<a href='$img'>&#8203;</a>⚠️ <b>ATTENZIONE</b>\n\n<b>Non hai a disposizione nessuna richiesta, puoi andarla a comprarla nel negozio!</b>";
        }
    }else{
        $img = $bot->setting["banner"]["errore2"];
        $text = "<a href='$img'>&#8203;</a>⚠️ <b>ATTENZIONE</b>\n\nSei stato bannato dall'utilizzo di questa sezione!\nPuoi rimuovere il ban comprando lo sblocco dal negozio!\n\n<b>Contatta il supporto per maggiori informazioni!</b>";
    }
    $bot->edit($text, $menu);
}

/*-----------------------
Supporto (chat_live)
-----------------------*/
elseif($bot->data("support")){
    $menu[] = [["text"=>"✖️CHIUDI CHAT✖️","callback_data"=>"home:home"]]; 
    $text = "🔹 Sei entrato in chat con un admin!\n»   Qualsiasi messaggio che invierai\n»   sarà ricevuto e letto da noi!";
    $bot->edit($text, $menu);
    $bot->page("home:chat");
}




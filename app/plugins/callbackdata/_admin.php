<?php

if($bot->data("approva")){
    $id = explode("_", $bot->cbdata)[1];
    $info = $bot->conn->query("SELECT by_user_id, nome FROM richieste WHERE id = '$id'")->fetch();
    $img = $bot->setting["banner"]["accettata"];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $bot->sm($info["by_user_id"], "<a href='$img'>&#8203</>✅ <b>RICHIESTA ACCETTATA</>\n\n💮 Anime: <code>".$info["nome"]."</code>\n📨 <i>La tua richiesta è stata presa\nin considerazione, adesso non\nti resta altro che aspettare!\n\n</i>🎈 <b>Un saluto dallo staff!</b>", $menu);
    $menu1[] = [["text" => "⚙️ CONFERMA", "callback_data" => "admin:completata_$id"], ["text" => "🚫 RIFIUTA", "callback_data" => "admin:rifiuta_$id"]];
    $bot->editButton($menu1);
    $bot->conn->query("UPDATE richieste SET aproved = 1 WHERE id = '$id'");
}

elseif($bot->data("rifiuta")){
    $id = explode("_", $bot->cbdata)[1];
    $info = $bot->conn->query("SELECT by_user_id, nome FROM richieste WHERE id = '$id'")->fetch();
    $img = $bot->setting["banner"]["accettata"];
    $bot->alert("Inviami la motivazione del rifiuto:");
    $bot->page("admin:motivazione_$id");
    $menu1[] = [["text" => "🚫 RIFIUTATA", "callback_data" => "admin:null"]];
    $bot->editButton($menu1);
}

elseif($bot->data("completata")){
    $id = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT nome, by_user_id FROM richieste WHERE id = '$id'")->fetch();
    $bot->conn->query("DELETE FROM richieste WHERE id = '$id'");
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $img = $bot->setting["banner"]["completato"];
    $bot->sm($q["by_user_id"], "<a href='$img'>&#8203;</>✅ <b>RICHIESTA COMPLETATA</>\n\n💮 Anime: <code>".$q["nome"]."</>\n📨 <i>L'anime da te richiesto è stato \ncaricato con successo!\nOra puoi cercarlo nell'apposita sezione.\n\n</i>🎈 <b>Un saluto dallo staff!</b>", $menu);
    $menu1[] = [["text" => "✅ COMPLETATA", "callback_data" => "admin:null"]];
    $bot->editButton($menu1);
}

elseif($bot->data("aggiornaRichieste")){
    $q = $bot->conn->query("SELECT * FROM richieste");
    foreach($q as $ad){
        $richieste[] = "» <a href='https://t.me/c/1373617365/".$ad["msgid"]."'>".$ad["nome"]."</>";
    }
    $menu[] = [["text" => "🔄 AGGIORNA", "callback_data" => "admin:aggiornaRichieste"]];
    $bot->edit("<b>📩 RICHIESTE IN SOSPESO:</>\n\n".implode("\n", $richieste), $menu);
}


elseif($bot->data("stats")){
    $total_users = $bot->conn->query("SELECT COUNT(chat_id) as tot_subs FROM utenti")->fetch()["tot_subs"];
    $users_24ago = $bot->conn->query("SELECT COUNT(chat_id) as tots  FROM utenti WHERE last_update > NOW() - INTERVAL 1 DAY")->fetch()["tots"];
    $users_20ago = $bot->conn->query("SELECT COUNT(chat_id) as tot FROM utenti WHERE last_update > NOW() - INTERVAL 20 MINUTE")->fetch()["tot"];
    $inactive = $bot->conn->query("SELECT COUNT(chat_id) as tot FROM utenti WHERE last_update < NOW() - INTERVAL 2 WEEK")->fetch()["tot"];
    $last_week = $bot->conn->query("SELECT COUNT(chat_id) as tot FROM utenti WHERE last_update > NOW() - INTERVAL 1 WEEK")->fetch()["tot"];
    $offBot = $bot->conn->query("SELECT COUNT(chat_id) as tot FROM utenti WHERE page = 'home:inactive'")->fetch()["tot"];
    $total_active = $total_users-$inactive;
   //$views = $bot->conn->query("SELECT COUNT(anime_id) as views FROM anime_views")->fetch()["views"];
    $text = "Total users: $total_users\nActive users: $total_active\nInactive users: $inactive\nBot-off users: $offBot\n\nActive users last 20 minutes: $users_20ago\nActive users last 24 hours: $users_24ago\nActive users last week: $last_week\n\nTotal anime views: $views";
    $bot->alert($text);
}

elseif($bot->data("sendAnime")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $bot->sendAnimeToChannel($anime_id, -1001196557835);
    $bot->alert("Ho inviato l'anime sul canale!");
}

elseif($bot->data("removeAnime")){
    $anime_id = explode("_", $bot->cbdata)[1];
    if($bot->cPage("admin:removeAnime_$anime_id")){
        $bot->conn->query("DELETE FROM anime_views WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM anime_genere WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM anime_studios WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM anime_cercati WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM anime_groups WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM bookmarks WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM preferreds WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM last_view_episode WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM simulcast_notify WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM anime_simulcast WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM anime_info WHERE anime_id = '$anime_id'");
        $bot->conn->query("DELETE FROM anime WHERE id = '$anime_id'");
        $menu[] = [["text" => "◀️ TORNA ALLA HOME", "callback_data" => "home:home"]];
        $bot->edit("Anime rimosso con successo", $menu);
        $bot->page("home:home");
    }else{
        $bot->alert("Clicca un'altra volta per confermare");
        $bot->page("admin:removeAnime_$anime_id");
    }
}


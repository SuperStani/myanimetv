<?php
//Admin response message
if($bot->reply_to_message && $bot->reply_is_bot){
    preg_match('/[0-9]{5,25}/', $bot->reply_msg, $output_array);
    $user_id = $output_array[0];
    if($user_id){
        $name = $bot->getChat($user_id)["first_name"];
        if($bot->msg == "/fine"){
            $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home"]]; 
            $bot->sm($user_id,"❗️ Chat terminata.\n\nLa tua chat è stata chiusa\nda un amministratore.",$menu);
            $bot->sm($bot->chatID,"🔹 La chat con <a href='tg://user?id=$user_id'>$name</> è stata chiusa!");
            $bot->page("start", $user_id);
        }else if($bot->msg == "/warn"){
            $bot->conn->query("INSERT INTO warn_users SET chat_id = '$user_id', type = 1");
            $total_warn = $bot->conn->query("SELECT chat_id FROM warn_users WHERE chat_id = '$user_id' AND type = 1")->rowCount();
            if($total_warn < 3){
                $bot->sm($bot->chatID, "Ho assegnato $total_warn warn su 3 a <a href='tg://user?id=$user_id'>$name</>");
                $bot->sm($user_id, "Hai appena ricevuto <b>".$total_warn. "</b> warn su 3!\n\n📮 Motivazione:\n<i>Hai richiesto un anime gia presente sul bot scrivendo male il nome, ignorando cosi l'avviso presente nel messaggio principale della sezione richieste</>\n\n⚠️ <b>ATTENZIONE</> se raggiungerai il limite di warn non ti sara piu permesso di richiedere anime!");
            }else{
                $bot->sm($bot->chatID, "Ho bannato <a href='tg://user?id=$user_id'>$name</> dalla sezione richieste!");
                $bot->sm($user_id, "⚠️ <b>ATTENZIONE</>\nSei stato bannato dalla sezione \"RICHIESTE\"\n\n📮 Motivazione:\n<i>Ti sono stati assegnati 3 warn, raggiungendo cosi il limite massimo stabilito!</>\n\n<b>Per maggiori informazioni o incompressioni non esitare a contattarci nella sezione di supporto!</>");
            }
        }else if($bot->msg == "/unbanr"){
            $bot->conn->query("DELETE FROM warn_users WHERE chat_id = '$user_id' AND type = 1");
            $bot->sm($bot->chatID, "Ho rimosso il ban di <a href='tg://user?id=$user_id'>$name</> dalla sezione richieste");
            $bot->sm($user_id, "Sei stato sbannato dalla sezione richieste!");
        }else if($bot->msg == "/ban"){
            $date = date("m-d-Y");
            $bot->page("banned:$date", $user_id);
            $bot->reply("<a href='tg://user?id=$user_id'>$name</a> è stato bannato dal bot!");
            $bot->sm($user_id, "Sei stato bannato dal bot!\nNon potrai piu usare nessuna funzione del bot!");
        }else{
            $bot->page("home:chat", $user_id);
            $bot->sm($user_id,"<b>Admin:</> $bot->msg");
        }
    }
} 

elseif($bot->checkPage("motivazione")){
    $id = explode("_", $bot->page)[1];
    $info = $bot->conn->query("SELECT by_user_id, nome, msgid FROM richieste WHERE id = '$id'")->fetch();
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $img = $bot->setting["banner"]["errore3"];
    $bot->sm($info["by_user_id"], "<a href='$img'>&#8203</a>❌ <b>RICHIESTA RIFIUTATA</b>\n\n💮 | Anime: <code>".$info["nome"]."</>\n📮 | Motivazione:\n$bot->msg\n\n🎈 <b>Un saluto dallo staff!</b>", $menu);
    $bot->conn->query("DELETE FROM richieste WHERE id = '$id'");
    $bot->deleteMessage($bot->chatID, $bot->msgid);
    $bot->sm($bot->chatID, "<b>✅ | Motivazione inviata con successo!</b>\nℹ️ | <i>$bot->msg</i>", null, "html", false, false, $info["msgid"]);
    $bot->page("home:home");
}

elseif($bot->msg == "/richieste"){
    $q = $bot->conn->query("SELECT * FROM richieste");
    foreach($q as $ad){
        $richieste[] = "» <a href='https://t.me/c/1373617365/".$ad["msgid"]."'>".$ad["nome"]."</a>";
    }
    $menu[] = [["text" => "🔄 AGGIORNA", "callback_data" => "admin:aggiornaRichieste"]];
    $bot->reply("<b>📩 RICHIESTE IN SOSPESO:</>\n\n".implode("\n", $richieste), $menu);
}


elseif($bot->checkPage("editEpisodeVideo") && $bot->video){
    $id = explode("_", $bot->page)[1];
    $file_id = $bot->file_id;
    $bot->conn->query("UPDATE episodes SET fileID = '$file_id' WHERE id = '$id'");
    $bot->deleteMessage($bot->chatID, $bot->msgid);
    $bot->page("search:home");
}

elseif($bot->checkPage("editEpisodeTitle")){
    $id = explode("_", $bot->page)[1];
    $q = $bot->conn->prepare("UPDATE episodes SET title = :title WHERE id = :id");
    $q->bindParam(":title", $bot->msg);
    $q->bindParam(":id", $id);
    $q->execute();
    $bot->deleteMessage($bot->chatID, $bot->msgid);
    $bot->page("search:home");
}

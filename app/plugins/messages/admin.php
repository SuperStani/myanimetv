<?php
//Admin response message
if($bot->reply_to_message && $bot->reply_is_bot){
    preg_match('/[0-9]{5,25}/', $bot->reply_msg, $output_array);
    $user_id = $output_array[0];
    if($user_id){
        $name = $bot->getChat($user_id)["first_name"];
        if($bot->msg == "/fine"){
            $menu[] = [["text"=>"🏡 Indietro","callback_data"=>"home"]]; 
            $bot->sm($user_id,"❗️Chat terminata.\n\nLa tua chat è stata chiusa\nda un amministratore.",$menu);
            $bot->sm($bot->chatID,"🔹 La chat con <a href='tg://user?id=$user_id'>$name</> è stata chiusa!");
            $bot->page("start", $user_id);
        }else if($bot->msg == "/warn"){
            $bot->conn->query("INSERT INTO warn_users SET chat_id = '$user_id', type = 1");
            $total_warn = $bot->conn->query("SELECT chat_id FROM warn_users WHERE chat_id = '$user_id' AND type = 1")->rowCount();
            if($total_warn < 3){
                $bot->sm($bot->chatID, "Ho assegnato $total_warn warn su 3 a <a href='tg://user?id=$user_id'>$name</>");
                $bot->sm($user_id, "Hai appena ricevuto <b>".$total_warn. " warn su 3!</>\n\n📮 Motivazione:\n<i>Hai richiesto un anime gia presente sul bot scrivendo male il nome, ignorando cosi l'avviso presente nel messaggio principale della sezione</>\n\n⚠️ <b>ATTENZIONE</> se raggiungerai il limite di warn non ti sara piu permesso di richiedere anime!");
            }else{
                $bot->sm($bot->chatID, "Ho bannato <a href='tg://user?id=$user_id'>$name</> dalla sezione richieste!");
                $bot->sm($user_id, "⚠️ <b>ATTENZIONE</>\nSei stato bannato dalla sezione \"RICHIESTE\"\n\n📮 Motivazione:\n<i>Ti sono stati assegnati 3 warn, raggiungendo cosi il limite massimo stabilito!</>\n\n<b>Per maggiori informazioni o incompressioni non esitare a contattarci nella sezione di supporto!</>");
            }
        }else if($bot->msg == "/unbanr"){
            $bot->conn->query("DELETE FROM warn_users WHERE chat_id = '$user_id' AND type = 1");
            $bot->sm($bot->chatID, "Ho rimosso il ban di <a href='tg://user?id=$user_id'>$name</> dalla sezione richieste");
            $bot->sm($user_id, "Sei stato sbannato dalla sezione richieste!");
        }else if($bot->msg == "/ban"){

        }else if($bot->msg == "/superuser"){
            $grado = "SuperUtente";
            $q = $bot->conn->prepare("UPDATE users SET grado = :grado WHERE chat_id = :chat");
            $q->bindParam(":grado", $grado);
            $q->bindParam(":chat", $user_id);
            $q->execute();
            $img = "http://simplebot.ml/bots/stani/myanimetv/img/COMPLIMENTI(banner).png";
            $bot->sm($user_id, "<a href='$img'>&#8203</><b>🎉 COMPLIMENTI</>\nSei appena stato promosso\na <b>SuperUtente</>!\n\n💌 DESCRIZIONE:\nQuesta promozione è dovuta al fatto\nche finora hai contribuito richiedendo\nmolti anime che mancavano sul nostro bot.\n\nℹ️ INFO: \n<i>Data la tua promozione, adesso\nle tue richieste verranno considerate\nper prime, e ci impegneremo a caricarle\nil più presto possibile!</>\n\n🛎 <b>Tanti saluti dallo staff!</>");
            $bot->sm($bot->chatID, "<b><a href='tg://user?id=$user_id'>$name</> è stato promosso a SuperUtente!</>");
        }else{
            $bot->page("chat", $user_id);
            $bot->sm($user_id,"<b>Admin:</> $bot->msg");
        }
    }
} 

elseif($bot->cPage("motivation+")){
    $e = explode("+", $bot->page);
    $anime = $e[2];
    $user_id = $e[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home"]];
    $img = "http://simplebot.ml/bots/stani/myanimetv/img/RIFIUTATA(banner).png";
    $bot->sm($user_id, "<a href='$img'>&#8203</>❌ <b>RICHIESTA RIFIUTATA</>\n\n💮 Anime:<code>$anime</>\n📮 Motivazione:\n<i>$bot->msg</>\n\n🎈 <b>Un saluto dallo staff!</>", $menu);
    $q = $bot->conn->prepare("DELETE FROM richieste WHERE nome = :nome AND by_user_id = :user");
    $q->bindParam(":user", $user_id);
    $q->bindParam(":nome", trim($anime));
    $q->execute();
    $bot->sm($bot->chatID, "<b>Motivazione inviata con successo!</>", null, "html", false, false, $bot->msgid);
    $bot->page("home:");
}

elseif($bot->msg == "/richieste" && $bot->isadmin()){
    $q = $bot->conn->query("SELECT * FROM richieste");
    foreach($q as $ad){
        $richieste[] = "» <a href='https://t.me/c/1373617365/".$ad["msgid"]."'>".$ad["nome"]."</>";
    }
    $menu[] = [["text" => "🔄 AGGIORNA", "callback_data" => "admin:aggiornaRichieste"]];
    $bot->sm($bot->chatID, "<b>📩 RICHIESTE IN SOSPESO:</>\n\n".implode("\n", $richieste), $menu);
}
<?php


$bot->connessione($bot->setting["host"],$bot->setting["nome_utente"],$bot->setting["password"],$bot->setting["database"]);
if($bot->chatID > 0){
    $q = $bot->conn->prepare("SELECT chat_id FROM utenti WHERE chat_id = :chat");
    $q->bindParam(":chat", $bot->userID);
    $q->execute();
    if($q->rowCount() == 0){ 
        $page = 'start';
        $q2 = $bot->conn->prepare("INSERT INTO utenti SET chat_id = :chat , page = :pagina, username = :username");
        $q2->bindParam(":chat", $bot->userID);
        $q2->bindParam(":pagina", $page);
        $q2->bindParam(":username", $bot->username);
        $q2->execute();
    }
    $q3 = $bot->conn->prepare("UPDATE utenti SET last_update = CURRENT_TIMESTAMP, username = :username WHERE chat_id = :chat_id");
    $q3->bindParam(":username", $bot->username);
    $q3->bindParam(":chat_id", $bot->userID);
    $q3->execute();
}




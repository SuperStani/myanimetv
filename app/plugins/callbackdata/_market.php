<?php

if($bot->data("home")){
    $bot->connessione("localhost", "admin", "@Naruto96", "myanimetvchat");
    $is_follower = $bot->conn->query("SELECT user_id FROm users WHERE user_id = '$bot->userID'")->rowCount();
    if($is_follower){
        $exp = $bot->conn->query("SELECT exp FROM users WHERE user_id = '$bot->userID'")->fetch()["exp"];
        $menu[] = [["text" => "š¹ 1 RICHIESTA (200EXP)", "callback_data" => "market:richiesta_1"]];
        $menu[] = [["text" => "š¹ 5 RICHIESTE (950EXP)", "callback_data" => "market:richiesta_2"]];
        $menu[] = [["text" => "š¹ 10 RICHIESTE (1920EXP)", "callback_data" => "market:richiesta_3"]];
        $menu[] = [["text" => "š¹ 15 RICHIESTE (2300EXP)", "callback_data" => "market:richiesta_4"]];
        $menu[] = [["text" => "š SBLOCCO RICHIESTE (3000EXP)", "callback_data" => "market:sblocco"]];
        $menu[] = [["text" => "āļø INDIETRO", "callback_data" => "home:home"]];
        $img = $bot->setting["banner"]["market"];
        $bot->edit("<a href='$img'>&#8203;</a>š | EXP Disponibili: <b>$exp</b>\nš° | Seleziona l'articolo che vuoi acquistare:", $menu);
    }else{
        $menu[] = [["text" => "āļø UNISCITI AL GRUPPO", "url" => "t.me/myanimetvchat"]];
        $menu[] = [["text" => "āļø INDIETRO", "callback_data" => "home:home"]];
        $bot->edit("Per sbloccare il negozio devi unirti alla chat ufficiale del bot!", $menu);
    }
}

elseif($bot->data("richiesta")){
    $type = explode("_", $bot->cbdata)[1];
    $price = [200, 950, 1920, 2300];
    $bot->connessione("localhost", "admin", "@Naruto96", "myanimetvchat");
    $exp = $bot->conn->query("SELECT exp FROM users WHERE user_id = '$bot->userID'")->fetch()["exp"];
    if($exp >= $price[$type - 1]){
        $menu[] = [["text" => "ā CONFERMA", "callback_data" => "market:shopRichiesta_$type"]];
        $menu[] = [["text" => "ā ANNULLA", "callback_data" => "market:home"]];
        $bot->edit("Vuoi confermare l'acquisto?\nSpenderai <b>".$price[$type - 1]."exp</b> per questo articolo!", $menu);
    }else{
        $bot->alert("ā ļø Non hai abbastanza exp per poter comprare questo articolo!");
    }
}

elseif($bot->data("sblocco")){
    $warns = $bot->conn->query("SELECT COUNT(chat_id) AS warns FROM warn_users WHERE chat_id = '$bot->userID'")->fetch()["warns"];
    if($warns == 3){
        $bot->connessione("localhost", "admin", "@Naruto96", "myanimetvchat");
        $exp = $bot->conn->query("SELECT exp FROM users WHERE user_id = '$bot->userID'")->fetch()["exp"];
        if($exp >= $price[$type - 1]){
            $menu[] = [["text" => "ā CONFERMA", "callback_data" => "market:shopSblocco_$type"]];
            $menu[] = [["text" => "ā ANNULLA", "callback_data" => "market:home"]];
            $bot->edit("Vuoi confermare l'acquisto?\nSpenderai <b>2500exp</b> per questo articolo!\n\nā¹ļø <i>Questo articolo ti permette di sbannarti dalla sezione di richieste!</i>", $menu);
        }else{
            $bot->alert("ā ļø Non hai abbastanza exp per poter comprare questo articolo!");
        }
    }else{
        $bot->alert("ā ļø Non hai bisogno di acquistare questo articolo perchĆØ non sei stato/a bannato/a dalla sezione di richieste!");
    }
}

elseif($bot->data("shopRichiesta")){
    $type = explode("_", $bot->cbdata)[1];
    $price = [200, 950, 1920, 2300];
    $total = [1, 5, 10, 15];
    $bot->connessione("localhost", "admin", "@Naruto96", "myanimetvchat");
    $exp = $bot->conn->query("SELECT exp FROM users WHERE user_id = '$bot->userID'")->fetch()["exp"];
    if($exp >= $price[$type - 1]){
        $price = $price[$type - 1];
        $total = $total[$type - 1];
        $bot->conn->query("UPDATE users SET exp = exp - $price WHERE user_id = '$bot->userID'");
        $bot->connessione("localhost", "admin", "@Naruto96", "myanimetv");
        $bot->conn->query("UPDATE utenti SET richieste = richieste + $total WHERE chat_id = '$bot->userID'");
        $menu[] = [["text" => "āļø INDIETRO", "callback_data" => "market:home"]];
        if($total == 1) {$text = "ā | E' stata aggiunta <b>$total</b> richiesta sul tuo account!";}
        else { $text = "ā | Sono state aggiunte <b>$total</b> richieste sul tuo account!";}
        $bot->edit("āļø | Articolo acquistato con successo!\n$text", $menu);
    }else{
        $bot->alert("ā ļø Non hai abbastanza exp per poter comprare questo articolo!");
    }
}

elseif($bot->data("shopSblocco")){
    $bot->connessione("localhost", "admin", "@Naruto96", "myanimetvchat");
    $exp = $bot->conn->query("SELECT exp FROM users WHERE user_id = '$bot->userID'")->fetch()["exp"];
    if($exp >= 3000){
        $price = 3000;
        $bot->conn->query("UPDATE users SET exp = exp - $price WHERE user_id = '$bot->userID'");
        $bot->connessione("localhost", "admin", "@Naruto96", "myanimetv");
        $bot->conn->query("DELETE FROM warn_users WHERE chat_id = '$bot->userID'");
        $menu[] = [["text" => "āļø INDIETRO", "callback_data" => "market:home"]];
        $bot->edit("āļø | Articolo acquistato con successo!\nš | Sei stato sbloccato dalla sezione di richieste!", $menu);
    }else{
        $bot->alert("ā ļø Non hai abbastanza exp per poter comprare questo articolo!");
    }
}
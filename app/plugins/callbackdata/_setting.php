<?php

if($bot->data("home")) {
    $bot->page("setting:home");
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $delete = $e[2];
    $menu[] = [["text" => "⏏️ MODIFICA INFO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $menu[] = [["text" => "➕ AGGIUNGI EPISODIO", "callback_data" => "episodes:upload_$anime_id"]];
    $menu[] = [["text" => "📤 INVIA", "callback_data" => "admin:sendAnime_$anime_id"], ["text" => "🗑 ELIMINA", "callback_data" => "admin:removeAnime_$anime_id"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "view:anime_$anime_id"."_1"]];
    if($delete){
        $bot->deleteMessage($bot->chatID, $bot->msgid);
        $bot->reply("<b>⚜️ Seleziona un'opzione </b>", $menu);
    }else{
        $bot->edit("<b>⚜️ Seleziona un'opzione </b>", $menu);
    }
}

elseif($bot->data("option")){
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $option = $e[2];
    if($option){
        $menu[] = [["text" => "🔽 MODIFICA INFO", "callback_data" => "setting:option_$anime_id"."_0"]];
        $menu[] = [["text" => "✏️ TITOLO", "callback_data" => "setting:title_$anime_id"], ["text" => "✏️ POSTER", "callback_data" => "setting:poster_$anime_id"]];
        $menu[] = [["text" => "✏️ NR. STAGIONE", "callback_data" => "setting:stagione_$anime_id"], ["text" => "✏️ ORDINE", "callback_data" => "setting:ordine_$anime_id"]];
        $menu[] = [["text" => "✏️ DURATA EP", "callback_data" => "setting:durata_$anime_id"], ["text" => "✏️ GENERI", "callback_data" => "setting:generi_$anime_id"]];
        $menu[] = [["text" => "✏️ NR.EP", "callback_data" => "setting:episodiNr_$anime_id"], ["text" => "✏️ DATA", "callback_data" => "setting:aired_$anime_id"]];
        $menu[] = [["text" => "✏️ TRAMA", "callback_data" => "setting:trama_$anime_id"], ["text" => "✏️ TRAILER", "callback_data" => "setting:trailer_$anime_id"]];
        $menu[] = [["text" => "✏️ CATEGORIA", "callback_data" => "setting:categoria_$anime_id"], ["text" => "🗑 ELIMINA EP", "callback_data" => "episodes:allRemove_$anime_id"]];
        $is_simuclast = $bot->conn->query("SELECT anime_id FROM anime_simulcast WHERE anime_id = '$anime_id'")->rowCount();
        if($is_simuclast) {
            $menu[] = [["text" => "🖌 TITOLO ON-GOING", "callback_data" => "simulcast:title_$anime_id"]];
            $menu[] = [["text" => "🖌 IMMAGINE ON-GOING", "callback_data" => "simulcast:poster_$anime_id"]];
            $menu[] = [["text" => "❌ RIMUOVI ON-GOING", "callback_data" => "simulcast:remove_$anime_id"]];
        }else{
            $menu[] = [["text" => "✳️ IMPOSTA ON-GOING", "callback_data" => "simulcast:settup_$anime_id"]];
        }
    }else{
        $menu[] = [["text" => "⏏️ MODIFICA INFO", "callback_data" => "setting:option_$anime_id"."_1"]];
    }
    $menu[] = [["text" => "➕ AGGIUNGI EPISODIO", "callback_data" => "episodes:upload_$anime_id"]];
    $menu[] = [["text" => "📤 INVIA", "callback_data" => "admin:sendAnime_$anime_id"], ["text" => "🗑 ELIMINA", "callback_data" => "admin:removeAnime_$anime_id"]];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "view:anime_$anime_id"."_1"]];
    $bot->edit("<b>⚜️ Seleziona un'opzione </b>", $menu);
}

elseif($bot->data("title")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $title = $bot->conn->query("SELECT nome, nomi_alternativi FROM anime WHERE id = '$anime_id'")->fetch();
    $bot->edit("Ok, invia il nuovo titolo dell' anime:\nTitolo attuale: <code>".$title["nome"]."</code>\nAlternativi attuali: <code>".$title["nomi_alternativi"]."</code>\n\n<b>N.B.</b> <i>Per aggiungere titoli alternativi usa questa sintassi \"TITOLO_PRINCIPALE + NOME_ALT1, NOME_ALT2\"</i>", $menu);
    $bot->page("setting:title_$anime_id");
}

elseif($bot->data("poster")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->edit("Ok, invia il nuovo poster dell'anime:", $menu);
    $bot->page("setting:poster_$anime_id");
}

elseif($bot->data("durata")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $durata = $bot->conn->query("SELECT durata_ep FROM anime_info WHERE anime_id = '$anime_id'")->fetch()["durata_ep"];
    $bot->edit("Ok, invia la nuova durata media degli episodi:\nDurata attuale: <code>$durata</code>", $menu);
    $bot->page("setting:durata_$anime_id");
}

elseif($bot->data("stagione")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $stagione = $bot->conn->query("SELECT stagione FROM anime WHERE id = '$anime_id'")->fetch()["stagione"];
    $bot->edit("Ok, invia il numero della stagione:\nNr.Stagione attuale: <b>$stagione</b>\n\n<b>N.B</b> <i>Inviare 0 se l'anime è singolo (Senza altre stagioni)</i>", $menu);
    $bot->page("setting:stagione_$anime_id");
}

elseif($bot->data("ordine")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $ordine = $bot->conn->query("SELECT stagione FROM anime_groups WHERE anime_id = '$anime_id'")->fetch()["stagione"];
    if(isset($ordine)){
        $bot->edit("Ok, invia il numero dell'ordine sequenziale dell'anime':\nOrdine attuale: <b>$ordine</b>\n\n<b>N.B.</b> <i>Se questo è un movie che va in mezzo alla 1° e 2° stagione di un anime scrivere 1.5 (Questa sintassi è generalizzata)</i>", $menu);
        $bot->page("setting:ordine_$anime_id");
    }else{
        $bot->alert("Questo anime non fa parte di nessun gruppo!");
    }
}

elseif($bot->data("episodiNr")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $durata = $bot->conn->query("SELECT episodi FROM anime_info WHERE anime_id = '$anime_id'")->fetch()["episodi"];
    $bot->edit("Ok, invia il nuovo numero di episodi:\nEpisodi attuali: <b>$durata</b>\n\n<b>N.B.</b> <i>quando il numero di episodi è indeterminato invia 0</i>", $menu);
    $bot->page("setting:episodiNr_$anime_id");
}

elseif($bot->data("aired")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $data = $bot->conn->query("SELECT uscita FROM anime_info WHERE anime_id = '$anime_id'")->fetch()["uscita"];
    $bot->edit("Ok, invia la nuova data di uscita dell'anime:\nData attuale: <code>$data</code>\n\n<b>N.B.</b> <i>il formato è \"giorno mese anno\"</i>", $menu);
    $bot->page("setting:aired_$anime_id");
}

elseif($bot->data("trama")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $trama = $bot->conn->query("SELECT trama_url FROM anime_info WHERE anime_id = '$anime_id'")->fetch()["trama_url"];
    $bot->edit("Ok, invia la nuova trama:\nTrama attuale: $trama", $menu, null, null, true);
    $bot->page("setting:trama_$anime_id");
}

elseif($bot->data("trailer")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $trailer = $bot->conn->query("SELECT trailer FROM anime_info WHERE anime_id = '$anime_id'")->fetch()["trailer"];
    $bot->edit("Ok, invia il nuovo trailer:\nTrailer attuale: $trailer", $menu);
    $bot->page("setting:trailer_$anime_id");
}

elseif($bot->data("categoria")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT * FROM categorie");
    foreach($q as $row){
        $menu[] = [["text" => $row["tipo"], "callback_data" => "setting:setCategoria_$anime_id"."_".$row["id"]]];
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->edit("Seleziona la categoria:", $menu);
}

elseif($bot->data("setCategoria")){
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $id = $e[2];
    $bot->conn->query("UPDATE anime_info SET categoria = '$id' WHERE anime_id = '$anime_id'");
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->edit("Modifica effettuata con successo!", $menu);
    $bot->page("setting:home");
}


elseif($bot->data("generi")){
    $anime_id = explode("_", $bot->cbdata)[1];
    $q = $bot->conn->query("SELECT id, nome FROM generi");
    $x = 0; $y = 0;
    foreach($q as $ad){
        $genres_id = $ad["id"];
        $row = $bot->conn->query("SELECT genere_id FROM anime_genere WHERE genere_id = '$genres_id' AND anime_id = '$anime_id'");
        if($row->rowCount()){
            $txt = $ad["nome"]." 🔵";
        }else{
            $txt = $ad["nome"]." 🔴";
        }
        if($x < 2){ $x++;}
        else { $x = 1; $y++;}
        $menu[$y][] = ["text" => $txt, "callback_data" => "setting:setGenere_$anime_id"."_".$ad["id"]];
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->edit("Seleziona i generi:", $menu);
}


elseif($bot->data("setGenere")){
    $e = explode("_", $bot->cbdata);
    $anime_id = $e[1];
    $id = $e[2];
    $row = $bot->conn->prepare("SELECT genere_id FROM anime_genere WHERE genere_id = :id AND anime_id = :anime");
    $row->bindParam(":id", $id);
    $row->bindParam(":anime", $anime_id);
    $row->execute();
    if($row->rowCount()){ //...Remove
        $delete = $bot->conn->prepare("DELETE FROM anime_genere WHERE genere_id = :id AND anime_id = :anime");
        $delete->bindParam(":id", $id);
        $delete->bindParam(":anime", $anime_id);
        $delete->execute();
    }else{ //...ADD
        $add = $bot->conn->prepare("INSERT INTO anime_genere SET genere_id = :id, anime_id = :anime");
        $add->bindParam(":id", $id);
        $add->bindParam(":anime", $anime_id);
        $add->execute();
    }
    $q = $bot->conn->query("SELECT id, nome FROM generi");
    $x = 0; $y = 0;
    foreach($q as $ad){
        $genres_id = $ad["id"];
        $row = $bot->conn->query("SELECT genere_id FROM anime_genere WHERE genere_id = '$genres_id' AND anime_id = '$anime_id'");
        if($row->rowCount()){
            $txt = $ad["nome"]." 🔵";
        }else{
            $txt = $ad["nome"]." 🔴";
        }
        if($x < 2){ $x++;}
        else { $x = 1; $y++;}
        $menu[$y][] = ["text" => $txt, "callback_data" => "setting:setGenere_$anime_id"."_".$ad["id"]];
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "setting:option_$anime_id"."_1"]];
    $bot->edit("Seleziona i generi:", $menu);
}
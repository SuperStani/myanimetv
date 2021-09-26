<?php
include "/var/www/html/bots/myanimetv/app/queries/search.php";
//...Search anime keyword and fetch results
if($bot->checkPage("q") && isset($bot->msg)){
    $bot->deleteMessage($bot->userID, $bot->msgid - 1);
    $bot->deleteMessage($bot->userID, $bot->msgid);
    $index = 0; $limite = 11;
    $search = '%'.$bot->msg.'%';
    $order = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $q = $bot->conn->prepare($query["name"][$order - 1]);
    $q->bindParam(":search", $search);
    $q->bindParam(":searchIndex", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $limite, PDO::PARAM_INT);
    $q->execute();
    if($q->rowCount() == 0){
        $menu[] = [["text" => "📥 RICHIEDI", "callback_data" => "home:richiedi"]];
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
        $img = $bot->setting["banner"]["errore1"];
        $bot->reply("<a href='$img'>&#8203;</>❗️<b>ERRORE</>\n\nNon è presente alcun anime con\nquesto nome <u>$bot->msg</u>\nRichiedilo nella apposita sezione.!", $menu);
    }else{
        $i = $bot->conn->prepare("INSERT INTO search_keys SET text = :text, chat_id = :chat");
        $i->bindParam(":text", $bot->msg);
        $i->bindParam(":chat", $bot->userID);
        $i->execute();
        $i = 1; $x = 0; $y = 0;
        if($q->rowCount() == $limite){ $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]]; $y = 1;}
        $text = "";
        foreach($q as $ad){
            if($i < $limite){
                    $anime_id = $ad["id"];
                $generi = $bot->conn->prepare("SELECT 
                                                generi.nome
                                            FROM generi 
                                            INNER JOIN anime_genere 
                                            ON anime_genere.genere_id = generi.id 
                                            INNER JOIN anime 
                                            ON anime.id = anime_genere.anime_id 
                                            WHERE anime.id = :anime 
                                            ORDER BY generi.id DESC 
                                            LIMIT 3 ");
                $generi->bindParam(":anime", $anime_id);
                $generi->execute();
                $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
                $nr = ["1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣", "🔟"];
                $nr = $nr[$i - 1];
                $text = $text.$nr." ➥ <b>".$ad["nome"]." S".$ad["stagione"]."</>\n$generi\n";
                if($x < 5){
                    $x++;
                }else{
                    $y++;
                    $x = 1;
                }
                $menu[$y][] = ["text" => $nr, "callback_data" => "view:anime_".$ad["id"]."_1"];
                $i++;
            }
        }
        $text = str_replace("S0","", $text);
        if($q->rowCount() == $limite){
            $menu[] = [["text" => "»»»", "callback_data" => "scroll:name_10"]];
        }
        $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "search:home"]];
        $bot->reply("🔎 Ecco i risultati della ricerca: \"<b>$bot->msg</b>\"\n\n$text", $menu);
        $bot->page("search:results");
    }
}else{
    $bot->reply("Vuoi cercare un anime?\nDai /start al bot e cercalo nell'apposita sezione!");
}


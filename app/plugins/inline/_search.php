<?php

$bot->page("search:results");
$search = '%'.$bot->imsg.'%';
$q = $bot->conn->prepare("SELECT
                                anime.nome, 
                                anime.id,
                                anime.stagione,
                                anime.poster_msgid,
                                anime_info.episodi, 
                                anime_info.trama_url, 
                                anime_info.uscita,
                                anime_info.trailer
                            FROM anime
                            INNER JOIN anime_info
                            ON anime.id = anime_info.anime_id 
                            WHERE anime.nome LIKE :search 
                            OR anime.nomi_alternativi LIKE :search 
                            LIMIT 50");
$q->bindParam(":search", $search);
$q->execute();
if($q->rowCount() > 0){
    foreach($q as $ad){
        $anime_id = $ad["id"];
        $generi = $bot->conn->prepare("SELECT 
                                        generi.nome
                                    FROM generi 
                                    INNER JOIN anime_genere 
                                    ON anime_genere.genere_id = generi.id 
                                    INNER JOIN anime 
                                    ON anime.id = anime_genere.anime_id 
                                    WHERE anime.id = :anime
                                    ORDER by generi.id
                                    LIMIT 4");
        $generi->bindParam(":anime", $anime_id);
        $generi->execute();
        $generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome'));
        $stagione = $ad["stagione"];
        $trama_url = $ad["trama_url"];
        $trailer = $ad["trailer"]; 
        $episodi = $ad["episodi"];
        $uscita = $ad["uscita"];
        $nome = $ad["nome"];
        $link = "https://t.me/my6325anime2243tv23523posters/".$ad["poster_msgid"];
        if($stagione > 0){
            $stagioni = ["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"];
            $stag = "➥ ".str_replace([1,2,3,4,5,6,7,8,9,10], $stagioni, $stagione)." stagione\n";
        }else{
            $stag = "";
        }
        if($trailer != ''){
            $trailer = "\n📽 | <b>Trailer:</> <a href='$trailer'>Clicca qui</>";
        }
        if($episodi == 0){
            $episodi = "??";
        }
        $text = "<b>$nome</>\n━━━━━━━━━━━\n$stag
🗓 | <b>Data:</> $uscita
➕ | <b>Episodi:</> $episodi
📖 | <b>Trama:</> <a href='$trama_url'>Clicca qui</>$trailer\n
🌟 | <b>Generi:</> $generi
🆔 | <code>$anime_id</code>";
        $json[] = [
            "type" => "article",
            "id" => $ad["id"],
            "title" => $ad["nome"],
            "description" => "$stag",
            "thumb_url" => $link,
            "message_text" => "<a href='$link'>&#8203;</>$text",
            "parse_mode" => "html",
            "reply_markup" => ["inline_keyboard" => [[["text" => "🎥 GUARDA ORA", "url" => "t.me/myanimetvbot?start=animeID_".$anime_id]]]]
        ];
    }
}else{
    $json[] = [
        "type" => "article",
        "id" => 1,
        "title" => "ANIME NON TROVATO",
        "description" => "Questo anime non è presente nella mia collezione!",
        "thumb_url" => "http://simplebot.ml/bots/stani/myanimetv/img/ERRORE(banner).png",
        "message_text" => "<a href='http://simplebot.ml/bots/stani/myanimetv/img/ERRORE(banner).png'>&#8203;</><b>ANIME NON PRESENTE!</>",
        "parse_mode" => "html",
    ];
}
$bot->inline($json, $bot->inline);
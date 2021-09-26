<?php
include "/var/www/html/bots/myanimetv/app/queries/mylists.php";
if($bot->data("completed")) {
    $listID = 1;
    $srcOrder = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $e = explode("_", $bot->cbdata);
    $index = $e[2]; 
    $user_id = $e[1];
    $next_index = $index + 10; $prev_index = $index - 10;
    $max_results = 21;
    $squery = $query["mylists"][$srcOrder - 1];
    $q = $bot->conn->prepare($squery);
    $q->bindParam(":chat_id", $user_id);
    $q->bindParam(":list", $listID);
    $q->bindParam(":index", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $max_results, PDO::PARAM_INT);
    $q->execute();
    $i = 1;
    $text = "🔵 <b>COMPLETED LIST</b>\n\n";
    foreach($q as $row){
        if($i < $max_results){
            $text = $text."➥ <b><a href='t.me/myanimetvbot?start=animeID_".$row["id"]."'>".$row["nome"]." S".$row["stagione"]."</a></b>\n";
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]]; $y = 1;
    if($q->rowCount() == $max_results){
        if($user_id == $bot->userID){
            $menu[] = [["text" => "🗑 RIMUOVI", "callback_data" => "mylist:scrollRemove_1_0"]];
        }
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "mylist:completed_$user_id"."_".$next_index]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "mylist:completed_$user_id"."_".$prev_index],["text" => "»»»", "callback_data" => "mylist:completed_$user_id"."_".$next_index]];
        }
    }else{
        if($user_id == $bot->userID){
            $menu[] = [["text" => "🗑 RIMUOVI", "callback_data" => "mylist:scrollRemove_1_0"]];
        }
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "mylist:completed_$user_id"."_".$prev_index]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "profile:home_$user_id"]];
    $bot->edit($text, $menu);
}

elseif($bot->data("watching")) {
    $listID = 2;
    $srcOrder = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $e = explode("_", $bot->cbdata);
    $index = $e[2]; 
    $user_id = $e[1];
    $next_index = $index + 10; $prev_index = $index - 10;
    $max_results = 21;
    $squery = $query["mylists"][$srcOrder - 1];
    $q = $bot->conn->prepare($squery);
    $q->bindParam(":chat_id", $user_id);
    $q->bindParam(":list", $listID);
    $q->bindParam(":index", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $max_results, PDO::PARAM_INT);
    $q->execute();
    $i = 1;
    $text = "🟢<b> WATCHING LIST</b>\n\n";
    foreach($q as $row){
        if($i < $max_results){
            $text = $text."➥ <b><a href='t.me/myanimetvbot?start=animeID_".$row["id"]."'>".$row["nome"]." S".$row["stagione"]."</a></b>\n";
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]]; $y = 1;
    if($q->rowCount() == $max_results){
        if($user_id == $bot->userID){
            $menu[] = [["text" => "🗑 RIMUOVI", "callback_data" => "mylist:scrollRemove_2_0"]];
        }
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "mylist:watching_$user_id"."_".$next_index]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "mylist:watching_$user_id"."_".$prev_index], ["text" => "»»»", "callback_data" => "mylist:watching_$user_id"."_".$next_index]];
        }
    }else{
        if($user_id == $bot->userID){
            $menu[] = [["text" => "🗑 RIMUOVI", "callback_data" => "mylist:scrollRemove_2_0"]];
        }
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "mylist:watching_$user_id"."_".$prev_index]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "profile:home_$user_id"]];
    $bot->edit($text, $menu);
}

elseif($bot->data("plntowatch")) { 
    $listID = 3;
    $srcOrder = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $e = explode("_", $bot->cbdata);
    $index = $e[2]; 
    $user_id = $e[1];
    $next_index = $index + 20; $prev_index = $index - 20;
    $max_results = 21;
    $squery = $query["mylists"][$srcOrder - 1];
    $q = $bot->conn->prepare($squery);
    $q->bindParam(":chat_id", $user_id);
    $q->bindParam(":list", $listID);
    $q->bindParam(":index", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $max_results, PDO::PARAM_INT);
    $q->execute();
    $i = 1;
    $text = "⚪️ <b>PLAN TO WATCH LIST</b>\n\n";
    foreach($q as $row){
        if($i < $max_results){
            $text = $text."➥ <b><a href='t.me/myanimetvbot?start=animeID_".$row["id"]."'>".$row["nome"]." S".$row["stagione"]."</a></b>\n";
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    $menu[] = [["text" => "⏏️ ORDINE", "callback_data" => "scroll:option_0"]];
    if($q->rowCount() == $max_results){
        if($user_id == $bot->userID) {
            $menu[] = [["text" => "🗑 RIMUOVI", "callback_data" => "mylist:scrollRemove_3_0"]];
        }
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "mylist:pntowatch_$user_id"."_".$next_index]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "mylist:plntowatch_$user_id"."_".$prev_index],["text" => "»»»", "callback_data" => "mylist:plntowatch_$user_id"."_".$next_index]];
        }
    }else{
        if($user_id == $bot->userID) {
            $menu[] = [["text" => "🗑 RIMUOVI", "callback_data" => "mylist:scrollRemove_3_0"]];
        }
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "mylist:plntowatch_$user_id"."_".$prev_index]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "profile:home_$user_id"]];
    $bot->edit($text, $menu);
}

elseif($bot->data("preferreds")) {
    $index = explode("_", $bot->cbdata)[1];
    $next_index = $index + 10; $prev_index = $index - 10;
    $srcOrder = $bot->conn->query("SELECT srcOrder FROM utenti WHERE chat_id = '$bot->userID'")->fetch()["srcOrder"];
    $e = explode("_", $bot->cbdata);
    $index = $e[2]; 
    $user_id = $e[1];
    $next_index = $index + 20; $prev_index = $index - 20;
    $max_results = 21;
    $squery = $query["mylists"][$srcOrder - 1];
    $q = $bot->conn->prepare("SELECT anime.nome, anime.stagione, anime.id FROM anime INNER JOIN preferreds ON anime.id = preferreds.anime_id WHERE chat_id = :chat_id ORDER by anime.nome, anime.stagione LIMIT :index, :limite");
    $q->bindParam(":chat_id", $user_id);
    $q->bindParam(":index", $index, PDO::PARAM_INT);
    $q->bindParam(":limite", $max_results, PDO::PARAM_INT);
    $q->execute();
    $i = 1;
    $text = "❤️ <b>ANIME PREFERITI</b>\n\n";
    foreach($q as $row){
        if($i < $max_results){
            $text = $text."➥ <b><a href='t.me/myanimetvbot?start=animeID_".$row["id"]."'>".$row["nome"]." S".$row["stagione"]."</a></b>\n";
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    if($q->rowCount() == $max_results){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "mylist:preferreds_$user_id"."_".$next_index]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "mylist:preferreds_$user_id"."_".$prev_index], ["text" => "»»»", "callback_data" => "mylist:preferreds_$user_id"."_".$next_index]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "mylist:preferreds_$user_id"."_".$prev_index]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "profile:home_$user_id"]];
    $bot->edit($text, $menu);
}


elseif($bot->data("import")){
  $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "profile:home_$bot->userID"]];
  $text = "ℹ️ <b>Questa funzione ti permette d'importare tutti i tuoi anime da MyAnimeList a MY ANIME TV</b>.\n\nBasterà inviare qui il file XML della tua lista (scaricabile da questo link su <a href='https://myanimelist.net/panel.php?go=export'>MyAnimeList</a>).\n\n<i>N.B: Se alcuni anime presenti nel tuo MAL non sono presenti su MY ANIME TV non saranno importati.</i>\n\n⚠️ Quando importerai la nuova lista quella attuale del bot verra sostituita con la nuova!";
  //$text = "ℹ️ <b>Questa funzione verrà resa dispobile a breve!</b>";
  $bot->edit($text, $menu, null, false, true);
  $bot->page("mylist:import");
}

elseif($bot->data("export")){
  //1 Completed; 2 Preferred; 3 Watching; 4 Plan-to-watch
  $anime = $bot->conn->query("SELECT DISTINCT(anime.mal_id), anime.nome, anime.stagione, anime_info.episodi, bookmarks.list_id FROM anime INNER JOIN bookmarks ON anime.id = bookmarks.anime_id INNER JOIN anime_info ON anime.id = anime_info.anime_id WHERE bookmarks.chat_id = '$bot->userID' AND anime.mal_id <> 0");
  $xml = new SimpleXMLElement('<?xml version="1.0"?><myanimelist/>');
  $profile = $xml->addChild("myinfo");
  $profile->addChild("user_export_type", 1);
  $profile->addChild("user_total_anime", "2");
  foreach($anime as $row){
      $node = $xml->addChild("anime");
      $node->addChild("series_animedb_id", $row["mal_id"]);
      $title = $node->addChild("series_title", NULL);
      $base = dom_import_simplexml($title);
      $docOwner = $base->ownerDocument;
      $base->appendChild($docOwner->createCDATASection($row["nome"]." ".str_replace("S0", "", "S".$row["stagione"])));
      switch($row["list_id"]){
          case 1: $status = "Completed"; break;
          case 2: $status = "Watching"; break;
          case 3: $status = "Plan to Watch"; break;
      }
      $node->addChild("my_status", $status);
      $node->addChild("my_sns", "default");
      $node->addChild("update_on_import", 1);
      if($status == "Completed"){
        $node->addChild("my_watched_episodes", $row["episodi"]);
      }else{
        $node->addChild("my_watched_episodes", 0);
      }
  }
  $file = "bots/myanimetv/resources/lists_exported/myanimetvbot_animelist_$bot->userID"."_".rand(1000, 10000).".zip";
  $myzip = new ZipArchive;
  $myzip->open("/var/www/html/".$file, ZipArchive::CREATE);
  $myzip->addFromString("myanimetvbot_animelist_$bot->userID.xml", $xml->asXML());
  $myzip->close();
  //$menu[] = [["text" => "TUTORIAL", "url" => "telegra.ph"]];
  $bot->send_document($bot->userID, "https://myanimetv.org/$file", "<b>ℹ️ Puoi importare la tua lista in qualsiasi sito supporti il sistema di MAL</b>\n\nPer importare la lista su MAL dirigiti su questo link https://myanimelist.net/import.php", $menu);
  unlink("/var/www/html/".$file);
}


elseif($bot->data("scrollRemove")) {
    $e = explode("_", $bot->cbdata);
    $list_id = $e[1];
    $index = $e[2];
    $next_index = $index + 10; $prev_index = $index - 10;
    $max_results = 11;
    $q = $bot->conn->query("SELECT anime.id, anime.nome, anime.stagione FROM anime INNER JOIN bookmarks ON anime.id = bookmarks.anime_id WHERE bookmarks.list_id = '$list_id' AND bookmarks.chat_id = '$bot->userID' ORDER by anime.nome, anime.stagione LIMIT $index, 11");
    $i = 1; $x = 0; $y = 0;
    $nrs = ["1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣", "🔟"];
    $text = "👇 <b>SELEZIONA L'ANIME CHE VUOI RIMUOVERE DALLA LISTA:</b>\n\n";
    foreach($q as $row){
        if($i < $max_results){
            if($x < 3) { $x++; }
            else { $x = 1; $y++; }
            $text = $text.$nrs[$i - 1]." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$row["id"]."'>".$row["nome"]." S".$row["stagione"]."</a></b>\n";
            $menu[$y][] = ["text" => $nrs[$i - 1], "callback_data" => "mylist:null"];
            $menu[$y][] = ["text" => "🗑", "callback_data" => "mylist:remove_$list_id"."_".$row["id"]."_$index"];
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    $lists = ["completed", "watching", "plntowatch"];
    $results = $q->rowCount();
    if($results == $max_results){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "mylist:scrollRemove_$list_id"."_$next_index"]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "mylist:scrollRemove_$list_id"."_$prev_index"],["text" => "»»»", "callback_data" => "mylist:scrollRemove_$list_id"."_$next_index"]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "mylist:scrollRemove_$list_id"."_$prev_index"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "mylist:".$lists[$list_id - 1]."_$user_id"."_0"]];
    $bot->edit($text, $menu);
}

elseif($bot->data("remove")){
    $e = explode("_", $bot->cbdata);
    $list_id = $e[1];
    $anime_id = $e[2];
    $index = $e[3];
    $next_index = $index + 10; $prev_index = $index - 10;
    $max_results = 11;
    $bot->conn->query("DELETE FROM bookmarks WHERE anime_id = '$anime_id' AND chat_id = '$bot->userID'");
    $q = $bot->conn->query("SELECT anime.id, anime.nome, anime.stagione FROM anime INNER JOIN bookmarks ON anime.id = bookmarks.anime_id WHERE bookmarks.list_id = '$list_id' AND bookmarks.chat_id = '$bot->userID' ORDER by anime.nome, anime.stagione LIMIT $index, 11");
    $i = 1; $x = 0; $y = 0;
    $nrs = ["1️⃣","2️⃣","3️⃣","4️⃣","5️⃣","6️⃣","7️⃣","8️⃣","9️⃣", "🔟"];
    $text = "👇 <b>SELEZIONA L'ANIME CHE VUOI RIMUOVERE DALLA LISTA:</b>\n\n";
    foreach($q as $row){
        if($i < $max_results){
            if($x < 3) { $x++; }
            else { $x = 1; $y++; }
            $text = $text.$nrs[$i - 1]." ➥ <b><a href='t.me/myanimetvbot?start=animeID_".$row["id"]."'>".$row["nome"]." S".$row["stagione"]."</a></b>\n";
            $menu[$y][] = ["text" => $nrs[$i - 1], "callback_data" => "mylist:null"];
            $menu[$y][] = ["text" => "🗑", "callback_data" => "mylist:remove_$list_id"."_".$row["id"]."_$index"];
        }
        $i++;
    }
    $text = str_replace("S0","", $text);
    $lists = ["completed", "watching", "plntowatch"];
    $results = $q->rowCount();
    if($results == $max_results){
        if($index == 0){
            $menu[] = [["text" => "»»»", "callback_data" => "mylist:scrollRemove_$list_id"."_$next_index"]];
        }else{
            $menu[] = [["text" => "«««", "callback_data" => "mylist:scrollRemove_$list_id"."_$prev_index"],["text" => "»»»", "callback_data" => "mylist:scrollRemove_$list_id"."_$next_index"]];
        }
    }else{
        if($index > 0){
            $menu[] = [["text" => "«««", "callback_data" => "mylist:scrollRemove_$list_id"."_$prev_index"]];
        }
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "mylist:".$lists[$list_id - 1]."_$bot->userID"."_0"]];
    $bot->edit($text, $menu);
}
?>
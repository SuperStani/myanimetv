<?php

//Profile
if($bot->data("home")){
    $bot->page("home:home");
    $user_id = explode("_", $bot->cbdata)[1];
    //COUNT TOTAL ANIME WATCHED
    $query = "SELECT 
                COUNT(anime.id) AS tot_anime 
              FROM anime
              INNER JOIN bookmarks 
              ON anime.id = bookmarks.anime_id 
              INNER JOIN anime_info
              ON anime.id = anime_info.anime_id
              WHERE bookmarks.chat_id = :user 
              AND bookmarks.list_id = 1 
              AND anime.stagione < 2 
              AND anime_info.categoria <> 2";
    $q = $bot->conn->prepare($query);
    $q->bindParam(":user", $user_id);
    $q->execute();
    $anime_visti = $q->fetch()["tot_anime"];

    //COUNT TOTAL ANIME MOVIE WATCHED
    $query = "SELECT 
                COUNT(anime.id) AS tot_film
              FROM anime
              INNER JOIN bookmarks 
              ON anime.id = bookmarks.anime_id 
              INNER JOIN anime_info
              ON anime.id = anime_info.anime_id
              WHERE bookmarks.chat_id = :user 
              AND bookmarks.list_id = 1 
              AND anime.stagione < 2 
              AND anime_info.categoria = 2";
    $q = $bot->conn->prepare($query);
    $q->bindParam(":user", $user_id);
    $q->execute();
    $film_visti = $q->fetch()["tot_film"];

    //COUNT TOTAL EPISODES WATCHED
    $query = "SELECT 
                COUNT(episodes.id) AS episodes 
              FROM episodes
              INNER JOIN bookmarks
              ON episodes.anime_id = bookmarks.anime_id 
              WHERE bookmarks.chat_id = :user 
              AND bookmarks.list_id = 1";
    $q = $bot->conn->prepare($query);
    $q->bindParam(":user", $user_id);
    $q->execute();
    $episodi = $q->fetch()["episodes"];

    //Calc total time spend with anime
    $hour = $episodi * 24 *60;
    $dtF = new DateTime('@0');
    $dtT = new DateTime("@$hour");
    $ore = $dtF->diff($dtT)->format('%a giorni | %h h | %i min');
    $img = $bot->setting["banner"]["profilo"];
    $nome = $bot->getChat($user_id)["first_name"];
    if(!isset($nome)){ $nome = "Nome Sconsciuto";}
    $text = "<a href='$img'>&#8203;</><b>ℹ️ | Informazioni profilo</>\n\n👤 | <a href='tg://user?id=$user_id'>$nome</>\n💮 | Anime visti: <b>$anime_visti</>\n🎥 | Movie visti: <b>$film_visti</b>\n🔥 | Episodi totali: <b>$episodi</>\n🕓 | <b>".$ore."</b>\n\n🔗 | Link profilo: <b>t.me/myanimetvbot?start=profile_$user_id</b>\n\n⬇️ <b>LISTE ANIME</b> ⬇️";
    $menu[] = [["text" => "❤️ PREFERITI", "callback_data" => "mylist:preferreds_$user_id"."_0"], ["text" => "🔵 COMPLETED", "callback_data" => "mylist:completed_$user_id"."_0"]];
    $menu[] = [["text" => "🟢 WATCHING", "callback_data" => "mylist:watching_$user_id"."_0"], ["text" => "⚪️ PLAN TO WATCH", "callback_data" => "mylist:plntowatch_$user_id"."_0"]];
    if($user_id == $bot->userID){
        $menu[] = [["text" => "📥 IMPORTA", "callback_data" => "mylist:import"], ["text" => "📤 ESPORTA", "callback_data" => "mylist:export"]];
    }
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $bot->edit($text, $menu);
}


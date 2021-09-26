<?php

if($bot->data("newanime")){
    $menu[] = [["text" => "◀️ INDIETRO", "callback_data" => "home:home"]];
    $bot->edit("Ok, invia il poster dell'anime:", $menu);
    $bot->page("post:poster");
}


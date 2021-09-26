<?php
ini_set('display_errors', true);
error_reporting(E_ALL);


include "functions.php";
$token = "BOT_TOKEN";
$bot = new Bot($token, file_get_contents("php://input"));
require_once ("config.php");
require_once ("database.php");

if(isset($bot->update->message)){ //Messages, images ecc...s
    $sections = ["admin", "home", "profile", "simulcast", "post", "search", "view", "start", "mylist", "viabot", "setting", "group"];
    if (strpos($bot->update->message->text, "/start") === 0) { //Gestione degli start
        $e = explode(" ", $bot->msg);
        if(count($e) < 2){ $section = "home";}
        else{
            $section = "start";
            $bot->msg = $e[1];
        }
    }elseif (isset($bot->update->message->via_bot) && $bot->update->message->via_bot->username == 'myanimetvbot') { //Invio messaggio inline sul bot
        if(strstr($bot->msg, "🆔 | ")){
            $section = "start";
            $bot->msg = "animeID_". explode("🆔 | ", $bot->msg)[1];
        }else{ $section = "home"; }
    }elseif($bot->chatID == -1001373617365){
        $section = "admin";
        $bot->cPage("");
        $e = explode(":", $bot->page, 2);
        $bot->sPage($e[1]);
    }else {
        $bot->cPage("");
        $e = explode(":", $bot->page, 2);
        $section = $e[0];
        $bot->sPage($e[1]);
    }
    if (in_array($section, $sections)) {
        require_once ("./plugins/messages/_".$section.".php");
    }else {
        $bot->page("home");
        $bot->reply("Per attivare la nuova versione del bot scrivi /start!");
    }
}elseif (isset($bot->update->callback_query)) {//Buttons clicks
    $sections = ["admin", "home", "profile", "simulcast", "post", "search", "view", "episodes", "player", "scroll", "vote", "bookmark", "mylist", "setting", "market", "top", "group"];
    $e = explode(":", $bot->cbdata, 2);
    $section = $e[0];
    $bot->setcbdata($e[1]);
    //$bot->alert($bot->cbdata);
    if(in_array($section, $sections)) {
        require_once ("./plugins/callbackdata/_".$section.".php");
    }else {
        $bot->page("home:home");
        if($bot->type == "private"){
            $bot->reply("Per attivare la nuova versione del bot scrivi /start!");
        }else{
            $bot->alert("❌ Questa è la vecchia versione del bot\nPer votare l'anime clicca su guarda ora e apri la scheda in privato!");
        }
    }
}else { //Inline
    if (strstr($bot->imsg, "share")) {
        $e = explode(":", $bot->imsg);
        if (isset($e[1])) { $bot->imsg = $e[1]; }
        else { $bot->imsg = "profile"; }
        $section = "share";
    }else {
        if (!empty($bot->imsg)) {
            $section = "search";
            $bot->reply("miau");
        }
    }
    require_once ("./plugins/inline/_".$section.".php");
}
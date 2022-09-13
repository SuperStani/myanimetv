<?php
header('Access-Control-Allow-Origin: https://webapp.myanimetv.org');
ini_set('display_errors', true);
error_reporting(E_ALL);

use superbot\Telegram\Update;
use superbot\Telegram\User;
use superbot\Telegram\Client;
use superbot\App\Controllers\Messages;
use superbot\App\Controllers\Query as Queries;
use superbot\App\Routing\Route;
use superbot\App\Config\GeneralConfigs as cfg;
use superbot\Database\DB;
require_once __DIR__."/vendor/autoload.php";
require __DIR__."/app/langs/getlang.php";

//WebApp update
if(isset($_GET["webapp"], $_GET["hash"], $_GET["to_user"], $_GET["anime"])) {
    $check_hash = $_GET["hash"];
    $anime = $_GET["anime"]; $user = $_GET["to_user"];
    /*--------------------------------|
    |--Check if data is from web app--|
    |--------------------------------*/
    unset($_GET["webapp"], $_GET["hash"], $_GET["to_user"], $_GET["anime"]);
    
    if(isset($_GET["episode"])) {
        $isEpisode = $_GET["episode"];
        unset($_GET["episode"]);
    }
        
    $data_check_arr = [];
    foreach ($_GET as $key => $value) {
      $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);
    $secret_key = hash_hmac('sha256', cfg::get("bot_token"), "WebAppData", true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);

    if (strcmp($hash, $check_hash) === 0 and (time() - $_GET["auth_date"]) < 86400)  {
        if(isset($isEpisode))
            $update = Update::getFakeUpdate($user, "Player:play|$anime|$isEpisode|-1");
        else
            $update = Update::getFakeUpdate($user, "Anime:view|$anime|-1");
    }
    else //Fake update
        die();
}else //Bot update
    $update = Update::get();

if(isset($update->message)) {
    if(isset($update->message->entities) and $update->message->entities[0]->type == "bot_command"){
        Route::get($update->message, Messages\CommandController::class, 'check');
    }else {
        $user = new User($update->message->from, new DB()); 
        $e = explode(":", $user->getPage());
        $user = null;
        if(isset($e[1])){ //Se ci sono parametri nel page
            $section = $e[0]; //la sezione
            $params = explode("|", $e[1]); //Parametri
            $data = $params[0];
            \array_splice($params, 0, 1); //Aggiusto i parametri
            $controller = $section.'Controller';
            Route::get($update->message, Messages::class."\\".$controller, $data, $params);
        }
    }
    die;
}elseif(isset($update->callback_query)) {
    $e = explode(":", $update->callback_query->data);
    $section = $e[0]; //Sezione
    $params = explode("|", $e[1]);
    $data = $params[0];
    \array_splice($params, 0, 1);
    $controller = $section.'Controller';
    //Client::debug(406343901, $update);
    Route::get($update->callback_query, Queries::class."\\".$controller, $data, $params);
    die;
}else {
    
}


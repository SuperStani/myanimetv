<?php
header('Access-Control-Allow-Origin: https://webapp.myanimetv.org');
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/app/langs/getlang.php";

use superbot\App\Routing\Route;
use superbot\Telegram\Update;

//WebApp update
if (isset($_GET["webapp"], $_GET["hash"], $_GET["to_user"], $_GET["anime"])) {
    $check_hash = $_GET["hash"];
    $anime = $_GET["anime"];
    $user = $_GET["to_user"];
    /*--------------------------------|
    |--Check if data is from web app--|
    |--------------------------------*/
    unset($_GET["webapp"], $_GET["hash"], $_GET["to_user"], $_GET["anime"]);

    if (isset($_GET["episode"])) {
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

    if (strcmp($hash, $check_hash) === 0 and (time() - $_GET["auth_date"]) < 86400) {
        if (isset($isEpisode))
            $update = Update::getFakeUpdate($user, "Player:play|$anime|$isEpisode|-1");
        else
            $update = Update::getFakeUpdate($user, "Anime:view|$anime|-1");
    } else //Fake update
        die();
} else //Bot update
    $update = Update::get();

Route::processUpdate($update);

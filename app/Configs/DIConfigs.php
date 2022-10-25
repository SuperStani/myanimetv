<?php

use superbot\App\Configs\DBConfigs;
use superbot\App\Controllers\Controller;
use superbot\App\Controllers\MessageController;
use superbot\App\Controllers\QueryController;
use superbot\App\Controllers\Messages\CommandController;
use superbot\App\Controllers\Messages\HomeController as MessageHomeController;
use superbot\App\Controllers\Messages\PostController as MessagePostController;
use superbot\App\Controllers\Messages\SearchController as MessageSearchController;
use superbot\App\Controllers\Messages\SettingsController as MessageSettingsController;
use superbot\App\Controllers\Query\AnimeController;
use superbot\App\Controllers\Query\BookmarkController;
use superbot\App\Controllers\Query\HomeController;
use superbot\App\Controllers\Query\LeadershipController;
use superbot\App\Controllers\Query\PlayerController;
use superbot\App\Controllers\Query\PostController;
use superbot\App\Controllers\Query\ProfileController;
use superbot\App\Controllers\Query\SearchController;
use superbot\App\Controllers\Query\SettingsController;
use superbot\App\Controllers\Query\SimulcastController;
use superbot\Telegram\Client;
use superbot\Telegram\Message;
use superbot\Telegram\Query;
use superbot\Telegram\Update;
use superbot\Telegram\User;
use superbot\Storage\DB;
use superbot\Storage\CacheService;
use superbot\App\Logger\Log;

use \DI\ContainerBuilder;
use \Redis;
use PDO;

$conf = [
    PDO::class => DI\factory(function () {
        return new PDO("mysql:host=" . DBConfigs::$dbhost . ";dbname=" . DBConfigs::$dbname, DBConfigs::$dbuser, DBConfigs::$dbpassword);
    }),
    Redis::class => DI\factory(function() {
        $redis = new Redis();
        $redis->connect(DBConfigs::$redishost, DBConfigs::$redisport);
        return $redis;
    }),
    DB::class => DI\autowire(),
    Log::class => DI\autowire(),
    CacheService::class => DI\autowire(),
    Query::class => DI\factory(function () {
        return new Message(Update::get()->callback_query);
    }),

    Message::class => DI\factory(function () {
        return new Message(Update::get()->message);
    }),

    User::class => DI\factory(function (DB $db, CacheService $cache) {
        $update = Update::get();
        if (isset($update->message)) {
            return new User($update->message->from, $db, $cache);
        } elseif (isset($update->callback_query)) {
            return new User($update->message->from, $db, $cache);
        }
    }),

    Controller::class => DI\autowire(),
    MessageController::class => DI\autowire(),
    QueryController::class => DI\autowire(),
    MessagePostController::class => DI\autowire(),
    MessageHomeController::class => DI\autowire(),
    MessageSearchController::class => DI\autowire(),
    MessageSettingsController::class => DI\autowire(),
    CommandController::class => DI\autowire(),
    AnimeController::class => DI\autowire(),
    BookmarkController::class => DI\autowire(),
    LeadershipController::class => DI\autowire(),
    HomeController::class => DI\autowire(),
    PlayerController::class => DI\autowire(),
    PostController::class => DI\autowire(),
    ProfileController::class => DI\autowire(),
    SearchController::class => DI\autowire(),
    SettingsController::class => DI\autowire(),
    SimulcastController::class => DI\autowire(),
];

$builder = new ContainerBuilder();
$builder->addDefinitions($conf);
return $builder->build();

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
use superbot\Telegram\Message;
use superbot\Telegram\Query;
use superbot\Telegram\Update;
use superbot\App\Storage\DB;
use superbot\App\Service\CacheService;
use superbot\App\Logger\Log;

use \DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\factory;
use \Redis;
use PDO;
use superbot\App\Storage\RedisController;

$conf = [
    PDO::class => factory(function () {
        return new PDO("mysql:host=" . DBConfigs::$dbhost . ";dbname=" . DBConfigs::$dbname, DBConfigs::$dbuser, DBConfigs::$dbpassword);
    }),
    Redis::class => factory(function() {
        $redis = new Redis();
        $redis->connect(DBConfigs::$redishost, DBConfigs::$redisport);
        return $redis;
    }),

    Update::class => factory(function(ContainerInterface $c) {
        return $c->get(Update::class);
        /*$update = Update::get();

        return (isset($update->message)) ? new Update($update->message) : ((isset($update->callback_query)) ? new Update($update->callback_query) : '');*/
    }),

    DB::class => autowire(),
    RedisController::class => autowire(),
    CacheService::class => autowire(),
    Log::class => autowire(),
    CacheService::class => autowire(),
    Query::class => autowire(),
    Message::class => autowire(),
    Controller::class => autowire(),
    MessageController::class => autowire(),
    QueryController::class => autowire(),
    MessagePostController::class => autowire(),
    MessageHomeController::class => autowire(),
    MessageSearchController::class => autowire(),
    MessageSettingsController::class => autowire(),
    CommandController::class => autowire(),
    AnimeController::class => autowire(),
    BookmarkController::class => autowire(),
    LeadershipController::class => autowire(),
    HomeController::class => autowire(),
    PlayerController::class => autowire(),
    PostController::class => autowire(),
    ProfileController::class => autowire(),
    SearchController::class => autowire(),
    SettingsController::class => autowire(),
    SimulcastController::class => autowire(),
];

$builder = new ContainerBuilder();
$builder->addDefinitions($conf);
return $builder->build();

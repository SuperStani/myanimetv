<?php

namespace superbot\App\Routing;

use superbot\App\Logger\Log;
use superbot\Telegram\Client;
use superbot\Telegram\Update;
use superbot\App\Controllers\UserController;
use function DI\factory;

class Route
{

    public static function processUpdate(Update $update)
    {
        $container = require __DIR__ . "/../Configs/DIConfigs.php";
        $container->set(Update::class, factory(function () {
            global $update;
            return $update;
        }));
        if ($update->getType() == 'message') {
            self::getMessage($container, $update);
        } else {
            self::getCallbackData($container, $update);
        }
    }

    private static function getCallbackData($container, Update $update)
    {
        $e = explode(":", $update->getUpdate()->data);
        $controller = $e[0] . 'Controller';
        $controller = 'superbot\\App\\Controllers\\Query\\' . $controller;
        if (class_exists($controller)) {
            $params = explode("|", $e[1]);
            $method = $params[0];
            unset($params[0]);
            $controller = $container->get($controller);
            return $controller->callAction($method, $params);
        } else {
            $log = $container->get(Log::class);
            $log->warning("");
        }
    }

    private static function getMessage($container, Update $update)
    {
        if (isset($update->getUpdate()->entities) and $update->getUpdate()->entities[0]->type == "bot_command") {
            $controller = $container->get('superbot\App\Controllers\Messages\CommandController');
            return $controller->callAction('check', []);
        } else {
            $user = $container->get(UserController::class);
            $e = explode(":", $user->getPage());
            if (isset($e[1])) { //Se ci sono parametri nel page
                $section = $e[0]; //la sezione
                $params = explode("|", $e[1]); //Parametri
                $method = $params[0];
                unset($params[0]); //Aggiusto i parametri
                $controller =  "superbot\App\Controllers\Messages\\" . $section . "Controller";
                if (class_exists($controller)) {
                    $container->injectOn($user);
                    $controller = $container->get($controller);
                    return $controller->callAction($method, $params);
                }
            }
        }
    }
}

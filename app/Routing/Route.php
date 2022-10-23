<?php

namespace superbot\App\Routing;

use superbot\App\Logger\log;

class Route
{

    public static function processUpdate($update)
    {
        $container = require __DIR__ . "../Configs/DIConfigs.php";
        if (isset($update->message)) {
            self::getMessage($container, $update);
        } else {
            self::getCallbackData($container, $update);
        }
    }

    private static function getCallbackData($container, $update)
    {
        $e = explode(":", $update->callback_query->data);
        $controller = $e[0] . 'Controller';
        if (class_exists($controller::class)) {
            $params = explode("|", $e[1]);
            $method = $params[0];
            unset($params[0]);
            $controller = $container->get($controller::class);
            return $controller->callAction($method, $params);
        } else {
            $log = $container->get(Log::class);
            $log->warning("");
        }
    }

    private static function getMessage($container, $update)
    {
        if (isset($update->message->entities) and $update->message->entities[0]->type == "bot_command") {
            $controller = $container->get(Messages\CommandController::class);
            return $controller->callAction('check', []);
        } else {
            $user = $container->get(User::class);
            $e = explode(":", $user->getPage());
            $user = null;
            if (isset($e[1])) { //Se ci sono parametri nel page
                $section = $e[0]; //la sezione
                $params = explode("|", $e[1]); //Parametri
                $method = $params[0];
                unset($params[0]); //Aggiusto i parametri
                $controller = $section . 'Controller';
                if (class_exists($controller::class)) {
                    $controller = $container->get($controller::class);
                    return $controller->callAction($method, $params);
                }
            }
        }
    }
}

<?php

namespace superbot\App\Routing;

class Route {
    public static function get($update, $class, $method, $params = []) {
        if(class_exists($class)) {
            $controller = new $class($update);
            return $controller->callAction($method, $params);
        }else{
            if(isset($update->data)) //query
            {
                $controller = new \superbot\App\Controllers\QueryController($update);
                $controller->callAction('error', []);
            }else 
            {
                $controller = new \superbot\App\Controllers\MessageController($update);
                $controller->callAction('error', []);
            }
        }
    }
} 
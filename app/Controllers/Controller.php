<?php

namespace superbot\App\Controllers;

abstract class Controller {
    public function callAction($method, array $params) {
        $this->user->update();
        if($this->user->isAdmin())
            return $this->{$method}(...array_values($params));
        else 
            return 0;
    }
}
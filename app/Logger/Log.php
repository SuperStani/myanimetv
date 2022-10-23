<?php

namespace app\Logger;

class Log
{
    private static $conn;
    public function __construct(DB $conn) {
        $this->conn = $conn;
    }

    public function error($error_type, $message) {
        //$this->conn->wquery();
    }

    public static function warning($message) {
        syslog(LOG_ERR, $message);
    }
}

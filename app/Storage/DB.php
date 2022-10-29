<?php

namespace superbot\App\Storage;

use superbot\App\Configs\DBConfigs;
use superbot\App\Logger\Log;
use PDO;
use PDOException;

class DB
{
    private $conn;
    private $logger;
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
        try {
            $this->conn = new PDO("mysql:host=" . DBConfigs::$dbhost . ";dbname=" . DBConfigs::$dbname, DBConfigs::$dbuser, DBConfigs::$dbpassword);
        } catch (PDOException $e) {
            $logger->warning($e->getMessage());
        }
    }

    public function rquery($query, ...$vars)
    {
        $conn = $this->conn;
        $q = $conn->prepare($query);
        foreach ($vars as $key => &$value) {
            $key = $key + 1;
            $q->bindParam($key, $value);
        }
        $q->execute();
        $conn = null;
        return $q->fetchObject();
    }

    public function rqueryAll($query, ...$vars)
    {
        $conn = $this->conn;
        $q = $conn->prepare($query);
        foreach ($vars as $key => &$value) {
            $key = $key + 1;
            if (is_numeric($value))
                $q->bindParam($key, $value, PDO::PARAM_INT);
            else
                $q->bindParam($key, $value);
        }
        $q->execute();
        $conn = null;
        return $q->fetchAll(PDO::FETCH_OBJ);
    }


    public function wquery($query, ...$vars)
    {
        $conn = $this->conn;
        $q = $conn->prepare($query);
        foreach ($vars as $key => &$value) {
            $key = $key + 1;
            $q->bindParam($key, $value);
        }
        try {
            $q->execute();
            try {
                $q = $conn->lastInsertId();
            } catch (PDOException $e) {
                //...
            }
            $conn = null;
            return $q;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}

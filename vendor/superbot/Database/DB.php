<?php

namespace superbot\Database;
use superbot\App\Config\GeneralConfigs;
use PDO;
use Exception;
use PDOException;
use superbot\Telegram\Client;

class DB{

	public function __construct() {
		$this->conn = self::connect();
	}

    public static function connect() {
        try{
        	$conn = new PDO("mysql:host=".GeneralConfigs::get("dbhost").";dbname=".GeneralConfigs::get("dbname"), GeneralConfigs::get("dbuser"), GeneralConfigs::get("dbpassword"));
    	}catch(PDOException $e){
        	throw new Exception($e->getMessage());
    	}
		return $conn;
    }

	public function rquery($query, ...$vars) {
		$conn = $this->conn;
		$q = $conn->prepare($query);
		foreach($vars as $key => &$value) {
            $key = $key + 1;
			$q->bindParam($key, $value);
		}
		$q->execute();
		$conn = null;
		return $q->fetchObject();
	}

    public function rqueryAll($query, ...$vars) {
		$conn = $this->conn;
		$q = $conn->prepare($query);
		foreach($vars as $key => &$value) {
            $key = $key + 1;
			if(is_numeric($value))
				$q->bindParam($key, $value, PDO::PARAM_INT);
			else
				$q->bindParam($key, $value);
		}
		$q->execute();
		$conn = null;
		return $q->fetchAll(PDO::FETCH_OBJ);
	}
    

	public function wquery($query, ...$vars) {
		$conn = $this->conn;
		$q = $conn->prepare($query);
		foreach($vars as $key => &$value) {
			$key = $key + 1;
			$q->bindParam($key, $value);
		}
		try{
			$q->execute();
            try{
                $q = $conn->lastInsertId();
            }catch(PDOException $e) {
                //...
            }
			$conn = null;
			return $q;
		} catch(PDOException $e) {
			return $e->getMessage();
		}
	}


    public function transaction(array $queries) {
        $conn = DB::connect();
        try{
            $conn->beginTransaction();
            foreach($queries as $query) {
                $q = $conn->prepare($query[0]);
                foreach($query[1] as $key => &$holder) {
                    $key = $key + 1;
                    $q->bindParam($key, $value);
                }
                $q->execute();
            }
            $conn->commit();
            return 1;
        } catch(PDOException $e){
            $conn->rollBack();
            return 0;
        }
        
    }

}
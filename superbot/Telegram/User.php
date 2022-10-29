<?php

namespace superbot\Telegram;
use superbot\Telegram\Client;
use superbot\App\Storage\Repositories\UserRepository;
use superbot\Storage\CacheService;

class User {
    public $id;
    public $name;
    public $lastname;
    public string $mention;
    public $page;

    public function __construct(
        $user, 
        UserRepository $userRepo, 
        CacheService $cacheService
    )
    {
        $this->id = $user->id;
        $this->name = $user->first_name;
        $this->mention = "[".$user->first_name."](tg://user?id=".$user->id.")";
        $this->conn = $conn;
        $this->cache = $cacheService;
    }

    public function getMe() {
        return Client::getChat($this->id)->result;
    }

    public function save(){
        $q = $this->conn->wquery("INSERT INTO users SET id = ?", $this->id);
        return $q;
    }


    public function update() {
        $q = $this->conn->wquery("UPDATE users SET last_update = NOW() WHERE id = ?", $this->id);
        return $q;
    }
    
    public function page(string $text = null){
        if($text == null) 
            $q = $this->pageDefault();
        else
            $q = $this->conn->wquery("UPDATE users SET page = ? WHERE id = ?", $text, $this->id);
        return $q;
    }

    public function pageDefault() {
        $q = $this->conn->wquery("UPDATE users SET page = ? WHERE id = ?", "Search:q", $this->id);
        return $q;
    }

    public function getPage(){
        $page = $this->conn->rquery("SELECT page FROM users WHERE id = ?", $this->id)->page;
        return $page;
    }

    public function isAdmin(){
        $admins = [
            406343901, //SuperStani
            198253421, //Kami
            737539655, //Stani2
            154658214, //Ester
            156371150, //Fra9898
            808699539, //GiorgioAmbulante69
            856835224 //Yukumi
        ];
        return in_array($this->id, $admins);
    }

}
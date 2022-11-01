<?php

namespace superbot\App\Storage\Entities;

class Episode
{
    private $id;
    private $url;
    private $number;

    public function __construct()
    {
      
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setNumber($number) {
        $this->number = $number;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}

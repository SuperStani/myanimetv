<?php

namespace superbot\App\Storage\Entities;

class Genre
{
    private $id;
    private $name;
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

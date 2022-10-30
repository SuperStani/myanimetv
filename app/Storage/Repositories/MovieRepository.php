<?php

namespace superbot\App\Storage\Repositories;

use superbot\App\Storage\DB;

class MovieRepository
{
    private $conn;
    private static $table = 'movie';
    public function __construct(DB $conn)
    {
        $this->conn = $conn;
    }

    public function getTotalMovies(): int
    {
        $query = "SELECT COUNT(*) AS tot FROM " . self::$table;
        return $this->conn->rquery($query)->tot;
    }

    public function getTotalEpisodes(): int
    {
        $query = "SELECT COUNT(*) AS tot FROM episodes";
        return $this->conn->rquery($query)->tot;
    }
}

<?php

namespace superbot\App\Storage\Repositories;

use superbot\App\Storage\DB;
use superbot\App\Storage\Entities\Episode;

class EpisodeRepository
{
    private $conn;
    private static $table = 'episodes';
    public function __construct(DB $conn)
    {
        $this->conn = $conn;
    }

    public function getTotalEpisodes(): int
    {
        $query = "SELECT COUNT(*) AS tot FROM " . self::$table;
        return $this->conn->rquery($query)->tot;
    }

    public function add(Episode $e, $movie_id)
    {
        $query = "INSERT INTO netfluzmax." . self::$table . " SET url = ?, episodeNumber = ?, movie = ?";
        return $this->conn->wquery(
            $query,
            $e->getUrl(),
            $e->getNumber(),
            $movie_id
        );
    }

    public function removeEpisodeFromMovieId(Episode $episode, $movie_id)
    {
    }
}

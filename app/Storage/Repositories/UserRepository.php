<?php

namespace superbot\App\Storage\Repositories;

use superbot\App\Storage\DB;

class UserRepository
{
    private $conn;
    private static $table = 'users';
    public function __construct(DB $conn)
    {
        $this->conn = $conn;
    }

    public function page($user_id, $text = null)
    {
        $page = "Search:q";
        if ($text != null)
            $page = $text;
        $query = "UPDATE " . self::$table . " SET page = ? WHERE id = ?";
        $this->conn->wquery($query, $page, $user_id);
    }

    public function getPage($user_id): string
    {
        $query = "SELECT page FROM " . self::$table . " WHERE id = ?";
        $page = $this->conn->rquery($query, $user_id)->page;
        return $page;
    }

    public function save($user_id): void
    {
        $query = "INSERT INTO " . self::$table . " SET id = ?";
        $this->conn->wquery($query, $user_id);
    }

    public function updateLastAction($user_id)
    {
        $query = "UPDATE " . self::$table . " SET last_update = NOW() WHERE id = ?";
        $this->conn->wquery($query, $user_id);
    }

    public function getMovieListByListType($user_id, $type): ?array
    {
        return null;
    }

    public function getPreferredMovies($user_id): ?array
    {
        return null;
    }

    public function getTotalWatchingTimeOnMovies($user_id): int
    {
        return 0;
    }

    public function getTotalEpisodesWatched($user_id): int
    {
        return 0;
    }

    public function getMoviesHistory($user_id)
    {
    }

    public function getTotalUsers(): int
    {
        $query = "SELECT COUNT(*) AS tot FROM " .  self::$table;
        return $this->conn->rquery($query)->tot;
    }
}

<?php

namespace superbot\App\Storage\Repositories;

use Exception;
use superbot\App\Storage\Entities\Genre;
use superbot\App\Storage\DB;

class GenreRepository
{
    private $conn;
    private static $table = 'genres';

    public function __construct(DB $conn)
    {
        $this->conn = $conn;
    }

    public function add($name)
    {
        $query = "INSERT INTO netfluzmax." . self::$table . " SET name = ?";
        try{
            $this->conn->wquery($query, $name);
        }catch(Exception $e) {}
    }

    public function getGenreByName($name): ?Genre
    {
        $query = "SELECT id, name FROM " . self::$table . " WHERE name = ?";
        $genre = $this->conn->rquery($query, $name);
        return new Genre($genre->id, $genre->name);
    }

    public function getGenresByMovieId($id): array
    {
        $query = "SELECT id, name FROM movie_" . self::$table . " WHERE movie = ?";
        $result = $this->conn->rqueryAll($query, $id);
        $r = [];
        foreach ($result as $genre_row) {
            $genre = new Genre($genre_row->id, $genre_row->name);
            $r[] = $genre;
        }
        return $r;
    }

    public function addGenreToMovie(Genre $genre, $movie_id)
    {
        $query = "INSERT INTO netfluzmax.movie_" . self::$table . " SET movie = ?, genre = ?";
        $this->conn->wquery($query, $movie_id, $genre->getId());
    }

    public function removeGenreFromMovieId(Genre $genre, $movie_id)
    {
        $query = "DELETE FROM movie_" . self::$table . "WHERE movie = ? AND genre = ?";
        $this->conn->wquery($query, $movie_id, $genre->getId());
    }
}

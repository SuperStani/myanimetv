<?php

namespace superbot\App\Storage\Repositories;

use superbot\App\Storage\DB;
use superbot\App\Storage\Entities\Movie;
use superbot\App\Storage\Entities\Genre;

class MovieRepository
{
    private $conn;
    private $episodeRepo;
    private $genreRepo;
    private static $table = 'movie';
    public function __construct(
        DB $conn,
        EpisodeRepository $episodeRepo,
        GenreRepository $genreRepo
    )
    {
        $this->conn = $conn;
        $this->episodeRepo = $episodeRepo;
        $this->genreRepo = $genreRepo;
    }

    public function getTotalMovies(): int
    {
        $query = "SELECT COUNT(*) AS tot FROM " . self::$table;
        return $this->conn->rquery($query)->tot;
    }

    public function getTotalEpisodes(): int
    {
        return $this->episodeRepo->getTotalEpisodes();
    }

    public function searchMoviesbyNameOrSynopsys($q): array
    {
        $query = "SELECT * FROM " . self::$table . " WHERE name LIKE ? OR synonyms LIKE ?";
        $results = $this->conn->rqueryALl($query, '%' . $q . '%', '%' . $q . '%');
        $r = [];
        foreach ($results as $row) {
            $movie = new Movie();
            $movie->setId($row->id);
            $movie->setName($row->name);
            $movie->setAiredOn($row->aired_on);
            $movie->setCategory($row->category);
            $movie->setEpisodesNumber($row->episodes);
            $movie->setPoster($row->poster);
            $movie->setSynonyms($row->synonyms);
            $movie->setTrailer($row->trailer);
            $movie->setSynopsis($row->synopsis);
            $movie->setSynopsisUrl($row->synopsis_url);
            $movie->setDuration($row->duration);
            $movie->setSeason($row->season);
            $movie->setGenres($this->getGenres($movie->getId()));
            $r[] = $movie;
        }
        return $r;
    }

    public function getMoviesbyCategory($q): array
    {
        $query = "SELECT * FROM " . self::$table . " WHERE category = ?";
        $results = $this->conn->rqueryAll($query, $q);
        $r = [];
        foreach ($results as $row) {
            $movie = new Movie();
            $movie->setId($row->id);
            $movie->setName($row->name);
            $movie->setAiredOn($row->aired_on);
            $movie->setCategory($row->category);
            $movie->setEpisodesNumber($row->episodes);
            $movie->setPoster($row->poster);
            $movie->setSynonyms($row->synonyms);
            $movie->setTrailer($row->trailer);
            $movie->setSynopsis($row->synopsis);
            $movie->setSynopsisUrl($row->synopsis_url);
            $movie->setGenres($this->getGenres($movie->getId()));
            $r[] = $movie;
        }
        return $r;
    }

    public function getGenres($id): array
    {
        return $this->genreRepo->getGenresByMovieId($id);
    }

    public function Save(Movie $movie): int
    {
        $query = "
            INSERT INTO netfluzmax.movie 
            SET name = ?,
                synonyms = ?,
                poster = ?,
                aired_on = ?,
                episodes = ?,
                synopsis = ?,
                synopsis_url = ?,
                category = ?,
                season = ?,
                trailer = ?,
                duration = ?
        ";
        return $this->conn->wquery(
            $query,
            $movie->getName(),
            $movie->getSynonyms(),
            $movie->getPoster(),
            $movie->getAiredOn(),
            $movie->getEpisodesNumber(),
            $movie->getSynopsis(),
            $movie->getSynopsisUrl(),
            $movie->getCategory(),
            $movie->getSeason(),
            $movie->getTrailer(),
            $movie->getDuration()
        );
    }

    public function Update(Movie $movie)
    {
        $query = "
            UPDATE movie 
            SET name = ?,
                synonyms = ?,
                poster = ?,
                aired_on = ?,
                episodes = ?,
                synopsis = ?,
                synopsis_url = ?,
                category = ?,
                season = ?,
                trailer = ?,
                duration = ?
            WHERE id = ?
        ";
        $this->conn->wquery(
            $query,
            $movie->getName(),
            $movie->getSynonyms(),
            $movie->getPoster(),
            $movie->getAiredOn(),
            $movie->getEpisodesNumber(),
            $movie->getSynopsis(),
            $movie->getSynopsisUrl(),
            $movie->getCategory(),
            $movie->getSeason(),
            $movie->getTrailer(),
            $movie->getDuration(),
            $movie->getId()
        );
    }
}

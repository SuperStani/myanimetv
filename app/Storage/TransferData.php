<?php

namespace superbot\App\Storage;

use superbot\App\Storage\Repositories\MovieRepository;
use superbot\App\Storage\Repositories\GenreRepository;
use superbot\App\Storage\Entities\Movie;
use superbot\App\Storage\Entities\Episode;
use superbot\App\Storage\Repositories\EpisodeRepository;

class TransferData
{
    private $movieRepo;
    private $genreRepo;
    private $episodeRepo;
    private $conn;
    public function __construct(
        MovieRepository $movieRepo,
        GenreRepository $genreRepo,
        EpisodeRepository $episodeRepo,
        DB $conn
    ) {
        $this->movieRepo = $movieRepo;
        $this->conn = $conn;
        $this->genreRepo = $genreRepo;
        $this->episodeRepo = $episodeRepo;
    }

    public function transferFilms()
    {
        $query = "SELECT m.*, f.durata FROM maxstream.movies m INNER JOIN maxstream.films f ON m.id = f.id WHERE m.title IS NOT NULL AND m.episodes IS NOT NULL";
        $results = $this->conn->rqueryAll($query);
        var_dump($results);
        foreach ($results as $row) {
            $episodes = explode("\n", $row->episodes);
            $movie = new Movie();
            $movie->setName($row->title);
            $movie->setAiredOn($row->aired_year);
            $movie->setCategory("FILM");
            $movie->setEpisodesNumber(count($episodes));
            $movie->setPoster($row->poster_id);
            $movie->setSynonyms("");
            $movie->setTrailer("#");
            $movie->setSynopsis("Non disponibile...");
            $movie->setSynopsisUrl("#");
            $movie->setSeason(0);
            $movie->setDuration((int)$row->durata);
            $movie_id = $this->movieRepo->Save($movie);
            $genres = explode("#", str_replace(" ", "", $row->genre));

            foreach($genres as $row) {
                if($row == '')
                    continue;
                $this->genreRepo->add($row);
                $g = $this->genreRepo->getGenreByName($row);
                $this->genreRepo->addGenreToMovie($g, $movie_id);
            }

            foreach($episodes as $key => $e) {
                $epi = new Episode();
                $epi->setUrl($e);
                $epi->setNumber($key + 1);
                $this->episodeRepo->add($epi, $movie_id);
            }
        }
    }

    public function transferSeries()
    {
        $query = "SELECT m.*, s.stagione FROM maxstream.movies m INNER JOIN maxstream.series s ON m.id = s.id WHERE m.title IS NOT NULL AND m.episodes IS NOT NULL";
        $results = $this->conn->rqueryAll($query);
        foreach ($results as $row) {
            $episodes = explode("\n", $row->episodes);
            $movie = new Movie();
            $movie->setName($row->title);
            $movie->setAiredOn($row->aired_year);
            $movie->setCategory("TVSERIES");
            $movie->setEpisodesNumber(count($episodes));
            $movie->setPoster($row->poster_id);
            $movie->setSynonyms("");
            $movie->setTrailer("#");
            $movie->setSynopsis("Non disponibile...");
            $movie->setSynopsisUrl("#");
            $movie->setSeason($row->stagione);
            $movie_id = $this->movieRepo->Save($movie);
            $genres = explode("#", str_replace(" ", "", $row->genre));

            foreach($genres as $row) {
                if($row == '')
                    continue;
                $this->genreRepo->add($row);
                $g = $this->genreRepo->getGenreByName($row);
                $this->genreRepo->addGenreToMovie($g, $movie_id);
            }

            foreach($episodes as $key => $e) {
                $epi = new Episode();
                $epi->setUrl($e);
                $epi->setNumber($key + 1);
                $this->episodeRepo->add($epi, $movie_id);
            }
        }
    }
}

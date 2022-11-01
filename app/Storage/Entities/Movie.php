<?php

namespace superbot\App\Storage\Entities;

class Movie
{
    private $id;
    private $name;
    private $synonyms;
    private $poster;
    private $episodesNumber;
    private $trailer;
    private $synopsis;
    private $synopsisUrl;
    private $genres;
    private $category;
    private $airedOn;
    private $season;
    private $duration;

    public function __construct()
    {
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSynonyms($synonyms)
    {
        $this->synonyms = $synonyms;
    }

    public function getSynonyms(): string
    {
        return $this->synonyms;
    }

    public function setPoster($poster)
    {
        $this->poster = $poster;
    }

    public function getPoster(): int
    {
        return $this->poster;
    }

    public function setEpisodesNumber($n)
    {
        $this->episodesNumber = $n;
    }

    public function getEpisodesNumber(): int
    {
        return $this->episodesNumber;
    }

    public function setTrailer($trailer)
    {
        $this->trailer = $trailer;
    }

    public function getTrailer(): string
    {
        return $this->trailer;
    }

    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;
    }

    public function getSynopsis(): string
    {
        return $this->synopsis;
    }

    public function setSynopsisUrl($synopsisUrl)
    {
        $this->synopsisUrl = $synopsisUrl;
    }

    public function getSynopsisUrl(): string
    {
        return $this->synopsisUrl;
    }

    public function setGenres($genres)
    {
        $this->genres = $genres;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function getParsedGenres(): string
    {
        return $this->genres;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setAiredOn($airedOn)
    {
        $this->airedOn = $airedOn;
    }

    public function getAiredOn(): string
    {
        return $this->airedOn;
    }

    public function setSeason($season)
    {
        $this->season = $season;
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getParsedSeason(): string
    {
        return ($this->season == 0) ? '' : 'S' . $this->season;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }
}

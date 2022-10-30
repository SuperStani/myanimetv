<?php

namespace superbot\App\Controllers;

use superbot\App\Configs\GeneralConfigs;
use superbot\Telegram\Client;
use superbot\App\Storage\Repositories\UserRepository;
use superbot\Telegram\Update;

class UserController extends Controller
{
    public $id;
    public $name;
    public $lastname;
    public $mention;
    public $page;
    private $userRepo;

    public function __construct(
        Update $user,
        UserRepository $userRepo
    ) {
        $this->id = $user->from->id;
        $this->name = $user->from->first_name;
        $this->mention = "[" . $user->from->first_name . "](tg://user?id=" . $user->from->id . ")";
        $this->userRepo = $userRepo;
    }

    public function getMe()
    {
        return Client::getChat($this->id)->result;
    }

    public function save()
    {
        $this->userRepo->save($this->id);
    }

    public function update()
    {
        $this->userRepo->updateLastAction($this->id);
    }

    public function page($text = null)
    {
        $this->userRepo->page($this->id, $text);
    }

    public function getPage()
    {
        return $this->userRepo->getpage($this->id);
    }

    public function isAdmin()
    {
        return in_array($this->id, GeneralConfigs::$admins);
    }

    public function getMovieListByListType($type): ?array
    {
        return $this->userRepo->getMovieListByListType($this->id, $type);
    }

    public function getPreferredMovies(): ?array
    {
        return $this->userRepo->getPreferredMovies($this->id);
    }

    public function getTotalWatchingTimeOnMovies(): int
    {
        return $this->userRepo->getTotalWatchingTimeOnMovies($this->id);
    }

    public function getTotalEpisodesWatched(): int
    {
        return $this->userRepo->getTotalEpisodesWatched($this->id);
    }

    public function getMoviesHistory()
    {
        return $this->userRepo->getMoviesHistory($this->id);
    }
}

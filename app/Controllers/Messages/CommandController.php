<?php

namespace superbot\App\Controllers\Messages;

use superbot\App\Controllers\MessageController;
use superbot\App\Controllers\Query\MovieController;
use superbot\App\Controllers\Query\PlayerController;
use superbot\Telegram\Client;
use superbot\App\Configs\GeneralConfigs as cfg;
use superbot\App\Controllers\UserController;
use superbot\App\Logger\Log;
use superbot\App\Services\CacheService;
use superbot\App\Storage\Repositories\MovieRepository;
use superbot\App\Storage\Repositories\UserRepository;
use superbot\Telegram\Message;


class CommandController extends MessageController
{
    private $movieRepo;
    private $userRepo;
    private $cacheService;
    public function __construct(
        Message $message,
        UserController $user,
        MovieRepository $movieRepo,
        UserRepository $userRepo,
        CacheService $cacheService,
        Log $logger
    ) {
        $this->message = $message;
        $this->user = $user;
        $this->logger = $logger;
        $this->movieRepo = $movieRepo;
        $this->userRepo = $userRepo;
        $this->cacheService = $cacheService;
    }

    public function start($param = null)
    {
        if (!$param) {
            $this->user->save();
            $this->user->page();
            if ($this->user->isAdmin())
                $menu[] = [["text" => "➕ ADD NEW MOVIE", "callback_data" => "Post:new"]];
            $menu[] = [["text" => get_button('it', 'search'), "callback_data" => "Search:home|0"], ["text" => get_button('it', 'profile'), "callback_data" => "Profile:me|0"]];
            $menu[] = [["text" => get_button('it', 'top'), "callback_data" => "Top:home"]];
            if(($text = $this->cacheService->getStartMessage()) == null) {
                $text = get_string(
                    'it',
                    'home',
                    $this->user->mention,
                    $this->userRepo->getTotalUsers(),
                    $this->movieRepo->getTotalMovies(),
                    $this->movieRepo->getTotalEpisodes()
                );
                $this->cacheService->setStartMessage($text);
            }
            return $this->message->reply($text, $menu);
        } else {
            $param = explode("_", $param);
            /*if ($param[0] == "movie")
                return $this->sendmovie($param[1]);*/
            if ($param[0] == "settings")
                return $this->sendSettings($param[1], $param[2]);
        }
    }

    /*public function sendmovie($id)
    {
        $update = Update::getFakeUpdate($this->user->id, "movie:view|$id|-1");
        $movie = new MovieController($update->callback_query, $this->conn, $this->user, $this->logger);
        return $movie->view($id, -1);
    }*/


    public function sendSettings($id, $message_id)
    {
        $this->message->delete();
        $menu[] = [["text" => "✏️ TITOLO", "callback_data" => "Settings:title|$id"], ["text" => "✏️ POSTER", "callback_data" => "Settings:poster|$id"]];
        $menu[] = [["text" => "✏️ NR. STAGIONE", "callback_data" => "Settings:season|$id"], ["text" => "✏️ GRUPPO", "callback_data" => "Settings:group|$id"]];
        $menu[] = [["text" => "✏️ DURATA EP", "callback_data" => "Settings:duration|$id"], ["text" => "✏️ GENERI", "callback_data" => "Settings:genres|$id"]];
        $menu[] = [["text" => "✏️ NR.EP", "callback_data" => "Settings:episodes|$id"], ["text" => "✏️ DATA", "callback_data" => "Settings:aired_on|$id"]];
        $menu[] = [["text" => "✏️ TRAMA", "callback_data" => "Settings:|$id|synopsis"], ["text" => "✏️ TRAILER", "callback_data" => "Settings:trailer|$id"]];
        $menu[] = [["text" => "✏️ CATEGORIA", "callback_data" => "Settings:category|$id"], ["text" => "✏️ STUDIO", "callback_data" => "Settings:studio|$id"]];
        $menu[] = [["text" => "🔁 REIMPOSTA", "callback_data" => "Settings:reloadInfo|$id"], ["text" => "🗑 ELIMINA EP", "callback_data" => "Settings:deleteEpisodes|$id|0"]];
        $issimulcast = isset($this->conn->rquery("SELECT movie FROM movie_simulcasts WHERE movie = ?", $id)->movie);
        if ($issimulcast) {
            $menu[] = [["text" => "🖌 TITOLO ON-GOING", "callback_data" => "Simulcast:title|$id"]];
            $menu[] = [["text" => "🖌 IMMAGINE ON-GOING", "callback_data" => "Simulcast:poster|$id"]];
            $menu[] = [["text" => "❌ RIMUOVI ON-GOING", "callback_data" => "Simulcast:remove|$id"]];
        } else {
            $menu[] = [["text" => "✳️ IMPOSTA ON-GOING", "callback_data" => "Simulcast:settup|$id"]];
        }
        $menu[] = [["text" => "➕ AGGIUNGI EPISODIO", "callback_data" => "Settings:uploadEpisode|$id"]];
        $menu[] = [["text" => "📤 INVIA", "callback_data" => "Settings:sendmovie|$id"], ["text" => "🗑 ELIMINA", "callback_data" => "Settings:removemovie|$id"]];
        $menu[] = [["text" => "✖️ CHIUDI IMPOSTAZIONI ✖️", "callback_data" => "Settings:close"]];
        return $this->message->reply("*Seleziona un'opzione qua sotto:*", $menu, 'Markdown', false);
    }

    public function check()
    {
        $e = explode(" ", $this->message->text);
        $method = str_replace("/", "", $e[0]);
        unset($e[0]);
        return $this->callAction($method, $e);
    }
}

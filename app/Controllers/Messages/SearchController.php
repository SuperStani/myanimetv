<?php

namespace superbot\App\Controllers\Messages;

use superbot\App\Controllers\MessageController;
use superbot\Telegram\Client;
use superbot\App\Logger\Log;
use superbot\Telegram\Message;
use superbot\App\Controllers\UserController;
use superbot\App\Storage\Repositories\MovieRepository;

class SearchController extends MessageController
{
    protected $movieRepo;
    public function __construct(
        Message $message,
        UserController $user,
        MovieRepository $movieRepo,
        Log $logger
    ) {
        $this->message = $message;
        $this->user = $user;
        $this->logger = $logger;
        $this->movieRepo = $movieRepo;
    }
    public function q($message_id = null)
    {
        if ($message_id != null)
            Client::deleteMessage($this->user->id, $message_id);
        $this->message->delete();
        $movies = $this->movieRepo->searchMoviesbyNameOrSynopsys($this->message->text);

        if (count($movies) == 0) {
            $this->message->reply("Non ho trovato niente!");
        }

        $text = "Risultati per \"{$this->message->text}\":\n";
        foreach ($movies as $key => $movie) {
            if ($key == 11)
                break;
            $text .= ">> [" . $movie->getName() . " " . $movie->getParsedSeason() . "](" . $movie->getId() . ")\n";
        }
        $this->message->reply($text);
        //Client::debug($this->movieRepo->searchMoviesbyNameOrSynopsys($this->message->text));
        /*$results = $this->conn->rquery("SELECT COUNT(*) AS tot FROM movie WHERE name LIKE ? OR synonyms LIKE ?", '%'.$this->message->text.'%', '%'.$this->message->text.'%');
        if($results->tot){
            $menu[] = [["text" => get_button('it', 'search_results'), "web_app" => ["url" => "https://webapp.mymovietv.org/search/q:".urlencode($this->message->text)]]];
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0"]];
            $this->message->reply(get_string('it', 'search_results', $this->message->text, $results->tot), $menu);
        }else{
            $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Search:home|0"]];
            $this->message->reply(get_string('it', 'result_not_found'), $menu);
        }*/
    }

    public function groupFormovie($id, $message_id)
    {
        $this->message->delete();
        $groups = $this->conn->rqueryAll("SELECT id, name FROM groups_list WHERE name LIKE ? LIMIT 10", "%{$this->message->text}%");
        foreach ($groups as $group) {
            $menu[] = [["text" => $group->name, "callback_data" => "Settings:addInGroup|$id|$group->id"]];
        }
        $menu[] = [["text" => get_button('it', 'back'), "callback_data" => "Settings:group|$id"]];
        $keyboard["inline_keyboard"] = $menu;
        Client::editMessageText($this->user->id, $message_id, null, "Seleziona il gruppo", "html", null, false, $keyboard);
    }
}

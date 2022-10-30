<?php

namespace superbot\Telegram;

use superbot\Telegram\Client;

class Query
{
    public $message;
    public $data, $id;
    public $chat_id;
    public function __construct(Update $update)
    {
        $this->data = $update->data;
        $this->id = $update->id;
        $this->message = new Message($update->message);
        $update = null;
    }

    public function alert(string $text = "💙", bool $show = false, string $url = null)
    {
        return Client::answerCallbackQuery($this->id, $text, $show, $url);
    }

    public function editButton(array $menu)
    {
        $keyboard["inline_keyboard"] = $menu;
        return Client::editMessageReplyMarkup($this->message->chat_id, $this->message->id, null, $keyboard);
    }
}

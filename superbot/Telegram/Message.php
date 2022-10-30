<?php

namespace superbot\Telegram;
use superbot\Telegram\Client;

class Message {
    public $text, $id, $chat_type, $chat_id;
    public $photo, $video;
    public $keyboard;
    public function __construct(Update $message)
    {
        $this->text = $message->text;
        $this->id = $message->message_id;
        $this->chat_id = $message->chat->id;
        $this->chat_type = $message->chat->type;
        if (isset($message->photo))
            $this->photo = $message->photo;
        if(isset($message->video))
            $this->video = $message->video;
        
        if (isset($message->reply_markup->inline_keyboard))
            $this->keyboard = $message->reply_markup->inline_keyboard;
        
        $message = null;
    }

    public function reply(string $text, $menu = null, $parse = "Markdown", bool $disable_preview = false, $reply_to_message = null) {
        if($menu != null)
            $keyboard["inline_keyboard"] = $menu;
        else
            $keyboard = null;
        return Client::sendMessage($this->chat_id, $text, $parse, null, $disable_preview, null, null, $reply_to_message, null, $keyboard);
    }
    

    public function reply_photo(string $photo, string $caption = "", $menu = null, $parse = "Markdown", bool $disable_preview = false, $reply_to_message = null) {
        if($menu != null)
            $keyboard["inline_keyboard"] = $menu;
        else
            $keyboard = null;
        return Client::sendPhoto($this->chat_id, $photo, $caption, $parse, null, null, null, $reply_to_message, null, $keyboard);
    }

    public function edit_media(string $media, string $caption = "", $menu = null, string $type_media = 'photo', $parse = "Markdown", bool $disable_preview = false, $reply_to_message = null) {
        if($menu != null)
            $keyboard["inline_keyboard"] = $menu;
        else
            $keyboard = null;
        return Client::editMessageMedia($this->chat_id, $this->id, null, ["type" => $type_media, "media" => $media, "caption" => $caption, "parse_mode" => $parse], $keyboard);
    }
    

    public function edit(string $text, array $menu = null, string $parse = "Markdown", bool $disable_preview = null) {
        if($menu != null)
            $keyboard["inline_keyboard"] = $menu;
        else
            $keyboard = null;
        return Client::editMessageText($this->chat_id, $this->id, null, $text, $parse, null, $disable_preview, $keyboard);
    }

    public function delete(){
        return Client::deleteMessage($this->chat_id, $this->id);
    }

    public function copy($chat_id){
        return Client::copyMessage($chat_id, $this->chat_id, $this->id);
    }

    public function find(string $text){
        if(stristr($this->text, $text))
            return true;
        else
            return false;
    }

    public function split($char, $times = null) {
        if(isset($this->text)){
            return explode($char, $this->text, $times);
        }else
            return false;
    }

    public function command(string $command = ""){
        if(strpos($this->text, "/".$command) === 0)
            return true;
        else
            return false;
    }

}
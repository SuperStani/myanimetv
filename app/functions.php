
<?php

class Bot{	
	public $token;
	public $update;

    
	public function __construct($token,$input){

		$this->token = 'bot'.$token;
		$this->input = $input;
		$this->update = json_decode($this->input);
		$this->endpoint = "https://api.telegram.org/bot" . $token . "/";
        $this->curl = curl_init();
        curl_setopt_array($this->curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_HTTPHEADER     => ["Connection: Keep-Alive", "Keep-Alive: 120"],
        ]);
        
		if ($this->update) {
            $this->chatID = $this->update->message->chat->id;
			$this->userID = $this->update->message->from->id;
            $this->is_bot = $this->update->message->from->is_bot;
            $this->nome = htmlspecialchars($this->update->message->from->first_name);
            $this->cognome = htmlspecialchars($this->update->message->from->last_name);
            $this->username = htmlspecialchars($this->update->message->from->username);
            $this->type = $this->update->message->chat->type;
            $this->photo = $this->update->message->photo;
            $this->video = $this->update->message->video;
            $this->msg = $this->update->message->text;
            $this->msgid = $this->update->message->message_id;
            $this->reply_to_message = $this->update->message->reply_to_message;
            if (isset($this->photo)) {
                $this->photo_name = $this->update->message->photo->file_name;
                $this->file_id = $this->update->message->photo{count($this->photo) - 1}->file_id;
            }
            if (isset($this->video)) {
                $this->video_name = $this->update->message->video->file_name;
                $this->file_id = $this->update->message->video->file_id;
            }

            if (isset($this->update->message->document)) {
                $this->document = $this->update->message->document;
            }
            
            if (isset($this->reply_to_message)) {
                $this->reply_msg = $this->update->message->reply_to_message->text;
                $this->reply_msgid = $this->update->message->reply_to_message->message_id;
                $this->reply_nome = $this->update->message->reply_to_message->from->first_name;
                $this->reply_cognome = $this->update->message->reply_to_message->from->last_name;
                $this->reply_username = $this->update->message->reply_to_message->from->username;
                $this->reply_userID = $this->update->message->reply_to_message->from->id;
                $this->reply_is_bot = $this->update->message->reply_to_message->from->is_bot;
                if (isset($this->update->message->reply_to_message->photo)) {
                    $this->reply_photo = $this->update->message->reply_to_message->photo;
                    $this->reply_photo_file_id = $this->update->message->reply_to_message->photo->{count($this->photo) - 2}->file_id;
                    $this->reply_photo_caption = $this->update->message->reply_to_message->caption;
                }
                if(isset($this->update->message->reply_to_message->forward_from_chat)){
                    $this->replyFromChatID = $this->update->message->reply_to_message->forward_from_chat->id;
                    $this->replyFromMsgid = $this->update->message->reply_to_message->forward_from_message_id;
                }
            }
                
            if (isset($this->update->callback_query)) {
                $this->msgid = $this->update->callback_query->message->message_id;
                $this->chatID = $this->update->callback_query->message->chat->id;
                $this->userID = $this->update->callback_query->from->id;
                $this->cbdata = $this->update->callback_query->data;
                $this->menu = $this->update->callback_query->message->reply_markup->inline_keyboard;
                $this->cbmsg = $this->update->callback_query->message->text;
                $this->nome = htmlspecialchars($this->update->callback_query->from->first_name);
                $this->cognome = htmlspecialchars($this->update->callback_query->from->last_name);
                $this->username = htmlspecialchars($this->update->callback_query->from->username);
                $this->is_bot = $this->update->callback_query->from->is_bot;
                $this->type = $this->update->callback_query->message->chat->type;
                $this->cbid = $this->update->callback_query->id;
                $this->reply_to_msgid = $this->update->callback_query->message->reply_to_message->message_id;
                if(isset($this->update->callback_query->inline_message_id)){
                    $this->imsgid = $this->update->callback_query->inline_message_id;
                }
            }
            //...Inline updates
			if(isset($this->update->inline_query)){            
                $this->inline = $this->update->inline_query->id;
                $this->imsg = $this->update->inline_query->query;
                $this->userID = $this->update->inline_query->from->id;
                $this->username = $this->update->inline_query->from->username;
                $this->name = $this->update->inline_query->from->first_name;
			}
        }
    
	}

	public function curl($method, $args = []){
		curl_setopt_array($this->curl, [
			CURLOPT_URL        => $this->endpoint . $method,
			CURLOPT_POSTFIELDS => empty($args) ? null : $args,
		]);
		$resultCurl = curl_exec($this->curl);
		if ($resultCurl === false) {
			$arr = [
				"ok"          => false,
				"error_code"  => curl_errno($this->curl),
				"description" => curl_error($this->curl),
				"curl_error"  => true
			];

			$resultCurl = json_encode($arr);
		}

		$resultJson = json_decode($resultCurl, true);
		if ($resultJson === null) {
			$arr = [
				"ok"          => false,
				"error_code"  => json_last_error(),
				"description" => json_last_error_msg(),
				"json_error"  => true
			];
			$resultJson = json_decode(json_encode($arr), true);
		}

		return $resultJson;
	}

	public function setting($setting = ["parse_mode" => "html","disable_web_page_preview" => false,"action" => false,"usa_database" => false,"channel_post" =>false,"funziona_modificati" =>false,"admin" => [],"develope_mode" => false]){
		$this->setting = $setting;
	}
	
    
    public function isadmin($user_id = null){
        if($user_id == null){
            $user_id = $this->userID;
        }
		$isadmin = in_array($user_id, $this->setting["admin"]);
        return $isadmin;
	}
	
	public function getNav($int){
		if($int == 0){
			$text = "━━━━━━━━━━━━━━━━━\n\t🔵⚪️⚪️⚪️⚪️⚪️⚪️⚪️⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 1){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️🔵⚪️⚪️⚪️⚪️⚪️⚪️⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 2){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️🔵⚪️⚪️⚪️⚪️⚪️⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 3){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️⚪️🔵⚪️⚪️⚪️⚪️⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 4){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️⚪️⚪️🔵⚪️⚪️⚪️⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 5){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️⚪️⚪️⚪️🔵⚪️⚪️⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 6){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️⚪️⚪️⚪️⚪️🔵⚪️⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 7){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️⚪️⚪️⚪️⚪️⚪️🔵⚪️⚪️\n━━━━━━━━━━━━━━━━━";
		}else if($int == 8){
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️⚪️⚪️⚪️⚪️⚪️⚪️🔵⚪️\n━━━━━━━━━━━━━━━━━";
		}else{
			$text = "━━━━━━━━━━━━━━━━━\n\t⚪️⚪️⚪️⚪️⚪️⚪️⚪️⚪️⚪️🔵\n━━━━━━━━━━━━━━━━━";
		}
		return $text;
    }
	
	public function sm(
        $chat_id,
		string $text,
		array $reply_markup = null,
        string $parse_mode = 'html',
        bool $disable_web_page_preview = false,
        bool $disable_notification = null,
        int $reply_to_message_id = null
    ) {
        $args = [
            'chat_id' => $chat_id,
            'text'    => $text
        ];

        if ($parse_mode !== null) {
            $args['parse_mode'] = $parse_mode;
        }

        if ($disable_web_page_preview !== null) {
            $args['disable_web_page_preview'] = $disable_web_page_preview;
        }

        if ($disable_notification !== null) {
            $args['disable_notification'] = $disable_notification;
        }

        if ($reply_to_message_id !== null) {
            $args['reply_to_message_id'] = $reply_to_message_id;
        }

        if ($reply_markup !== null) {
            $args['reply_markup'] = json_encode(["inline_keyboard" => $reply_markup]);
		}
		return $this->curl("sendMessage",$args);
    }
    
    public function reply($text, $reply_markup = null, $disable_web_page_preview = false, $reply_to_message_id = null){
       return $this->sm($this->chatID, $text, $reply_markup, 'html', $disable_web_page_preview, null, $reply_to_message_id);
    }

	public function si(
        $chat_id,
        $photo,
		string $caption = null,
		array $reply_markup = null,
        string $parse_mode = 'html',
        bool $disable_notification = null,
        int $reply_to_message_id = null
    ) {
        $args = [
            'chat_id' => $chat_id,
            'photo'   => $photo
        ];

        if ($caption !== null) {
            $args['caption'] = $caption;
        }

        if ($parse_mode !== null) {
            $args['parse_mode'] = $parse_mode;
        }

        if ($disable_notification !== null) {
            $args['disable_notification'] = $disable_notification;
        }

        if ($reply_to_message_id !== null) {
            $args['reply_to_message_id'] = $reply_to_message_id;
        }

        if ($reply_markup !== null) {
            $args['reply_markup'] = json_encode(["inline_keyboard" => $reply_markup]); 
        }

        return $this->curl('sendPhoto', $args);
    }

	public function editMedia($msgid, 
					   $file_id, 
					   $type = "video", 
					   $caption = " ", 
					   $menu = null)
	{
			$chat_id = $this->chatID;
			if($type == "photo"){
				$media = ["type" => "photo", "media" => $file_id, "caption" => $caption, "parse_mode" => "html"];
			}else if($type == "document"){
				$media = ["type" => "document", "media" => $file_id, "caption" => $caption, "parse_mode" => "html"];
			}else{
				$media = ["type" => "video", "media" => $file_id, "caption" => $caption, "parse_mode" => "html", "supports_streaming" => true];
			}
			$media = json_encode($media);
			$args = [
				"chat_id" => $chat_id,
				"message_id" => $msgid,
				"media"=> $media,
				];
				if($menu != null){
					$args["reply_markup"] = json_encode(["inline_keyboard" => $menu]);
				}
		return $this->curl("editMessageMedia",$args);
	}

    public function sendStick($chatID,$sticker){
        $args = array(
            "chat_id"=>$chatID,
            "sticker"=>$sticker
        );
        return $this->curl("sendSticker",$args);
	}


	public function sendVideo($chat_id = 0, 
					   		  $video = 0,
					  		  $caption = "", 
					   		  $menu = 0,
					   		  $reply_message_id = null
					   		 )
	{

		$args = [
			"chat_id" => $chat_id,
			"video" => $video,
			"caption" => $caption,
            "reply_to_message_id" => $reply_message_id,
			"parse_mode" => "html",
			"supports_streming" => true,
			"width" => 9
		];
		if($menu){
			$args["reply_markup"] = json_encode(["inline_keyboard" => $menu]); 
		}

		return $this->curl("sendVideo",$args);
	}

	public function sendAlbum($chat_id, $media){
		$args = [
			"chat_id" => $chat_id,
			"media" => json_encode($media),
			"caption" => "gg"
		];
		return $this->curl("sendMediaGroup", $args);
	}

    public function send_document($chat_id, $document, $caption = "", $menu = null) {
        $args = [
			"chat_id" => $chat_id,
			"document" => $document,
			"caption" => $caption,
			"parse_mode"=>"html",
		];
        if($menu){
            $args["reply_markup"] = json_encode(["inline_keyboard" => $menu]); 
        }
        return $this->curl("sendDocument", $args);
    }

    public function deleteMessage($chat_id = 0,$msgid = null){
        $args = [
            "chat_id" => $chat_id,
            "message_id" => $msgid
        ];
        $this->curl("deleteMessage",$args);
	}
	
	public function getFile($file_id = ""){
		$args = [
			"file_id" => $file_id,
		];
		return "https://api.telegram.org/file/$this->token/".$this->curl("getFile",$args)["result"]["file_path"];
	}

	public function leaveChat($chat_id){
		$args = ["chat_id" => $chat_id];
		return $this->curl("leaveChat",$args)["result"];
	}

	public function getChat($chat_id){
		$args = ["chat_id" => $chat_id];
		return $this->curl("getChat",$args)["result"];
	}

	public function getChatMembersCount($chat_id){
		$args = ["chat_id"=>$chat_id];
		return json_decode($this->curl("getChatMembersCount",$args),true)["result"];
	}

	public function getChatMember($chat_id,$user_id){
		$args = [
			"chat_id"=>$chat_id,
			"user_id"=>$user_id,
		];
		return $this->curl("getChatMember",$args);
	}
	public function isfollower($chat_id, $user_id = null){
		if($user_id == null){
			$user_id = $this->userID;
		}
		$info = $this->getChatMember($chat_id, $user_id);
		if($info["ok"] == false || $info["result"]["status"] == "left"){
			return 0;
		}else{
			return 1;
		}
	}

	public function edit( 
					   $text = "", 
					   $reply_markup = null, 
					   $msgid = null,
					   $inline_msg = null, 
					   $disable_web_page_preview = null, 
					   $parse_mode = null)
	{
    	$chat_id = $this->chatID;
		if($parse_mode == null){
			$parse_mode = $this->setting["parse_mode"];
		}
		if($disable_web_page_preview == null){
			$disable_web_page_preview = $this->setting["disable_web_page_preview"];
		}

		if($msgid == null){
			$msgid = $this->msgid;
		}
		$args = [
			"chat_id" => $chat_id,
			"message_id" => $msgid,
			"text"=>$text,
			"parse_mode" => $parse_mode,
			"disable_web_page_preview" => $disable_web_page_preview,
		];
		if($reply_markup != null){
			$args["reply_markup"] = json_encode(["inline_keyboard" => $reply_markup]);
    	}
    
    	if($inline_msg != null){
        	$args["inline_message_id"] = $inline_msg;
    	}
		return $this->curl("editMessageText",$args);


	}

	public function alert($text, $alert = true){
    	$args = array(
			'callback_query_id' => $this->cbid,
			'text' => $text,
			'show_alert' => $alert,
			'disable_web_page_preview' => true,
    	);
    	return $this->curl("answerCallbackQuery", $args);
	}

	public function editCaption($msgid,$caption,$menu, $parse_mode = null){
			if($parse_mode == null){
				$parse_mode = $this->setting["parse_mode"];
			}
		$args = [
			"chat_id"=>$this->chatID,
			"message_id" => $msgid,
			"caption" => $caption,
			"parse_mode" => $parse_mode,
			"reply_markup" => json_encode(["inline_keyboard" => $menu])
		];
		return $this->curl("editMessageCaption",$args);
	}

	public function editButton($reply_markup, $inline_msg = null){
		$args = [
			"chat_id" => $this->chatID,
			"message_id" => $this->msgid,
			"reply_markup" => json_encode(["inline_keyboard" => $reply_markup]),
    	];
    	if($inline_msg != null){
        	$args["inline_message_id"] = $inline_msg;
    	}
		return $this->curl("editMessageReplyMarkup",$args);
	}
    public function inline($json,$inline){//...Activate inline results
        $json = json_encode($json);
        $args = [
			"inline_query_id" => $inline,
        	"results" => $json,
        	"cache_time" => 3,
		];
        return $this->curl("answerInlineQuery", $args);
	}

    public function setcbdata($data){
        $this->cbdata = $data;
    }
    
    public function data($cbdata){
        if(strpos($this->cbdata, $cbdata) === 0){ return 1; }
        else{ return 0; }
    }

	public function sendAnimeToChannel($anime_id, $channel_id, $add_caption = ""){
		$query_string = "	SELECT  
								anime_info.episodi, 
								anime_info.trama_url, 
								anime_info.uscita,
								anime_info.trailer,
								anime.poster, 
								anime.nome, 
								anime.stagione
							FROM anime_info 
							INNER JOIN anime 
							ON anime.id = anime_info.anime_id
							WHERE  anime.id = '$anime_id'";
		$q = $this->conn->query($query_string);
		$info = $q->fetch();
		$nome = $info["nome"];
		$uscita = $info["uscita"];
		$trama = $info["trama_url"];
		$episodi = $info["episodi"];
		$poster = $info["poster"];
		$stagione = $info["stagione"];
		$trailer = $info["trailer"]; 
		$poster = $info["poster"];
		$generi = $this->conn->query("SELECT 
									generi.nome
								FROM generi 
								INNER JOIN anime_genere 
								ON anime_genere.genere_id = generi.id 
								INNER JOIN anime 
								ON anime.id = anime_genere.anime_id 
								WHERE anime.id = '$anime_id'
								ORDER by generi.id ASC");
		$generi = "#".implode(' #', array_column($generi->fetchAll(), 'nome')); //Implode genres
		if($stagione > 0){
			$stagioni = ["Prima", "Seconda", "Terza", "Quarta", "Quinta", "Sesta", "Settima", "Ottava", "Nona", "Decima"];
		$stag = "➥ <i>".str_replace([1,2,3,4,5,6,7,8,9,10], $stagioni, $stagione)." stagione</>\n";
		}
		if($trailer != ''){
			$trailer = "\n📽 | <b>Trailer:</> <a href='$trailer'>Clicca qui</>";
		}
		if($episodi == 0){
			$episodi = "??";
		}
		$text = "<b>$nome</>\n━━━━━━━━━━━\n$stag\n🗓 | <b>Data:</> $uscita\n➕ | <b>Episodi:</> $episodi\n📖 | <b>Trama:</> <a href='$trama'>Clicca qui</>$trailer\n\n🌟 | <b>Generi:</>\n$generi".$add_caption;
		$like = $this->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 1 AND anime_id = '$anime_id'")->fetch()["tot"];
		$dislike = $this->conn->query("SELECT COUNT(anime_id) as tot FROM votes WHERE type = 0 AND anime_id = '$anime_id'")->fetch()["tot"];
		$menu[] = [["text" => "👍 $like", "callback_data" => "vote:like_$anime_id"],["text" => "👎 $dislike", "callback_data" => "vote:dislike_$anime_id"]];
		$menu[] = [["text" => "🎥 GUARDA ORA", "url" => "t.me/myanimetvbot?start=animeID_$anime_id"]];
		$this->sendStick($channel_id, "CAACAgQAAxkBAAECn-tePYyWjApO0tANmNtfakX-dma1EgACEwAD3VA4GM3t39MKCGhHGAQ");
		$this->si($channel_id, $poster, $text, $menu);
	}

/*
______         _           _                       
|  _  \       | |         | |                      
| | | |  __ _ | |_   __ _ | |__    __ _  ___   ___ 
| | | | / _` || __| / _` || '_ \  / _` |/ __| / _ \
| |/ / | (_| || |_ | (_| || |_) || (_| |\__ \|  __/
|___/   \__,_| \__| \__,_||_.__/  \__,_||___/ \___|
*/


	public function connessione($host = "", $user = "", $pass = "", $nome_db = ""){
    	try{
        	$this->conn = new PDO("mysql:host=$host;dbname=$nome_db",$user,$pass);
    	}catch(PDOException $e){
        	echo $e->getMessage();
    	}
	}

	public function u($chat_id = null){
    	if($chat_id == null){
        	$chat_id = $this->userID;
    	}
		$q = $this->conn->prepare("SELECT * FROM utenti WHERE chat_id = :chat_id");
		$q->bindParam(":chat_id",$chat_id);
		$q->execute();
		$this->page = $q->fetch()["page"];
		return 0;
    }

    public function cPage($page, $chat_id = null){
    	if($chat_id == null){
        	$chat_id = $this->userID;
    	}
		$q = $this->conn->prepare("SELECT page FROM utenti WHERE chat_id = :chat_id");
		$q->bindParam(":chat_id",$chat_id);
		$q->execute();
        $this->page = $q->fetch()["page"];
        if(strpos($this->page, $page) === 0){ return 1;}
        else{ return 0;}
	}

	public function checkPage($page){
		if(strpos($this->page, $page) === 0){ return 1;}
        else{ return 0;}
	}

    public function sPage($page){
        $this->page = $page;
    }
	//Funzioni utili per il database

	//Setta il page
	public function page($page, $userID = null){
		if($userID == null){
			$userID = $this->userID;
		}
		$q = $this->conn->prepare("UPDATE utenti SET page = :pagina WHERE chat_id = :chat_id");
		$q->bindParam(":pagina", $page);
		$q->bindParam(":chat_id", $userID);
		$q->execute();
	}

	//Questa funzione conta tutti gli iscritti al bot
	public function getFollower(){
		$q = $this->conn->query("SELECT count(chat_id) as tot FROM utenti");
		return $q->fetch()["tot"];
	}

}


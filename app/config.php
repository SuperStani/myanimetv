<?php


$bot->setting([
	'parse_mode' => 'html', 					//formattazione default dei messaggi [html o markdown]
	'disable_web_page_preview' => false,			
	'action' => true,							
	'usa_database' => true,					//abilitare mysql
	'channel_post' => false,					//riceve update dai canali
	'funziona_modificati' => false,				//riceve update dai messaggi modificati
	'admin' => [
		406343901, //SuperStani
        198253421, //Kami
        737539655, //Stani2
        154658214, //Ester
        156371150, //Fra9898
        808699539, //GiorgioAmbulante69
		856835224 //Yukumi
	],
	'banner' => [
		"benvenuto" => "http://myanimetv.org/stani/myanimetv/img/bannerWelcome.png",
		"profilo" => "http://myanimetv.org/stani/myanimetv/img/bannerProfilo.png",
		"correlati" => "http://simplebot.ml/bots/stani/myanimetv/img/00003.png",
		"richiedi" => "http://myanimetv.org/stani/myanimetv/img/bannerRichiesta.png",
		"calendario" => "http://myanimetv.org/stani/myanimetv/img/bannerCalendario.png",
		"ricerca" => "http://myanimetv.org/stani/myanimetv/img/banners.png",
		"classifiche" => "http://myanimetv.org/stani/myanimetv/img/bannerClassifiche.png",
        "listaAnime" => "http://simplebot.ml/bots/stani/myanimetv/img/010.png",
        "consigliati" => "http://simplebot.ml/bots/stani/myanimetv/img/00012.png",
        "completato" => "http://simplebot.ml/bots/stani/myanimetv/img/00009.png",
        "accettata" => "http://myanimetv.org/stani/myanimetv/img/bannerAccettata.png",
        "popolari" => "http://simplebot.ml/bots/stani/myanimetv/img/00015.png",
        "votati" => "http://simplebot.ml/bots/stani/myanimetv/img/00014.png",
        "errore1" => "http://myanimetv.org/stani/myanimetv/img/bannerErrorSearch2.png",
        "errore2" => "http://myanimetv.org/stani/myanimetv/img/bannerErrorRichieste.png",
        "errore3" => "http://myanimetv.org/stani/myanimetv/img/bannerRichiestaRifiutataa.png",
		"market" => "http://myanimetv.org/stani/myanimetv/img/bannerMarket.png",
        "anteprima" => "http://myanimetv.org/stani/myanimetv/img/bannerAnteprima.png",
		"simulcasts" => "http://myanimetv.org/stani/myanimetv/img/bannerSimulcasts.png"
	],						
	'develope_mode' => false, 					//abilitare solo durante lo sviluppo del bot
	'nome_tabella' => 'utenti',				   //nome della tabella principale del bot


	/* SETTING MYSQL*/
	'host' => 'localhost',
	'nome_utente' => 'admin',
	'password' => '@Naruto96',
	'database' => 'myanimetv', 							//nome database
]); 

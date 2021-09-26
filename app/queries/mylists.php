<?php

$query = [
    "mylists" => [
        //A-Z order
        "SELECT 
            anime.id,
            anime.nome,
            anime.stagione
         FROM anime
         INNER JOIN bookmarks
         ON anime.id = bookmarks.anime_id
         WHERE bookmarks.chat_id = :chat_id
         AND bookmarks.list_id = :list
         ORDER by anime.nome, anime.stagione
         LIMIT :index, :limite",

        //Z-A order
        "SELECT 
            anime.id,
            anime.nome,
            anime.stagione
         FROM anime
         INNER JOIN bookmarks
         ON anime.id = bookmarks.anime_id
         WHERE bookmarks.chat_id = :chat_id
         AND bookmarks.list_id = :list
         ORDER by anime.nome DESC , anime.stagione
         LIMIT :index, :limite",

        //Views order
        "SELECT 
            anime.id,
            anime.nome,
            anime.stagione,
            COUNT(anime_views.anime_id) AS tot_views
         FROM anime
         INNER JOIN bookmarks
         ON anime.id = bookmarks.anime_id
         LEFT JOIN anime_views
         ON anime.id = anime_views.anime_id
         WHERE bookmarks.chat_id = :chat_id
         AND bookmarks.list_id = :list
         GROUP by anime.id
         ORDER by tot_views DESC
         LIMIT :index, :limite",
        
        //Z-A order
        "SELECT 
            anime.id,
            anime.nome,
            anime.stagione,
            anime_info.episodi
         FROM anime
         INNER JOIN anime_info
         ON anime.id = anime_info.anime_id
         INNER JOIN bookmarks
         ON anime.id = bookmarks.anime_id
         WHERE bookmarks.chat_id = :chat_id
         AND bookmarks.list_id = :list
         ORDER by anime_info.episodi ASC
         LIMIT :index, :limite"
    ],
    "preferreds" => 
    [
        //A-Z order
        "SELECT 
            anime.id,
            anime.nome,
            anime.stagione
         FROM anime
         INNER JOIN preferreds
         ON anime.id = preferreds.anime_id
         WHERE preferreds.chat_id = :chat_id
         ORDER by anime.nome, anime.stagione
         LIMIT :index, :limite"
    ]
];
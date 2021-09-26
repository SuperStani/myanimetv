<?php

$query = [
    "name" => [
        "   SELECT 
                    anime.id, 
                    anime.nome, 
                    anime.stagione 
            FROM anime
            WHERE anime.nome 
            LIKE :search  
            OR anime.nomi_alternativi
            LIKE :search 
            ORDER by anime.nome, anime.stagione 
            LIMIT :searchIndex, :limite",
        "   SELECT 
                    anime.id, 
                    anime.nome, 
                    anime.stagione 
            FROM anime
            WHERE anime.nome 
            LIKE :search  
            OR anime.nomi_alternativi
            LIKE :search 
            ORDER by anime.nome DESC, anime.stagione
            LIMIT :searchIndex, :limite",    
        "   SELECT 
                    anime.id, 
                    anime.nome, 
                    anime.stagione ,
                    COUNT(anime_views.anime_id) as views
            FROM anime
            LEFT JOIN anime_views
            ON anime.id = anime_views.anime_id
            WHERE anime.nome 
            LIKE :search  
            OR anime.nomi_alternativi
            LIKE :search 
            GROUP by anime.id
            ORDER by views DESC
            LIMIT :searchIndex, :limite",
        "   SELECT 
                    anime.id, 
                    anime.nome, 
                    anime.stagione 
            FROM anime
            INNER JOIN anime_info 
            ON anime.id = anime_info.anime_id 
            WHERE anime.nome 
            LIKE :search  
            OR anime.nomi_alternativi
            LIKE :search 
            ORDER by anime_info.episodi ASC
            LIMIT :searchIndex, :limite",
    ],
    "episodi" => [
        "   SELECT 
                anime.id,
                anime.nome, 
                anime_info.episodi,
                anime.stagione
            FROM anime
            RIGHT JOIN anime_info 
            ON anime.id = anime_info.anime_id 
            WHERE anime_info.episodi >= :start_index
            AND anime_info.episodi <= :limit_index
            AND anime_info.categoria NOT IN (2, 5)
            ORDER by anime.nome, anime.stagione ASC LIMIT :searchIndex, :limite",
        "   SELECT 
                anime.id,
                anime.nome, 
                anime_info.episodi,
                anime.stagione
            FROM anime
            RIGHT JOIN anime_info
            ON anime.id = anime_info.anime_id 
            WHERE anime_info.episodi >= :start_index
            AND anime_info.episodi <= :limit_index
            AND anime_info.categoria NOT IN (2, 5)
            ORDER by anime.nome DESC LIMIT :searchIndex, :limite",
        "   SELECT 
                anime.id,
                anime.nome, 
                anime_info.episodi,
                anime.stagione,
                COUNT(anime_views.anime_id) as views
            FROM anime
            INNER JOIN anime_info 
            ON anime.id = anime_info.anime_id 
            LEFT JOIN anime_views
            ON anime.id = anime_views.anime_id
            WHERE anime_info.episodi >= :start_index
            AND anime_info.episodi <= :limit_index
            AND anime_info.categoria NOT IN (2, 5)
            GROUP by anime_info.anime_id 
            ORDER by views DESC 
            LIMIT :searchIndex, :limite",
        "   SELECT 
                anime.id,
                anime.nome, 
                anime_info.episodi,
                anime.stagione
            FROM anime
            RIGHT JOIN anime_info 
            ON anime.id = anime_info.anime_id 
            WHERE anime_info.episodi >= :start_index
            AND anime_info.episodi <= :limit_index
            AND anime_info.categoria NOT IN (2, 5)
            ORDER by anime_info.episodi ASC 
            LIMIT :searchIndex, :limite",
    ],
    "anno" => [
        "   SELECT 
                anime.id, 
                anime.nome, 
                anime.stagione 
            FROM anime 
            INNER JOIN anime_info
            ON anime.id = anime_info.anime_id 
            WHERE anime_info.uscita
            LIKE :search  
            ORDER by anime.nome, anime.stagione
            LIMIT :searchIndex, :limite",
        "   SELECT 
                anime.id, 
                anime.nome, 
                anime.stagione 
            FROM anime
            INNER JOIN anime_info
            ON anime.id = anime_info.anime_id 
            WHERE anime_info.uscita
            LIKE :search  
            ORDER by anime.nome DESC
            LIMIT :searchIndex, :limite", 
        "   SELECT 
                anime.id, 
                anime.nome, 
                anime.stagione,
                COUNT(anime_views.anime_id) as views
            FROM anime
            INNER JOIN anime_info 
            ON anime.id = anime_info.anime_id 
            LEFT JOIN anime_views
            ON anime.id = anime_views.anime_id
            WHERE anime_info.uscita
            LIKE :search  
            GROUP by anime.id
            ORDER by views DESC
            LIMIT :searchIndex, :limite",  
        "   SELECT 
                anime.id, 
                anime.nome, 
                anime.stagione,
                anime_info.episodi
            FROM anime
            INNER JOIN anime_info
            ON anime.id = anime_info.anime_id 
            WHERE anime_info.uscita
            LIKE :search  
            ORDER by anime_info.episodi ASC
            LIMIT :searchIndex, :limite",  
    ],
    "genre" => [
        "   SELECT 
                anime.id, 
                anime.nome, 
                anime.stagione 
            FROM anime 
            INNER JOIN anime_genere 
            ON anime_genere.anime_id = anime.id 
            WHERE anime_genere.genere_id IN(SELECT 
                                            genres_id 
                                            FROM generi_cercati 
                                            WHERE by_user_id = '$bot->userID'
                                        ) 
            AND anime.stagione < 2
            GROUP by anime_genere.anime_id 
            HAVING COUNT(anime_genere.genere_id) >= (SELECT 
                                                    COUNT(genres_id) 
                                                    FROM generi_cercati 
                                                    WHERE by_user_id = '$bot->userID' 
                                                    )
            ORDER by anime.nome 
            LIMIT $page_id, 10"
    ]
]

?>

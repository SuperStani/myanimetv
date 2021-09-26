
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myanimetv`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `anime`
--

CREATE TABLE `anime` (
  `id` int NOT NULL,
  `mal_id` int NOT NULL DEFAULT '0',
  `nome` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stagione` int DEFAULT NULL,
  `poster` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poster_msgid` int NOT NULL,
  `nomi_alternativi` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anime_cercati`
--

CREATE TABLE `anime_cercati` (
  `anime_id` int NOT NULL,
  `by_user_id` int NOT NULL,
  `search_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `anime_genere`
--

CREATE TABLE `anime_genere` (
  `anime_id` int NOT NULL,
  `genere_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `anime_groups`
--

CREATE TABLE `anime_groups` (
  `group_id` int NOT NULL,
  `anime_id` int NOT NULL,
  `stagione` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `anime_info`
--

CREATE TABLE `anime_info` (
  `anime_id` int NOT NULL,
  `categoria` tinyint DEFAULT '1',
  `episodi` int DEFAULT NULL,
  `trama` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `trama_url` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uscita` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aired_on` date DEFAULT NULL,
  `durata_ep` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audio` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anime_simulcast`
--

CREATE TABLE `anime_simulcast` (
  `anime_id` int NOT NULL,
  `poster` varchar(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `slide` int DEFAULT '1',
  `day_id` int DEFAULT NULL,
  `last_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int NOT NULL DEFAULT '0',
  `aw_url` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anime_studios`
--

CREATE TABLE `anime_studios` (
  `anime_id` int NOT NULL,
  `studio_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `anime_views`
--

CREATE TABLE `anime_views` (
  `anime_id` int NOT NULL,
  `episode_id` smallint NOT NULL DEFAULT '0',
  `chat_id` int NOT NULL,
  `view_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `antiflood`
--

CREATE TABLE `antiflood` (
  `chat_id` int NOT NULL,
  `update_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `bookmarks`
--

CREATE TABLE `bookmarks` (
  `anime_id` int NOT NULL,
  `chat_id` int NOT NULL,
  `list_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `categorie`
--

CREATE TABLE `categorie` (
  `id` int NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `commenti`
--

CREATE TABLE `commenti` (
  `nome` varchar(1000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `commento` varchar(2000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `anime_id` int NOT NULL,
  `episode_id` int NOT NULL,
  `wrote_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `episodes`
--

CREATE TABLE `episodes` (
  `id` int NOT NULL,
  `anime_id` int NOT NULL,
  `fileID` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tipo` tinyint NOT NULL DEFAULT '1',
  `title` varchar(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `upload_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `generi`
--

CREATE TABLE `generi` (
  `id` int NOT NULL,
  `nome` varchar(300) NOT NULL,
  `descrizione` varchar(400) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `generi_cercati`
--

CREATE TABLE `generi_cercati` (
  `by_user_id` int NOT NULL,
  `genres_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `giorni`
--

CREATE TABLE `giorni` (
  `id` int NOT NULL,
  `giorno` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `group_names`
--

CREATE TABLE `group_names` (
  `id` int NOT NULL,
  `name` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `last_view_episode`
--

CREATE TABLE `last_view_episode` (
  `anime_id` int NOT NULL,
  `chat_id` int NOT NULL,
  `episode_call` smallint NOT NULL,
  `view_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `preferreds`
--

CREATE TABLE `preferreds` (
  `anime_id` int NOT NULL,
  `chat_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `richieste`
--

CREATE TABLE `richieste` (
  `id` int NOT NULL,
  `by_user_id` int NOT NULL,
  `nome` text NOT NULL,
  `aproved` tinyint NOT NULL DEFAULT '0',
  `msgid` int DEFAULT NULL,
  `asked_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `search_keys`
--

CREATE TABLE `search_keys` (
  `id` int NOT NULL,
  `text` varchar(500) NOT NULL,
  `index_point` int NOT NULL DEFAULT '0',
  `chat_id` int NOT NULL,
  `searched_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `search_orders`
--

CREATE TABLE `search_orders` (
  `id` int NOT NULL,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `emoji` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `simulcast_notify`
--

CREATE TABLE `simulcast_notify` (
  `chat_id` int NOT NULL,
  `anime_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `sponsor`
--

CREATE TABLE `sponsor` (
  `id` int NOT NULL,
  `msg` varchar(2000) DEFAULT NULL,
  `img` varchar(2000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `studios`
--

CREATE TABLE `studios` (
  `id` int NOT NULL,
  `name` text CHARACTER SET latin1 COLLATE latin1_swedish_ci
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int NOT NULL,
  `chat_id` int NOT NULL,
  `username` varchar(300) DEFAULT NULL,
  `page` varchar(1000) DEFAULT NULL,
  `srcOrder` int NOT NULL DEFAULT '1',
  `richieste` int NOT NULL DEFAULT '1',
  `started_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Trigger `utenti`
--
DELIMITER $$
CREATE TRIGGER `delete anime null` AFTER UPDATE ON `utenti` FOR EACH ROW IF new.page = 'sendposter' THEN
DELETE FROM anime WHERE stagione IS NULL;
END IF
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_genres` AFTER UPDATE ON `utenti` FOR EACH ROW IF OLD.page <> NEW.page THEN
DELETE FROM generi_cercati WHERE by_user_id = OLD.chat_id;
END IF
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `votes`
--

CREATE TABLE `votes` (
  `anime_id` int NOT NULL,
  `chat_id` int NOT NULL,
  `type` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `warn_users`
--

CREATE TABLE `warn_users` (
  `id` int NOT NULL,
  `chat_id` int NOT NULL,
  `type` smallint NOT NULL DEFAULT '0',
  `warn_on_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `anime`
--
ALTER TABLE `anime`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `anime_cercati`
--
ALTER TABLE `anime_cercati`
  ADD KEY `anime_id_2` (`anime_id`),
  ADD KEY `fk_cercati_users` (`by_user_id`);

--
-- Indici per le tabelle `anime_genere`
--
ALTER TABLE `anime_genere`
  ADD KEY `anime_id` (`anime_id`),
  ADD KEY `fk_generi_anime` (`genere_id`);

--
-- Indici per le tabelle `anime_groups`
--
ALTER TABLE `anime_groups`
  ADD KEY `anime_id` (`anime_id`),
  ADD KEY `fk_groups` (`group_id`);

--
-- Indici per le tabelle `anime_info`
--
ALTER TABLE `anime_info`
  ADD PRIMARY KEY (`anime_id`);

--
-- Indici per le tabelle `anime_simulcast`
--
ALTER TABLE `anime_simulcast`
  ADD PRIMARY KEY (`anime_id`);

--
-- Indici per le tabelle `anime_studios`
--
ALTER TABLE `anime_studios`
  ADD KEY `anime_id` (`anime_id`),
  ADD KEY `studio_id` (`studio_id`);

--
-- Indici per le tabelle `anime_views`
--
ALTER TABLE `anime_views`
  ADD KEY `anime_id` (`anime_id`);

--
-- Indici per le tabelle `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD KEY `fk_bookmarks_anime` (`anime_id`);

--
-- Indici per le tabelle `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indici per le tabelle `commenti`
--
ALTER TABLE `commenti`
  ADD PRIMARY KEY (`wrote_on`);

--
-- Indici per le tabelle `episodes`
--
ALTER TABLE `episodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anime_id` (`anime_id`);

--
-- Indici per le tabelle `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indici per le tabelle `generi`
--
ALTER TABLE `generi`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `generi_cercati`
--
ALTER TABLE `generi_cercati`
  ADD KEY `genres_id` (`genres_id`);

--
-- Indici per le tabelle `giorni`
--
ALTER TABLE `giorni`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `group_names`
--
ALTER TABLE `group_names`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `last_view_episode`
--
ALTER TABLE `last_view_episode`
  ADD PRIMARY KEY (`view_on`);

--
-- Indici per le tabelle `preferreds`
--
ALTER TABLE `preferreds`
  ADD KEY `fk_anime_preferreds` (`anime_id`),
  ADD KEY `fk_users_preffereds` (`chat_id`);

--
-- Indici per le tabelle `richieste`
--
ALTER TABLE `richieste`
  ADD PRIMARY KEY (`id`),
  ADD KEY `by_user_id` (`by_user_id`);

--
-- Indici per le tabelle `search_keys`
--
ALTER TABLE `search_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_users` (`chat_id`);

--
-- Indici per le tabelle `search_orders`
--
ALTER TABLE `search_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `simulcast_notify`
--
ALTER TABLE `simulcast_notify`
  ADD KEY `anime_id` (`anime_id`);

--
-- Indici per le tabelle `sponsor`
--
ALTER TABLE `sponsor`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `studios`
--
ALTER TABLE `studios`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`);

--
-- Indici per le tabelle `votes`
--
ALTER TABLE `votes`
  ADD KEY `fk_anime_votes` (`anime_id`);

--
-- Indici per le tabelle `warn_users`
--
ALTER TABLE `warn_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `anime`
--
ALTER TABLE `anime`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `episodes`
--
ALTER TABLE `episodes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `generi`
--
ALTER TABLE `generi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `giorni`
--
ALTER TABLE `giorni`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `group_names`
--
ALTER TABLE `group_names`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `richieste`
--
ALTER TABLE `richieste`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `search_keys`
--
ALTER TABLE `search_keys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `search_orders`
--
ALTER TABLE `search_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `sponsor`
--
ALTER TABLE `sponsor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `studios`
--
ALTER TABLE `studios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `warn_users`
--
ALTER TABLE `warn_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `anime_cercati`
--
ALTER TABLE `anime_cercati`
  ADD CONSTRAINT `fk_anime_cercati` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cercati_users` FOREIGN KEY (`by_user_id`) REFERENCES `utenti` (`chat_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Limiti per la tabella `anime_genere`
--
ALTER TABLE `anime_genere`
  ADD CONSTRAINT `fk_anime` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_generi_anime` FOREIGN KEY (`genere_id`) REFERENCES `generi` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Limiti per la tabella `anime_groups`
--
ALTER TABLE `anime_groups`
  ADD CONSTRAINT `fk_anime_groups` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_groups` FOREIGN KEY (`group_id`) REFERENCES `group_names` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `anime_info`
--
ALTER TABLE `anime_info`
  ADD CONSTRAINT `fk_anime_info` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `anime_studios`
--
ALTER TABLE `anime_studios`
  ADD CONSTRAINT `fk_anime_studio` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_studio` FOREIGN KEY (`studio_id`) REFERENCES `studios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `anime_views`
--
ALTER TABLE `anime_views`
  ADD CONSTRAINT `fk_anime_views` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Limiti per la tabella `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `fk_bookmarks_anime` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `fk_anime_episodes` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `generi_cercati`
--
ALTER TABLE `generi_cercati`
  ADD CONSTRAINT `fk_generi` FOREIGN KEY (`genres_id`) REFERENCES `generi` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Limiti per la tabella `preferreds`
--
ALTER TABLE `preferreds`
  ADD CONSTRAINT `fk_anime_preferreds` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_preffereds` FOREIGN KEY (`chat_id`) REFERENCES `utenti` (`chat_id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `search_keys`
--
ALTER TABLE `search_keys`
  ADD CONSTRAINT `fk_users` FOREIGN KEY (`chat_id`) REFERENCES `utenti` (`chat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `fk_anime_votes` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

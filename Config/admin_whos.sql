SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `attribute_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `about` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `a_games` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `parsed` int(11) DEFAULT '0',
  `game_status` int(11) DEFAULT NULL,
  `game_status_name` varchar(128) DEFAULT NULL,
  `json_head` mediumtext NOT NULL,
  `json_events` mediumtext,
  `preview` mediumtext,
  `review_html` mediumtext,
  `t1_tour_id` int(11) DEFAULT NULL,
  `t2_tour_id` int(11) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `date_upd` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `a_languages` (
  `id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_name` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `a_params` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `a_tasks` (
  `id` int(11) NOT NULL,
  `parser` enum('calendar','anonse','preview','review','team','player','referee','coach') COLLATE utf8_unicode_ci NOT NULL,
  `subj_id` int(11) DEFAULT NULL,
  `subj_str` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priority` tinyint(4) NOT NULL DEFAULT '0',
  `reparse` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - існуючий запис не змінюється, 1 - існуючий запис апдейтиться',
  `old` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) DEFAULT '0' COMMENT '0 - вільне, 1 - взято в роботу ZP, 2 - оброблено ZP, 3 - оброблено Parser, 9 - Успішно виконано | відємні значення - помилки, -1 - неконкретизоване позначення помилки',
  `vars` text COLLATE utf8_unicode_ci,
  `date_start` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `coaches` (
  `id` int(11) NOT NULL,
  `main_id` int(11) DEFAULT NULL,
  `nationality` int(11) DEFAULT NULL COMMENT 'region_id',
  `img_src` varchar(256) DEFAULT NULL,
  `born_date` datetime DEFAULT NULL,
  `born_region` int(11) DEFAULT NULL,
  `born_place` varchar(128) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `foot` tinyint(4) DEFAULT NULL COMMENT '1 - права, 2 - ліва, 3 - обидві',
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `coaches_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `alt_name` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `ws_id` int(11) DEFAULT NULL,
  `game_id` int(11) NOT NULL,
  `event_num` int(11) DEFAULT NULL,
  `minute` tinyint(3) UNSIGNED DEFAULT NULL,
  `second` tinyint(4) DEFAULT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `ex_minute` int(11) DEFAULT NULL,
  `period` tinyint(4) DEFAULT NULL,
  `event_type_id` smallint(6) NOT NULL,
  `outcome` tinyint(4) DEFAULT NULL,
  `extra_attributes` text,
  `extra_attributes_ser` text,
  `attributes` text,
  `attributes_ser` text,
  `is_touch` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `event_types` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `url` varchar(256) DEFAULT NULL,
  `main_id` int(11) NOT NULL,
  `last_processed` date NOT NULL,
  `region_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `stage_id` int(11) DEFAULT NULL,
  `season_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `week` smallint(6) DEFAULT NULL,
  `time_stamp` datetime DEFAULT NULL,
  `start` datetime NOT NULL,
  `start_set` tinyint(4) DEFAULT '0',
  `team_1_id` int(11) NOT NULL,
  `team_2_id` int(11) NOT NULL,
  `team_1_url` varchar(256) DEFAULT NULL,
  `team_2_url` varchar(256) DEFAULT NULL,
  `anonse` varchar(12) DEFAULT '0' COMMENT '0 - матч ще не відбувся, 1 - матч закінчився в основний час, 2 - в додатковий час, 3 - по пенальті, 4 - матч відкладено',
  `status` tinyint(4) DEFAULT '0' COMMENT 'статус з whoscored',
  `status_name` varchar(64) DEFAULT NULL,
  `content_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 - тільки календарні дані, 2 - результати матчу без додаткової інфи, 3 - мінімальна інфа по матчу, 4 - повні дані',
  `team_1_goals` tinyint(4) DEFAULT NULL,
  `team_2_goals` tinyint(4) DEFAULT NULL,
  `team_1_goals_p1` tinyint(4) DEFAULT NULL,
  `team_2_goals_p1` tinyint(4) DEFAULT NULL,
  `team_1_goals_p2` tinyint(4) DEFAULT NULL,
  `team_2_goals_p2` tinyint(4) DEFAULT NULL,
  `team_1_goals_p3` tinyint(4) DEFAULT NULL,
  `team_2_goals_p3` tinyint(4) DEFAULT NULL,
  `team_1_goals_p4` tinyint(4) DEFAULT NULL,
  `team_2_goals_p4` tinyint(4) DEFAULT NULL,
  `team_1_goals_p` tinyint(4) DEFAULT NULL,
  `team_2_goals_p` tinyint(4) DEFAULT NULL,
  `team_1_red_cards` tinyint(4) DEFAULT NULL,
  `team_2_red_cards` tinyint(4) DEFAULT NULL,
  `team_1_red_cards_p1` tinyint(4) DEFAULT NULL,
  `team_2_red_cards_p1` tinyint(4) DEFAULT NULL,
  `team_1_red_cards_p2` tinyint(4) DEFAULT NULL,
  `team_2_red_cards_p2` tinyint(4) DEFAULT NULL,
  `team_1_red_cards_p3` tinyint(4) DEFAULT NULL,
  `team_2_red_cards_p3` tinyint(4) DEFAULT NULL,
  `team_1_red_cards_p4` tinyint(4) DEFAULT NULL,
  `team_2_red_cards_p4` tinyint(4) DEFAULT NULL,
  `team_1_red_cards_p` tinyint(4) DEFAULT NULL,
  `team_2_red_cards_p` tinyint(4) DEFAULT NULL,
  `team_1_yellow_cards` tinyint(4) DEFAULT NULL,
  `team_2_yellow_cards` tinyint(4) DEFAULT NULL,
  `team_1_yellow_cards_p1` tinyint(4) DEFAULT NULL,
  `team_2_yellow_cards_p1` tinyint(4) DEFAULT NULL,
  `team_1_yellow_cards_p2` tinyint(4) DEFAULT NULL,
  `team_2_yellow_cards_p2` tinyint(4) DEFAULT NULL,
  `team_1_yellow_cards_p3` tinyint(4) DEFAULT NULL,
  `team_2_yellow_cards_p3` tinyint(4) DEFAULT NULL,
  `team_1_yellow_cards_p4` tinyint(4) DEFAULT NULL,
  `team_2_yellow_cards_p4` tinyint(4) DEFAULT NULL,
  `team_1_yellow_cards_p` tinyint(4) DEFAULT NULL,
  `team_2_yellow_cards_p` tinyint(4) DEFAULT NULL,
  `team_1_substitutions` tinyint(4) DEFAULT NULL,
  `team_2_substitutions` tinyint(4) DEFAULT NULL,
  `team_1_substitutions_p1` tinyint(4) DEFAULT NULL,
  `team_2_substitutions_p1` tinyint(4) DEFAULT NULL,
  `team_1_substitutions_p2` tinyint(4) DEFAULT NULL,
  `team_2_substitutions_p2` tinyint(4) DEFAULT NULL,
  `team_1_substitutions_p3` tinyint(4) DEFAULT NULL,
  `team_2_substitutions_p3` tinyint(4) DEFAULT NULL,
  `team_1_substitutions_p4` tinyint(4) DEFAULT NULL,
  `team_2_substitutions_p4` tinyint(4) DEFAULT NULL,
  `team_1_substitutions_p` tinyint(4) DEFAULT NULL,
  `team_2_substitutions_p` tinyint(4) DEFAULT NULL,
  `team_1_corners` tinyint(4) DEFAULT NULL,
  `team_2_corners` tinyint(4) DEFAULT NULL,
  `team_1_corners_p1` tinyint(4) DEFAULT NULL,
  `team_2_corners_p1` tinyint(4) DEFAULT NULL,
  `team_1_corners_p2` tinyint(4) DEFAULT NULL,
  `team_2_corners_p2` tinyint(4) DEFAULT NULL,
  `team_1_corners_p3` tinyint(4) DEFAULT NULL,
  `team_2_corners_p3` tinyint(4) DEFAULT NULL,
  `team_1_corners_p4` tinyint(4) DEFAULT NULL,
  `team_2_corners_p4` tinyint(4) DEFAULT NULL,
  `team_1_schema` varchar(50) DEFAULT NULL,
  `team_2_schema` varchar(50) DEFAULT NULL,
  `first_goal_time` tinyint(3) UNSIGNED DEFAULT NULL,
  `first_goal_team_id` int(11) DEFAULT NULL,
  `first_goal_player_id` int(11) DEFAULT NULL,
  `first_yellow_card_time` tinyint(3) UNSIGNED DEFAULT NULL,
  `first_yellow_card_team_id` int(11) DEFAULT NULL,
  `first_yellow_card_player_id` int(11) DEFAULT NULL,
  `first_red_card_time` tinyint(3) UNSIGNED DEFAULT NULL,
  `first_red_card_team_id` int(11) DEFAULT NULL,
  `first_red_card_player_id` int(11) DEFAULT NULL,
  `first_corner_time` tinyint(3) UNSIGNED DEFAULT NULL,
  `first_corner_team_id` int(11) DEFAULT NULL,
  `first_corner_player_id` int(11) DEFAULT NULL,
  `first_substitution_time` tinyint(3) UNSIGNED DEFAULT NULL,
  `first_substitution_team_id` int(11) DEFAULT NULL,
  `first_substitution_player_id_on` int(11) DEFAULT NULL,
  `first_substitution_player_id_off` int(11) DEFAULT NULL,
  `weather_id` tinyint(4) DEFAULT NULL,
  `weather_name` varchar(50) DEFAULT NULL,
  `stadium_visitors` int(11) DEFAULT NULL,
  `stadium_id` int(11) DEFAULT NULL,
  `referee_id` int(11) DEFAULT NULL,
  `ref_assist_1_id` int(11) DEFAULT NULL,
  `ref_assist_2_id` int(11) DEFAULT NULL,
  `ref_fourth_id` int(11) DEFAULT NULL,
  `team_1_coach_id` int(11) DEFAULT NULL,
  `team_2_coach_id` int(11) DEFAULT NULL,
  `expanded_minutes` text,
  `team_1_stats` text,
  `team_2_stats` text,
  `audit` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_cards` (
  `id` int(11) NOT NULL,
  `ws_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `time` tinyint(3) UNSIGNED DEFAULT NULL,
  `period` tinyint(4) DEFAULT NULL,
  `extra_attributes` text,
  `attributes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_corners` (
  `id` int(11) NOT NULL,
  `ws_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `time` tinyint(3) UNSIGNED DEFAULT NULL,
  `period` tinyint(4) DEFAULT NULL,
  `extra_attributes` text,
  `attributes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_goals` (
  `id` int(11) NOT NULL,
  `ws_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `time` tinyint(3) UNSIGNED DEFAULT NULL,
  `period` tinyint(4) DEFAULT NULL,
  `extra_attributes` text,
  `attributes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_substitution` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id_off` int(11) DEFAULT NULL,
  `player_id_on` int(11) DEFAULT NULL,
  `time` tinyint(3) UNSIGNED DEFAULT NULL,
  `period` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `log_stats` (
  `id` int(11) NOT NULL,
  `parser` varchar(255) NOT NULL,
  `mode` varchar(32) NOT NULL,
  `games` varchar(50) DEFAULT NULL,
  `count_total` int(11) NOT NULL,
  `subj_id` int(11) DEFAULT NULL,
  `subj_str` varchar(1024) DEFAULT NULL,
  `next_day` int(11) DEFAULT NULL,
  `next_week` int(11) DEFAULT NULL,
  `has_players` int(11) DEFAULT NULL,
  `has_coaches` int(11) DEFAULT NULL,
  `has_referee` int(11) DEFAULT NULL,
  `has_goals` int(11) DEFAULT NULL,
  `has_substitutions` int(11) DEFAULT NULL,
  `has_corners` int(11) DEFAULT NULL,
  `has_cards` int(11) DEFAULT NULL,
  `has_json` int(11) DEFAULT NULL,
  `error` int(11) DEFAULT NULL,
  `target_shots` int(11) DEFAULT NULL,
  `penalty` int(11) DEFAULT NULL,
  `offcides` int(11) DEFAULT NULL,
  `fouls` int(11) DEFAULT NULL,
  `posts_crosbars` int(11) DEFAULT NULL,
  `goal_kicks` int(11) DEFAULT NULL,
  `throwins` int(11) DEFAULT NULL,
  `ball_possesion` int(11) DEFAULT NULL,
  `date_parsers` date DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `main_id` int(11) DEFAULT '0',
  `nationality` int(11) DEFAULT NULL COMMENT 'region_id',
  `img_src` varchar(256) DEFAULT NULL,
  `born_date` datetime DEFAULT NULL,
  `born_region` int(11) DEFAULT NULL,
  `born_place` varchar(128) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `foot` tinyint(4) DEFAULT NULL COMMENT '1 - права, 2 - ліва, 3 - обидві',
  `audit` smallint(6) DEFAULT '0',
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `players_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `alt_name` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `players_to_games` (
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT NULL COMMENT '1 - в стартовому складі, 2 - запасний, 3 - травмований',
  `number` smallint(6) DEFAULT NULL,
  `injury` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `referee` (
  `id` int(11) NOT NULL,
  `main_id` int(11) DEFAULT NULL,
  `nationality` int(11) DEFAULT NULL,
  `born_date` datetime DEFAULT NULL,
  `born_region` int(11) DEFAULT NULL,
  `born_place` varchar(128) DEFAULT NULL,
  `img_src` varchar(256) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `referee_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `alt_name` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `main_id` int(11) NOT NULL,
  `club_domestic` tinyint(4) NOT NULL DEFAULT '0',
  `club_international` tinyint(4) NOT NULL DEFAULT '0',
  `national` tinyint(4) NOT NULL DEFAULT '0',
  `flag` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `regions_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `alt_name` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `seasons` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL DEFAULT '-1',
  `years` varchar(9) CHARACTER SET utf8mb4 NOT NULL,
  `main_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stadiums` (
  `id` int(11) NOT NULL,
  `main_id` int(11) DEFAULT NULL,
  `img_src` varchar(256) DEFAULT NULL,
  `city` varchar(256) DEFAULT NULL,
  `address` varchar(256) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `fax` varchar(32) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `max_visitors` int(11) DEFAULT NULL,
  `opened` varchar(16) DEFAULT NULL,
  `surface` varchar(32) DEFAULT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stadiums_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `alt_name` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stages` (
  `id` int(11) NOT NULL,
  `main_id` int(11) DEFAULT NULL,
  `season_id` int(11) NOT NULL DEFAULT '-1',
  `date_added` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stages_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `alt_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `is_national` tinyint(4) DEFAULT NULL,
  `img_src` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `coach_id` int(11) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `address` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `phone` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stadium_id` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `fans` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `date_upd` datetime NOT NULL DEFAULT '2010-10-10 10:10:10',
  `main_id` int(11) NOT NULL,
  `audit` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `teams_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `alt_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `main_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT '1',
  `type` tinyint(4) DEFAULT NULL,
  `is_primary` tinyint(4) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `tournaments_description` (
  `content_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `alt_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `attribute_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `a_games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_id` (`task_id`);

ALTER TABLE `a_languages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `a_params`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `a_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`);

ALTER TABLE `coaches`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `coaches_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ws_id` (`ws_id`);

ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_cards`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_corners`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_goals`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_substitution`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `log_stats`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `players`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `players_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);

ALTER TABLE `players_to_games`
  ADD PRIMARY KEY (`game_id`,`player_id`);

ALTER TABLE `referee`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `referee_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);

ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `regions_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);

ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `stadiums`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `stadiums_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);

ALTER TABLE `stages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `stages_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);

ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `teams_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);

ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tournaments_description`
  ADD PRIMARY KEY (`content_id`,`lang_id`);


ALTER TABLE `attribute_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `a_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1231629;

ALTER TABLE `a_languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `a_params`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `a_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

ALTER TABLE `coaches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `coaches_description`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_corners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_substitution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `log_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `players_description`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `referee_description`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

ALTER TABLE `regions_description`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

ALTER TABLE `seasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6996;

ALTER TABLE `stadiums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `stadiums_description`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `stages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15437;

ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=906;

ALTER TABLE `teams_description`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=906;

ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

ALTER TABLE `tournaments_description`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

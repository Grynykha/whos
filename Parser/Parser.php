<?php
namespace PRS\Parser;

use PRS\Manager\Log;
use PRS\Model\DBA;
use PRS\Model\Event;
use PRS\Model\Player;
use PRS\Model\Game;
use PRS\Model;

/**
 * Class Parser
 * @package PRS\Parser
 */

abstract class Parser
{

    /*************************************
     * Випаршування гравців на основі json
     * -----------------------------------
     * @param $players
     * @param $game
     * @param $team_id
     * @return bool
     */
    public static function playersFromJson($players, $game, $team_id): bool
    {
        $k = 0;
        foreach ($players as $ws_player) {
            $player = new Player;
            $player->id = $ws_player->playerId;
            $player->name[1] = $ws_player->name;
            $player->position = $ws_player->position;
            $player->height = $ws_player->height;
            $player->weight = $ws_player->weight;
            @$player->status = $ws_player->isFirstEleven;
            $player->number = $ws_player->shirtNo;

            $player->save();
            $player->saveToGame($game->id, $team_id);
            if (strtotime($game->start) > time() and !Model\Team::isNational($team_id)) {
                $player->curr_number = $player->number;
                $player->curr_team_id = $team_id;
                $player->updCurrTeamAndNumber();
            }
            $k++;
        }

        if ($k > 0) return true;
        return false;
    }


    /*************************
     * Розпаршування json_head
     * -----------------------
     * @param Task
     * @return Log
     */
    public static function gameAnonse($task): log
    {
        $log = new Log;
        $log->error = 0;
        $DBA = DBA::Instance()->GetConnect();

        $stage = new Model\Stage;
        $season = new Model\Season;
        $tournament = new Model\Tournament;
        $region = new Model\Region;
        $team_1 = new Model\Team;
        $team_1_region = new Model\Region;

        $team_2 = new Model\Team;
        $team_2_region = new Model\Region;
        $game = new Model\Game;

        $stmt = $DBA->prepare("SELECT * FROM a_games WHERE task_id = :task_id");
        $stmt->bindValue(':task_id', $task->id);
        $stmt->execute();
        $a_game = $stmt->fetch();

        $jsons = explode('||||', $a_game->json_head);
        unset($a_game);

        $first = true;

        foreach ($jsons as $json) {

            list($lang_id, $json_head) = explode("::::", $json);

            $lang_id = (int)$lang_id;
            $json_head = json_decode($json_head);

            // -----------------------------------------------------------------
            // RTSS: Region-Tournament-Season-Stage
            if ($first) {

                $stage->id = $json_head->StageId;
                $stage->season_id = $json_head->SeasonId;

                $season->id = $json_head->SeasonId;
                $season->tournament_id = $json_head->TournamentId;
                $season->years = $json_head->SeasonName;

                $tournament->id = $json_head->TournamentId;
                $tournament->region_id = $json_head->RegionId;
                $tournament->is_primary = (int)($json_head->TournamentIsPopular);

                $region->id = $json_head->RegionId;
                $region->national = (int)($json_head->RegionIsInternational);
            }

            if (isset($json_head->TournamentShortName))
                $tournament->alt_name[$lang_id] = $json_head->TournamentShortName;
            if (isset($json_head->StageName))
                $stage->name[$lang_id] = $json_head->StageName;
            if (isset($json_head->RegionName))
                $region->name[$lang_id] = $json_head->RegionName;
            if (isset($json_head->RegionCode))
                $region->alt_name[$lang_id] = $json_head->RegionCode;
            if (isset($json_head->TournamentName))
                $tournament->name[$lang_id] = $json_head->TournamentName;

            // -----------------------------------------------------------------
            // Команди
            if ($first) {
                $team_1->id = $json_head->HomeTeamId;
                $team_1->region_id = $json_head->HomeTeamCountryId;
                $team_1->is_national = ($json_head->HomeTeamName == $json_head->HomeTeamCountryName) ? 1 : 0;

                $team_1_region->id = $json_head->HomeTeamCountryId;
                $team_1_region->national = ($team_1->is_national) ? 1 : 0;

                $team_2->id = $json_head->AwayTeamId;
                $team_2->region_id = $json_head->AwayTeamCountryId;
                $team_2->is_national = ($json_head->AwayTeamName == $json_head->AwayTeamCountryName) ? 1 : 0;

                $team_2_region->id = $json_head->AwayTeamCountryId;
                $team_2_region->national = ($team_1->is_national) ? 1 : 0;
            }
            if (isset($json_head->AwayTeamName)){
                $team_2->name[$lang_id] = $json_head->AwayTeamName;
            }
            if (isset($json_head->HomeTeamName)){
                $team_1->name[$lang_id] = $json_head->HomeTeamName;
            }
            if (isset($json_head->HomeTeamCountryName)){
                $team_1_region->name[$lang_id] = $json_head->AwayTeamCountryName;
            }
            if (isset($json_head->HomeTeamCountryCode)){
                $team_1_region->alt_name[$lang_id] = $json_head->HomeTeamCountryCode;
            }
            if (isset($json_head->AwayTeamCountryName)){
                $team_2_region->name[$lang_id] = $json_head->AwayTeamCountryName;
            }
            if (isset($json_head->HomeTeamCountryCode)){
                $team_2_region->alt_name[$lang_id] = $json_head->HomeTeamCountryCode;
            }

            // ---------------------------------------------------------------------
            // Модель матчу
            if ($first) {
                //$game->upd = false;
                $game->id = $json_head->Id;
                $game->url = 'https://www.whoscored.com/Matches/' . $game->id;
                $game->region_id = $json_head->RegionId;
                $game->tournament_id = $json_head->TournamentId;
                $game->season_id = $json_head->SeasonId;
                $game->stage_id = $json_head->StageId;
                $game->group_id = null;
                $game->week = null;
                $game->start = date('Y-m-d H:i:s', (int)(substr($json_head->StartTime, 6, -2) / 1000));
                $game->start_set = 1;

                // у випадку, якщо матч перепаршується, очистить таблиці від звязаних з матчем данних
                // if ($task->reparse)
                $game->clear();

                // ---------------------------------------------------------------------
                // тренери
                $team_1_coach_name = $json_head->HomeTeamManagerName;
                if ($team_1_coach_name) {
                    $coach_t1 = Model\Coach::getByName($team_1_coach_name);
                    $game->team_1_coach_id = $coach_t1->id;
                }
                $team_2_coach_name = $json_head->AwayTeamManagerName;
                if ($team_2_coach_name) {
                    $coach_t2 = Model\Coach::getByName($team_2_coach_name);
                    $game->team_2_coach_id = $coach_t2->id;
                }

                // ---------------------------------------------------------------------
                // команди
                $game->team_1_id = $json_head->HomeTeamId;
                $game->team_2_id = $json_head->AwayTeamId;

                // ---------------------------------------------------------------------
                // Статуси
                $game->anonse = (isset($json_head->Elapsed)) ? $json_head->Elapsed : -1;
                $game->status = $json_head->Status->Value;
                $game->status_name = $json_head->Status->DisplayName;
                $game->content_type = 1;

                // ---------------------------------------------------------------------
                // стадіон
                $stadium = new Model\Stadium;
                $stadium->name[1] = $json_head->VenueName;
                $stadium->save();

                $game->stadium_visitors = $json_head->Attendance;
                $game->stadium_name = $json_head->VenueName;
                $game->stadium_id = $stadium->id;

                // ---------------------------------------------------------------------
                // Погода
                @$game->weather_id = $json_head->Weather->Value;
                @$game->weather_name = $json_head->Weather->DisplayName;
            }
            $first = false;
        }

        unset($json_head);

//        echo '<pre>';
//        print_r($game);
//        echo '<hr>';
//        print_r($team_1);
//        echo '<hr>';
//        print_r($team_2);
//        echo '</pre>';

        $stage->save();
        $season->save();
        $tournament->save();
        $region->save();
        $team_1_region->save();
        $team_2_region->save();
        $team_1->save();
        $team_2->save();

        if (strtotime($game->start) > time() and !$region->national) {
            $team_1->tournament_id = $tournament->id;
            $team_2->tournament_id = $tournament->id;
            $team_1->updTournament();
            $team_2->updTournament();
        }

        $game->save();

        $log->gameStart = $game->start;
        $log->parserMode = "anonse";
        $log->subj_id = $game->id;

        if (strtotime($game->start) < time() + 60 * 60 * 24 && strtotime($game->start) > time())
            $log->nextDay = 1;
        if (strtotime($game->start) < time() + 60 * 60 * 24 * 7 && strtotime($game->start) > time())
            $log->nextWeek = 1;
        if (isset($game->ref_main_id))
            $log->has_referee = 1;
        if (isset($game->team_1_coach_id) and isset($game->team_2_coach_id))
            $log->has_coaches = 1;

        $stmt = $DBA->prepare("UPDATE a_games SET parsed = 1 WHERE task_id = :task_id");
        $stmt->bindValue(':task_id', $task->id);
        $stmt->execute();

        return $log;
    }


    /***********************
     * Розпаршування preview
     * ---------------------
     * @param Task
     * @return Log
     */
    public static function gamePreview($task)
    {
        $log = new Log;
        $log->error = 0;
        $DBA = DBA::Instance()->GetConnect();

        $game = Game::getById($task->subj_id);

        $stmt = $DBA->prepare("SELECT * FROM a_games WHERE task_id = :task_id");
        $stmt->bindValue(':task_id', $task->id);
        $stmt->execute();
        $a_game = $stmt->fetch();

        // TODO

        unset($a_game);
        //$game->save();

        $log->gameStart = $game->start;
        $log->parserMode = "preview";
        $log->subj_id = $game->id;

        if (strtotime($game->start) < time() + 60 * 60 * 24 && strtotime($game->start) > time())
            $log->nextDay = 1;
        if (strtotime($game->start) < time() + 60 * 60 * 24 * 7 && strtotime($game->start) > time())
            $log->nextWeek = 1;
        if (isset($game->ref_main_id))
            $log->has_referee = 1;
        if (isset($t1_has_player) and isset($t2_has_player))
            $log->has_players = 1;
        if (isset($game->team_1_coach_id) and isset($game->team_2_coach_id))
            $log->has_coaches = 1;

        $stmt = $DBA->prepare("UPDATE a_games SET parsed = 1 WHERE task_id = :task_id");
        $stmt->bindValue(':task_id', $task->id);
        $stmt->execute();

        return $log;
    }


    /*****************************
     * РОЗПАРШУВАННЯ ВЕЛИКОГО JSON
     * ---------------------------
     * @param Task
     * @return Log
     */
    public static function gameReview($task): Log
    {
        $log = new Log;
        $log->error = 0;
        $DBA = DBA::Instance()->GetConnect();
        $stmt = $DBA->prepare("SELECT * FROM a_games WHERE task_id = :task_id");
        $stmt->bindValue(':task_id', $task->id);
        $stmt->execute();
        $a_game = $stmt->fetch();

        $game = Game::getById($task->subj_id);

        if ($game === null){
            $log->parserMode = "review";
            $log->error = 1;
            return $log;
        }
        $game->clear();

        // ---------------------------------------------------------------------
        if ($a_game->json_events != '') {

            $log->has_json = 1;

            $json_events = json_decode($a_game->json_events);

            $game_goals = [];
            $game_r_cards = [];
            $game_y_cards = [];
            $game_subst_on = [];
            $game_corner = [];

            // ---------------------------------------------------------------------
            // Рефері
            $referee = new Model\Referee;
            $referee->id      = $json_events->referee->officialId;
            $referee->name[1] = $json_events->referee->name;
            $referee->save();

            $game->ref_main_id = $referee->id;

            // ---------------------------------------------------------------------
            // Рахунок
            @list($game->team_1_goals, $game->team_2_goals) = explode(':', str_replace('*', '', $json_events->score));

            $game->team_1_goals = (int)$game->team_1_goals;
            $game->team_2_goals = (int)$game->team_2_goals;

            // ---------------------------------------------------------------------
            // Статуси
            $game->anonse = (isset($json_events->elapsed)) ? $json_events->elapsed : -1;
            $game->status_name = '';
            $game->status = $json_events->statusCode;
            $game->content_type = 4;

            // ---------------------------------------------------------------------
            // Гравці
            $t1_has_player = self::playersFromJson($json_events->home->players, $game, $game->team_1_id);
            $t2_has_player = self::playersFromJson($json_events->away->players, $game, $game->team_2_id);
            //$response = true;

            // ---------------------------------------------------------------------
            // Стадіон
            if (isset($json_events->venueName)) {
                $stadium = new Model\Stadium;
                $stadium->name[1] = $json_events->venueName;
                $stadium->save();

                $game->stadium_visitors = $json_events->attendance;
                $game->stadium_name = $json_events->venueName;
                $game->stadium_id = $stadium->id;
            }
            // ---------------------------------------------------------------------
            // EVENTS

            foreach ($json_events->events as $event) {
                $event->true_minute = $event->minute + 1;
                if(Event::AddEventMain($event, $game->id)){
                    switch ($event->type->value) {
                        case 16:    // Goal
                            Event::AddEventGoal($event, $game->id);
                            $game_goals[] = $event;
                            break;
                        case 17:    // Card
                            Event::AddEventCard($event, $game->id);
                            if (in_array(1067, $event->satisfiedEventsTypes)) {
                                $game_r_cards[] = $event;
                                break;
                            }
                            $game_y_cards[] = $event;
                            break;
                        case 19:    // SubstitutionOn
                            $game_subst_on[] = $event;
                            Event::AddEventSubst($event, $game->id);
                            break;
                        case 6:     // Corner
                            if ($event->outcomeType->value == 1) {
                                Event::AddEventCorner($event, $game->id);
                                $game_corner[] = $event;
                            }
                            break;
                    }
                }
            }
            unset($json_events);

            $first = new \stdClass();
            $result = [];

            foreach ($game_goals as $event) {
                @$result['goal'][$event->teamId][$event->period->value]++;
                if (!isset($first->goal->time) or $event->true_minute < $first->goal->time) {
                    @$first->goal->time = $event->true_minute;
                    @$first->goal->team_id = $event->teamId;
                    @$first->goal->player_id = $event->playerId;
                }
            }
            foreach ($game_r_cards as $event) {
                @$result['r_card'][$event->teamId][$event->period->value]++;
                @$result['r_card'][$event->teamId]['sum']++;
                if (!isset($first->r_card->time) or $event->true_minute < $first->r_card->time) {
                    @$first->r_card->time = $event->true_minute;
                    @$first->r_card->team_id = $event->teamId;
                    @$first->r_card->player_id = $event->playerId;
                }
            }
            foreach ($game_y_cards as $event) {
                @$result['y_card'][$event->teamId][$event->period->value]++;
                @$result['y_card'][$event->teamId]['sum']++;
                if (!isset($first->y_card->time) or $event->true_minute < $first->y_card->time) {
                    @$first->y_card->time = $event->true_minute;
                    @$first->y_card->team_id = $event->teamId;
                    @$first->y_card->player_id = $event->playerId;
                }
            }
            foreach ($game_subst_on as $event) {
                @$result['subst'][$event->teamId][$event->period->value]++;
                @$result['subst'][$event->teamId]['sum']++;
                if (!isset($first->subst->time) or $event->true_minute < $first->subst->time) {
                    @$first->subst->time = $event->true_minute;
                    @$first->subst->team_id = $event->teamId;
                    @$first->subst->player_id_on = $event->playerId;
                    @$first->subst->player_id_off = $event->relatedPlayerId;
                }
            }
            foreach ($game_corner as $event) {
                @$result['corner'][$event->teamId][$event->period->value]++;
                @$result['corner'][$event->teamId]['sum']++;
                if (!isset($first->corner->time) or $event->true_minute < $first->corner->time) {
                    @$first->corner->time = $event->true_minute;
                    @$first->corner->team_id = $event->teamId;
                    @$first->corner->player_id = $event->playerId;
                }
            }

            @$game->team_1_goals_p1 = (int)$result['goal'][$game->team_1_id][1];
            @$game->team_2_goals_p1 = (int)$result['goal'][$game->team_2_id][1];
            @$game->team_1_goals_p2 = (int)$result['goal'][$game->team_1_id][2];
            @$game->team_2_goals_p2 = (int)$result['goal'][$game->team_2_id][2];
            @$game->team_1_goals_p3 = (int)$result['goal'][$game->team_1_id][3];
            @$game->team_2_goals_p3 = (int)$result['goal'][$game->team_2_id][3];
            @$game->team_1_goals_p4 = (int)$result['goal'][$game->team_1_id][4];
            @$game->team_2_goals_p4 = (int)$result['goal'][$game->team_2_id][4];
            @$game->team_1_goals_p = (int)$result['goal'][$game->team_1_id][5];
            @$game->team_2_goals_p = (int)$result['goal'][$game->team_2_id][5];
            @$game->team_1_red_cards = (int)$result['r_card'][$game->team_1_id]['sum'];
            @$game->team_2_red_cards = (int)$result['r_card'][$game->team_2_id]['sum'];
            @$game->team_1_red_cards_p1 = (int)$result['r_card'][$game->team_1_id][1];
            @$game->team_2_red_cards_p1 = (int)$result['r_card'][$game->team_2_id][1];
            @$game->team_1_red_cards_p2 = (int)$result['r_card'][$game->team_1_id][2];
            @$game->team_2_red_cards_p2 = (int)$result['r_card'][$game->team_2_id][2];
            @$game->team_1_red_cards_p3 = (int)$result['r_card'][$game->team_1_id][3];
            @$game->team_2_red_cards_p3 = (int)$result['r_card'][$game->team_2_id][3];
            @$game->team_1_red_cards_p4 = (int)$result['r_card'][$game->team_1_id][4];
            @$game->team_2_red_cards_p4 = (int)$result['r_card'][$game->team_2_id][4];
            @$game->team_1_red_cards_p = (int)$result['r_card'][$game->team_1_id][5];
            @$game->team_2_red_cards_p = (int)$result['r_card'][$game->team_2_id][5];
            @$game->team_1_yellow_cards = (int)$result['y_card'][$game->team_1_id]['sum'];
            @$game->team_2_yellow_cards = (int)$result['y_card'][$game->team_2_id]['sum'];
            @$game->team_1_yellow_cards_p1 = (int)$result['y_card'][$game->team_1_id][1];
            @$game->team_2_yellow_cards_p1 = (int)$result['y_card'][$game->team_2_id][1];
            @$game->team_1_yellow_cards_p2 = (int)$result['y_card'][$game->team_1_id][2];
            @$game->team_2_yellow_cards_p2 = (int)$result['y_card'][$game->team_2_id][2];
            @$game->team_1_yellow_cards_p3 = (int)$result['y_card'][$game->team_1_id][3];
            @$game->team_2_yellow_cards_p3 = (int)$result['y_card'][$game->team_2_id][3];
            @$game->team_1_yellow_cards_p4 = (int)$result['y_card'][$game->team_1_id][4];
            @$game->team_2_yellow_cards_p4 = (int)$result['y_card'][$game->team_2_id][4];
            @$game->team_1_yellow_cards_p = (int)$result['y_card'][$game->team_1_id][5];
            @$game->team_2_yellow_cards_p = (int)$result['y_card'][$game->team_2_id][5];
            @$game->team_1_substitutions = (int)$result['subst'][$game->team_1_id]['sum'];
            @$game->team_2_substitutions = (int)$result['subst'][$game->team_2_id]['sum'];
            @$game->team_1_substitutions_p1 = (int)$result['subst'][$game->team_1_id][1];
            @$game->team_2_substitutions_p1 = (int)$result['subst'][$game->team_2_id][1];
            @$game->team_1_substitutions_p2 = (int)$result['subst'][$game->team_1_id][2];
            @$game->team_2_substitutions_p2 = (int)$result['subst'][$game->team_2_id][2];
            @$game->team_1_substitutions_p3 = (int)$result['subst'][$game->team_1_id][3];
            @$game->team_2_substitutions_p3 = (int)$result['subst'][$game->team_2_id][3];
            @$game->team_1_substitutions_p4 = (int)$result['subst'][$game->team_1_id][4];
            @$game->team_2_substitutions_p4 = (int)$result['subst'][$game->team_2_id][4];
            @$game->team_1_substitutions_p = (int)$result['subst'][$game->team_1_id][5];
            @$game->team_2_substitutions_p = (int)$result['subst'][$game->team_2_id][5];
            @$game->team_1_corners = (int)$result['corner'][$game->team_1_id]['sum'];
            @$game->team_2_corners = (int)$result['corner'][$game->team_2_id]['sum'];
            @$game->team_1_corners_p1 = (int)$result['corner'][$game->team_1_id][1];
            @$game->team_2_corners_p1 = (int)$result['corner'][$game->team_2_id][1];
            @$game->team_1_corners_p2 = (int)$result['corner'][$game->team_1_id][2];
            @$game->team_2_corners_p2 = (int)$result['corner'][$game->team_2_id][2];
            @$game->team_1_corners_p3 = (int)$result['corner'][$game->team_1_id][3];
            @$game->team_2_corners_p3 = (int)$result['corner'][$game->team_2_id][3];
            @$game->team_1_corners_p4 = (int)$result['corner'][$game->team_1_id][4];
            @$game->team_2_corners_p4 = (int)$result['corner'][$game->team_2_id][4];

            unset($result);

            @$game->first_goal_time = $first->goal->time;
            @$game->first_goal_team_id = $first->goal->team_id;
            @$game->first_goal_player_id = $first->goal->player_id;

            @$game->first_yellow_card_time = $first->y_card->time;
            @$game->first_yellow_card_team_id = $first->y_card->team_id;
            @$game->first_yellow_card_player_id = $first->y_card->player_id;

            @$game->first_red_card_time = $first->r_card->time;
            @$game->first_red_card_team_id = $first->r_card->team_id;
            @$game->first_red_card_player_id = $first->r_card->player_id;

            @$game->first_corner_time = $first->corner->time;
            @$game->first_corner_team_id = $first->corner->team_id;
            @$game->first_corner_player_id = $first->corner->player_id;

            @$game->first_substitution_time = $first->subst->time;
            @$game->first_substitution_team_id = $first->subst->team_id;
            @$game->first_substitution_player_id_on = $first->subst->player_id_on;
            @$game->first_substitution_player_id_off = $first->subst->player_id_off;

            unset($first);
        }


        $game->save();

        $log->gameStart = $game->start;
        $log->parserMode = "review";
        $log->subj_id = $game->id;
        if (strtotime($game->start) < time() + 60 * 60 * 24 && strtotime($game->start) > time())
            $log->nextDay = 1;
        if (strtotime($game->start) < time() + 60 * 60 * 24 * 7 && strtotime($game->start) > time())
            $log->nextWeek = 1;
        if (isset($game->ref_main_id))
            $log->has_referee = 1;
        if (isset($t1_has_player) and isset($t2_has_player))
            $log->has_players = 1;
        if (isset($game->team_1_coach_id) and isset($game->team_2_coach_id))
            $log->has_coaches = 1;
        if (isset($game->team_1_goals) and isset($game->team_2_goals))
            $log->has_goals = 1;
        if (isset($game->team_1_substitutions) and isset($game->team_2_substitutions))
            $log->has_substitutions = 1;
        if (isset($game->team_1_corners) and isset($game->team_2_corners))
            $log->has_corners = 1;
        if (isset($game->team_1_red_cards) and isset($game->team_2_red_cards) and isset($game->team_1_yellow_cards) and isset($game->team_2_yellow_cards))
            $log->has_cards = 1;

        //////////////////////
        // ТЕСТИЛКА
        //////////////////////
        // ob_start();
        // echo "<pre>";
        // print_r($game);
        // echo "</pre>";
        // $log->out = ob_get_clean();
        //////////////////////

        unset($a_game);
        return $log;
    }
}

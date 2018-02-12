<?php
namespace PRS\Model;

//////////////
   // МАТЧ //
  //////////////////////////////////////////////////////////////////////////////

class Game
{
//  ----------------------------------------------------------------------------

    public $upd = false;
//  id
    public $id;
//  URL
    public $url;
//  Регіон
    public $region_id;
//  id турніру на сокервеї (c####)
    public $tournament_id;
//  id сузону на сокервеї (s####)
    public $season_id;
//  id етапу на сокервеї (r####)
    public $stage_id;
//  id групи на сокервеї (g####)
    public $group_id;
//  якийсь тиждень
    public $week;
//  час і дата початку матчу
    public $start;
//  Чи точно вказаний час (0/1)
    public $start_set;
//  тренери
    public $team_1_coach_id;
    public $team_2_coach_id;
//  команди
    public $team_1_id;
    public $team_2_id;
//    public $team_1_url;
//    public $team_2_url;
//  0 - матч ще не відбувся
//  1 - матч закінчився в основний час
//  2 - в додатковий час
//  3 - по пенальті
//  4 - матч відкладено
    public $anonse;
//  статус з хускореда
    public $status;
    public $status_name;
//  1 - тільки дані з анонсу
//  2 - результати матчу без додаткової інфи
//  3 - мінімальна інфа по матчу
//  4 - повні дані
    public $content_type;
//  стадіон
    public $stadium_visitors;
    public $stadium_id;
//  погода
    public $weather_id;
    public $weather_name;
//  рефері
    public $ref_main_id;
    public $ref_assist_1_id;
    public $ref_assist_2_id;
    public $ref_fourth_id;
//  голи
    public $team_1_goals;
    public $team_2_goals;
    public $team_1_goals_p1;
    public $team_2_goals_p1;
    public $team_1_goals_p2;
    public $team_2_goals_p2;
    public $team_1_goals_p3;
    public $team_2_goals_p3;
    public $team_1_goals_p4;
    public $team_2_goals_p4;
    public $team_1_goals_p;
    public $team_2_goals_p;
//  жовті картки 
    public $team_1_yellow_cards;
    public $team_2_yellow_cards;
    public $team_1_yellow_cards_p1;
    public $team_2_yellow_cards_p1;
    public $team_1_yellow_cards_p2;
    public $team_2_yellow_cards_p2;
    public $team_1_yellow_cards_p3;
    public $team_2_yellow_cards_p3;
    public $team_1_yellow_cards_p4;
    public $team_2_yellow_cards_p4;
    public $team_1_yellow_cards_p;
    public $team_2_yellow_cards_p;
//  червоні картки 
    public $team_1_red_cards;
    public $team_2_red_cards;
    public $team_1_red_cards_p1;
    public $team_2_red_cards_p1;
    public $team_1_red_cards_p2;
    public $team_2_red_cards_p2;
    public $team_1_red_cards_p3;
    public $team_2_red_cards_p3;
    public $team_1_red_cards_p4;
    public $team_2_red_cards_p4;
    public $team_1_red_cards_p;
    public $team_2_red_cards_p;
//  заміни
    public $team_1_substitutions;
    public $team_2_substitutions;
    public $team_1_substitutions_p1;
    public $team_2_substitutions_p1;
    public $team_1_substitutions_p2;
    public $team_2_substitutions_p2;
    public $team_1_substitutions_p3;
    public $team_2_substitutions_p3;
    public $team_1_substitutions_p4;
    public $team_2_substitutions_p4;
    public $team_1_substitutions_p;
    public $team_2_substitutions_p;
//  кутові
    public $team_1_corners;
    public $team_2_corners;
    public $team_1_corners_p1;
    public $team_2_corners_p1;
    public $team_1_corners_p2;
    public $team_2_corners_p2;
    public $team_1_corners_p3;
    public $team_2_corners_p3;
    public $team_1_corners_p4;
    public $team_2_corners_p4;
//  перший гол
    public $first_goal_time;
    public $first_goal_team_id;
    public $first_goal_player_id;
//  перша жовта картка
    public $first_yellow_card_time;
    public $first_yellow_card_team_id;
    public $first_yellow_card_player_id;
// перша червона картка
    public $first_red_card_time;
    public $first_red_card_team_id;
    public $first_red_card_player_id;
// перший кутовий
    public $first_corner_time;
    public $first_corner_team_id;
    public $first_corner_player_id;
// перша заміна
    public $first_substitution_time;
    public $first_substitution_team_id;
    public $first_substitution_player_id_on;
    public $first_substitution_player_id_off;

    public $stadium_name;

//  --------------------------------------------------------------------------
//  Конструктор
//  --------------------------------------------------------------------------
    public function __construct($id = null)
    {
//        $upd = false;
//        if ($id) {
//            // SELECT * FROM game WHERE id = :id
//        }
    }

//  ---------------------------------------------------------------------
//  Чи є в базі
//  ---------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM games WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $num = $stmt->fetchColumn();
        if ($num){
            return true;
        }
        return false;
    }

//  ---------------------------------------------------------------------
//  Селект
//  ---------------------------------------------------------------------
    public static function getById($id): ?Game
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->bindValue(':id', $id, \PDO::PARAM_STR);
        $stmt->execute();
        $games = $stmt->fetchAll(\PDO::FETCH_CLASS, 'PRS\Model\Game');

        if (isset($games[0])) {
            $game = $games[0];
            $game->upd = true;
            return $game;
        }
        return null;
    }

//  ---------------------------------------------------------------------
//  Збереження
//  ---------------------------------------------------------------------
    public function save()
    {   
        if ($this->inDB()) {
            return $this->upd();
        }
        return $this->ins();
    }

//  ---------------------------------------------------------------------
//  Видалення гравців до гри
//  ---------------------------------------------------------------------
    public function clear()
    {   
        if ($this->inDB()) {
            $this->clearPlayers();
            $this->clearGoals();
            $this->clearSubstitutions();
            $this->clearCards();
            $this->clearCorners();
        }
    }

//  ---------------------------------------------------------------------
//  Видалення гравців до гри
//  ---------------------------------------------------------------------
    public function clearPlayers()
    {   
        $DB = DB::Instance()->GetConnect();
        $sql = "DELETE FROM players_to_games WHERE game_id = " . $this->id;
        $DB->exec($sql);
    }

//  ---------------------------------------------------------------------
//  Видалення тренерів до гри
//  ---------------------------------------------------------------------
//    public function clearCoaches()
//    {
//        $DB = DB::Instance()->GetConnect();
//        $sql = "DELETE FROM coaches_to_games WHERE game_id = " . $this->id;
//        $DB->exec($sql);
//    }

//  ---------------------------------------------------------------------
//  Видалення голів до гри
//  ---------------------------------------------------------------------
    public function clearGoals()
    {   
        $DB = DB::Instance()->GetConnect();
        $sql = "DELETE FROM game_goals WHERE game_id = " . $this->id;
        $DB->exec($sql);
    }

//  ---------------------------------------------------------------------
//  Видалення голів до гри
//  ---------------------------------------------------------------------
    public function clearSubstitutions()
    {   
        $DB = DB::Instance()->GetConnect();
        $sql = "DELETE FROM game_substitution WHERE game_id = " . $this->id;
        $DB->exec($sql);
    }

//  ---------------------------------------------------------------------
//  Видалення голів до гри
//  ---------------------------------------------------------------------
    public function clearCards()
    {   
        $DB = DB::Instance()->GetConnect();
        $sql = "DELETE FROM game_cards WHERE game_id = " . $this->id;
        $DB->exec($sql);
    }

//  ---------------------------------------------------------------------
//  Видалення голів до гри
//  ---------------------------------------------------------------------
    public function clearCorners()
    {   
        $DB = DB::Instance()->GetConnect();
        $sql = "DELETE FROM game_corners WHERE game_id = " . $this->id;
        $DB->exec($sql);
    }

//  ---------------------------------------------------------------------
//  Добавлення матчу в базу
//  ---------------------------------------------------------------------
    protected function ins()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("INSERT INTO games (
                id, 
                region_id, 
                tournament_id, 
                season_id, 
                stage_id, 
                group_id, 
                week, 
                start, 
                start_set, 
                team_1_coach_id,
                team_2_coach_id,
                team_1_id, 
                team_2_id, 
                stadium_visitors, 
                stadium_id, 
                anonse, 
                status, 
                status_name, 
                content_type, 
                referee_id,
                ref_assist_1_id,
                ref_assist_2_id,
                ref_fourth_id,
                weather_id,
                weather_name,
                team_1_goals, 
                team_2_goals, 
                team_1_goals_p1, 
                team_2_goals_p1, 
                team_1_goals_p2, 
                team_2_goals_p2, 
                team_1_goals_p3, 
                team_2_goals_p3, 
                team_1_goals_p4, 
                team_2_goals_p4, 
                team_1_goals_p, 
                team_2_goals_p,
                team_1_yellow_cards, 
                team_2_yellow_cards, 
                team_1_yellow_cards_p1, 
                team_2_yellow_cards_p1, 
                team_1_yellow_cards_p2, 
                team_2_yellow_cards_p2, 
                team_1_yellow_cards_p3, 
                team_2_yellow_cards_p3, 
                team_1_yellow_cards_p4, 
                team_2_yellow_cards_p4, 
                team_1_yellow_cards_p, 
                team_2_yellow_cards_p,
                team_1_red_cards, 
                team_2_red_cards, 
                team_1_red_cards_p1, 
                team_2_red_cards_p1, 
                team_1_red_cards_p2, 
                team_2_red_cards_p2, 
                team_1_red_cards_p3, 
                team_2_red_cards_p3, 
                team_1_red_cards_p4, 
                team_2_red_cards_p4, 
                team_1_red_cards_p, 
                team_2_red_cards_p,
                team_1_substitutions, 
                team_2_substitutions, 
                team_1_substitutions_p1, 
                team_2_substitutions_p1, 
                team_1_substitutions_p2, 
                team_2_substitutions_p2, 
                team_1_substitutions_p3, 
                team_2_substitutions_p3, 
                team_1_substitutions_p4, 
                team_2_substitutions_p4, 
                team_1_substitutions_p, 
                team_2_substitutions_p,
                team_1_corners, 
                team_2_corners, 
                team_1_corners_p1, 
                team_2_corners_p1, 
                team_1_corners_p2, 
                team_2_corners_p2, 
                team_1_corners_p3, 
                team_2_corners_p3, 
                team_1_corners_p4, 
                team_2_corners_p4, 
                first_goal_time,
                first_goal_team_id,
                first_goal_player_id,
                first_yellow_card_time,
                first_yellow_card_team_id,
                first_yellow_card_player_id,
                first_red_card_time,
                first_red_card_team_id,
                first_red_card_player_id,
                first_corner_time,
                first_corner_team_id,
                first_corner_player_id,
                first_substitution_time,
                first_substitution_team_id,
                first_substitution_player_id_on,
                first_substitution_player_id_off,
                created_at
            ) VALUES (
                :id, 
                :region_id, 
                :tournament_id, 
                :season_id, 
                :stage_id, 
                :group_id, 
                :week, 
                :start, 
                :start_set, 
                :team_1_coach_id, 
                :team_2_coach_id, 
                :team_1_id, 
                :team_2_id, 
                :stadium_visitors, 
                :stadium_id, 
                :anonse, 
                :status, 
                :status_name, 
                :content_type, 
                :ref_main_id, 
                :ref_assist_1_id, 
                :ref_assist_2_id, 
                :ref_fourth_id, 
                :weather_id, 
                :weather_name, 
                :team_1_goals, 
                :team_2_goals, 
                :team_1_goals_p1, 
                :team_2_goals_p1, 
                :team_1_goals_p2, 
                :team_2_goals_p2, 
                :team_1_goals_p3, 
                :team_2_goals_p3, 
                :team_1_goals_p4, 
                :team_2_goals_p4, 
                :team_1_goals_p, 
                :team_2_goals_p,
                :team_1_yellow_cards, 
                :team_2_yellow_cards, 
                :team_1_yellow_cards_p1, 
                :team_2_yellow_cards_p1, 
                :team_1_yellow_cards_p2, 
                :team_2_yellow_cards_p2, 
                :team_1_yellow_cards_p3, 
                :team_2_yellow_cards_p3, 
                :team_1_yellow_cards_p4, 
                :team_2_yellow_cards_p4, 
                :team_1_yellow_cards_p, 
                :team_2_yellow_cards_p,
                :team_1_red_cards, 
                :team_2_red_cards, 
                :team_1_red_cards_p1, 
                :team_2_red_cards_p1, 
                :team_1_red_cards_p2, 
                :team_2_red_cards_p2, 
                :team_1_red_cards_p3, 
                :team_2_red_cards_p3, 
                :team_1_red_cards_p4, 
                :team_2_red_cards_p4, 
                :team_1_red_cards_p, 
                :team_2_red_cards_p,
                :team_1_substitutions, 
                :team_2_substitutions, 
                :team_1_substitutions_p1, 
                :team_2_substitutions_p1, 
                :team_1_substitutions_p2, 
                :team_2_substitutions_p2, 
                :team_1_substitutions_p3, 
                :team_2_substitutions_p3, 
                :team_1_substitutions_p4, 
                :team_2_substitutions_p4, 
                :team_1_substitutions_p, 
                :team_2_substitutions_p,
                :team_1_corners, 
                :team_2_corners, 
                :team_1_corners_p1, 
                :team_2_corners_p1, 
                :team_1_corners_p2, 
                :team_2_corners_p2, 
                :team_1_corners_p3, 
                :team_2_corners_p3, 
                :team_1_corners_p4, 
                :team_2_corners_p4, 
                :first_goal_time,
                :first_goal_team_id,
                :first_goal_player_id,
                :first_yellow_card_time,
                :first_yellow_card_team_id,
                :first_yellow_card_player_id,
                :first_red_card_time,
                :first_red_card_team_id,
                :first_red_card_player_id,
                :first_corner_time,
                :first_corner_team_id,
                :first_corner_player_id,
                :first_substitution_time,
                :first_substitution_team_id,
                :first_substitution_player_id_on,
                :first_substitution_player_id_off,
                :created_at
            )");
        $stmt->execute(array(
            "id" => $this->id,
            "region_id" => $this->region_id,
            "tournament_id" => $this->tournament_id,
            "season_id" => $this->season_id,
            "stage_id" => $this->stage_id,
            "group_id" => $this->group_id,
            "week" => $this->week,
            "start" => $this->start,
            "start_set" => $this->start_set,
            "team_1_coach_id" => $this->team_1_coach_id,
            "team_2_coach_id" => $this->team_2_coach_id,
            "team_1_id" => $this->team_1_id,
            "team_2_id" => $this->team_2_id,
            "stadium_visitors" => $this->stadium_visitors,
            "stadium_id" => $this->stadium_id,
            "anonse" => $this->anonse,
            "status" => $this->status,
            "status_name" => $this->status_name,
            "content_type" => $this->content_type,
            "ref_main_id" => $this->ref_main_id,
            "ref_assist_1_id" => $this->ref_assist_1_id,
            "ref_assist_2_id" => $this->ref_assist_2_id,
            "ref_fourth_id" => $this->ref_fourth_id,
            "weather_id" => $this->weather_id,
            "weather_name" => $this->weather_name,
            "team_1_goals" => $this->team_1_goals,
            "team_2_goals" => $this->team_2_goals,
            "team_1_goals_p1" => $this->team_1_goals_p1,
            "team_2_goals_p1" => $this->team_2_goals_p1,
            "team_1_goals_p2" => $this->team_1_goals_p2,
            "team_2_goals_p2" => $this->team_2_goals_p2,
            "team_1_goals_p3" => $this->team_1_goals_p3,
            "team_2_goals_p3" => $this->team_2_goals_p3,
            "team_1_goals_p4" => $this->team_1_goals_p4,
            "team_2_goals_p4" => $this->team_2_goals_p4,
            "team_1_goals_p" => $this->team_1_goals_p,
            "team_2_goals_p" => $this->team_2_goals_p,
            "team_1_yellow_cards" => $this->team_1_yellow_cards,
            "team_2_yellow_cards" => $this->team_2_yellow_cards,
            "team_1_yellow_cards_p1" => $this->team_1_yellow_cards_p1,
            "team_2_yellow_cards_p1" => $this->team_2_yellow_cards_p1,
            "team_1_yellow_cards_p2" => $this->team_1_yellow_cards_p2,
            "team_2_yellow_cards_p2" => $this->team_2_yellow_cards_p2,
            "team_1_yellow_cards_p3" => $this->team_1_yellow_cards_p3,
            "team_2_yellow_cards_p3" => $this->team_2_yellow_cards_p3,
            "team_1_yellow_cards_p4" => $this->team_1_yellow_cards_p4,
            "team_2_yellow_cards_p4" => $this->team_2_yellow_cards_p4,
            "team_1_yellow_cards_p" => $this->team_1_yellow_cards_p,
            "team_2_yellow_cards_p" => $this->team_2_yellow_cards_p,
            "team_1_red_cards" => $this->team_1_red_cards,
            "team_2_red_cards" => $this->team_2_red_cards,
            "team_1_red_cards_p1" => $this->team_1_red_cards_p1,
            "team_2_red_cards_p1" => $this->team_2_red_cards_p1,
            "team_1_red_cards_p2" => $this->team_1_red_cards_p2,
            "team_2_red_cards_p2" => $this->team_2_red_cards_p2,
            "team_1_red_cards_p3" => $this->team_1_red_cards_p3,
            "team_2_red_cards_p3" => $this->team_2_red_cards_p3,
            "team_1_red_cards_p4" => $this->team_1_red_cards_p4,
            "team_2_red_cards_p4" => $this->team_2_red_cards_p4,
            "team_1_red_cards_p" => $this->team_1_red_cards_p,
            "team_2_red_cards_p" => $this->team_2_red_cards_p,
            "team_1_substitutions" => $this->team_1_substitutions,
            "team_2_substitutions" => $this->team_2_substitutions,
            "team_1_substitutions_p1" => $this->team_1_substitutions_p1,
            "team_2_substitutions_p1" => $this->team_2_substitutions_p1,
            "team_1_substitutions_p2" => $this->team_1_substitutions_p2,
            "team_2_substitutions_p2" => $this->team_2_substitutions_p2,
            "team_1_substitutions_p3" => $this->team_1_substitutions_p3,
            "team_2_substitutions_p3" => $this->team_2_substitutions_p3,
            "team_1_substitutions_p4" => $this->team_1_substitutions_p4,
            "team_2_substitutions_p4" => $this->team_2_substitutions_p4,
            "team_1_substitutions_p" => $this->team_1_substitutions_p,
            "team_2_substitutions_p" => $this->team_2_substitutions_p,
            "team_1_corners" => $this->team_1_corners,
            "team_2_corners" => $this->team_2_corners,
            "team_1_corners_p1" => $this->team_1_corners_p1,
            "team_2_corners_p1" => $this->team_2_corners_p1,
            "team_1_corners_p2" => $this->team_1_corners_p2,
            "team_2_corners_p2" => $this->team_2_corners_p2,
            "team_1_corners_p3" => $this->team_1_corners_p3,
            "team_2_corners_p3" => $this->team_2_corners_p3,
            "team_1_corners_p4" => $this->team_1_corners_p4,
            "team_2_corners_p4" => $this->team_2_corners_p4,
            "first_goal_time" => $this->first_goal_time,
            "first_goal_team_id" => $this->first_goal_team_id,
            "first_goal_player_id" => $this->first_goal_player_id,
            "first_yellow_card_time" => $this->first_yellow_card_time,
            "first_yellow_card_team_id" => $this->first_yellow_card_team_id,
            "first_yellow_card_player_id" => $this->first_yellow_card_player_id,
            "first_red_card_time" => $this->first_red_card_time,
            "first_red_card_team_id" => $this->first_red_card_team_id,
            "first_red_card_player_id" => $this->first_red_card_player_id,
            "first_corner_time" => $this->first_corner_time,
            "first_corner_team_id" => $this->first_corner_team_id,
            "first_corner_player_id" => $this->first_corner_player_id,
            "first_substitution_time" => $this->first_substitution_time,
            "first_substitution_team_id" => $this->first_substitution_team_id,
            "first_substitution_player_id_on" => $this->first_substitution_player_id_on,
            "first_substitution_player_id_off" => $this->first_substitution_player_id_off,
            "created_at" => date('Y-m-d H:i:s')
        ));
        return true;   

    }

//  ---------------------------------------------------------------------
//  Оновлення матчу
//  ---------------------------------------------------------------------
    protected function upd()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("UPDATE games SET 

            id = :id,
            region_id = :region_id,
            tournament_id = :tournament_id,
            season_id = :season_id,
            stage_id = :stage_id,
            group_id = :group_id,
            week = :week,
            start = :start,
            start_set = :start_set,
            team_1_coach_id = :team_1_coach_id,
            team_2_coach_id = :team_2_coach_id,
            team_1_id = :team_1_id,
            team_2_id = :team_2_id,
            stadium_visitors = :stadium_visitors,
            stadium_id = :stadium_id,
            anonse = :anonse,
            status = :status,
            status_name = :status_name,
            content_type = :content_type,
            referee_id = :ref_main_id,
            ref_assist_1_id = :ref_assist_1_id,
            ref_assist_2_id = :ref_assist_2_id,
            ref_fourth_id = :ref_fourth_id,
            weather_id = :weather_id,
            weather_name = :weather_name,
            team_1_goals = :team_1_goals,
            team_2_goals = :team_2_goals,
            team_1_goals_p1 = :team_1_goals_p1,
            team_2_goals_p1 = :team_2_goals_p1,
            team_1_goals_p2 = :team_1_goals_p2,
            team_2_goals_p2 = :team_2_goals_p2,
            team_1_goals_p3 = :team_1_goals_p3,
            team_2_goals_p3 = :team_2_goals_p3,
            team_1_goals_p4 = :team_1_goals_p4,
            team_2_goals_p4 = :team_2_goals_p4,
            team_1_goals_p = :team_1_goals_p,
            team_2_goals_p = :team_2_goals_p,
            team_1_yellow_cards = :team_1_yellow_cards,
            team_2_yellow_cards = :team_2_yellow_cards,
            team_1_yellow_cards_p1 = :team_1_yellow_cards_p1,
            team_2_yellow_cards_p1 = :team_2_yellow_cards_p1,
            team_1_yellow_cards_p2 = :team_1_yellow_cards_p2,
            team_2_yellow_cards_p2 = :team_2_yellow_cards_p2,
            team_1_yellow_cards_p3 = :team_1_yellow_cards_p3,
            team_2_yellow_cards_p3 = :team_2_yellow_cards_p3,
            team_1_yellow_cards_p4 = :team_1_yellow_cards_p4,
            team_2_yellow_cards_p4 = :team_2_yellow_cards_p4,
            team_1_yellow_cards_p = :team_1_yellow_cards_p,
            team_2_yellow_cards_p = :team_2_yellow_cards_p,
            team_1_red_cards = :team_1_red_cards,
            team_2_red_cards = :team_2_red_cards,
            team_1_red_cards_p1 = :team_1_red_cards_p1,
            team_2_red_cards_p1 = :team_2_red_cards_p1,
            team_1_red_cards_p2 = :team_1_red_cards_p2,
            team_2_red_cards_p2 = :team_2_red_cards_p2,
            team_1_red_cards_p3 = :team_1_red_cards_p3,
            team_2_red_cards_p3 = :team_2_red_cards_p3,
            team_1_red_cards_p4 = :team_1_red_cards_p4,
            team_2_red_cards_p4 = :team_2_red_cards_p4,
            team_1_red_cards_p = :team_1_red_cards_p,
            team_2_red_cards_p = :team_2_red_cards_p,
            team_1_substitutions = :team_1_substitutions,
            team_2_substitutions = :team_2_substitutions,
            team_1_substitutions_p1 = :team_1_substitutions_p1,
            team_2_substitutions_p1 = :team_2_substitutions_p1,
            team_1_substitutions_p2 = :team_1_substitutions_p2,
            team_2_substitutions_p2 = :team_2_substitutions_p2,
            team_1_substitutions_p3 = :team_1_substitutions_p3,
            team_2_substitutions_p3 = :team_2_substitutions_p3,
            team_1_substitutions_p4 = :team_1_substitutions_p4,
            team_2_substitutions_p4 = :team_2_substitutions_p4,
            team_1_substitutions_p = :team_1_substitutions_p,
            team_2_substitutions_p = :team_2_substitutions_p,
            team_1_corners = :team_1_corners,
            team_2_corners = :team_2_corners,
            team_1_corners_p1 = :team_1_corners_p1,
            team_2_corners_p1 = :team_2_corners_p1,
            team_1_corners_p2 = :team_1_corners_p2,
            team_2_corners_p2 = :team_2_corners_p2,
            team_1_corners_p3 = :team_1_corners_p3,
            team_2_corners_p3 = :team_2_corners_p3,
            team_1_corners_p4 = :team_1_corners_p4,
            team_2_corners_p4 = :team_2_corners_p4,
            first_goal_time = :first_goal_time,
            first_goal_team_id = :first_goal_team_id,
            first_goal_player_id = :first_goal_player_id,
            first_yellow_card_time = :first_yellow_card_time,
            first_yellow_card_team_id = :first_yellow_card_team_id,
            first_yellow_card_player_id = :first_yellow_card_player_id,
            first_red_card_time = :first_red_card_time,
            first_red_card_team_id = :first_red_card_team_id,
            first_red_card_player_id = :first_red_card_player_id,
            first_corner_time = :first_corner_time,
            first_corner_team_id = :first_corner_team_id,
            first_corner_player_id = :first_corner_player_id,
            first_substitution_time = :first_substitution_time,
            first_substitution_team_id = :first_substitution_team_id,
            first_substitution_player_id_on = :first_substitution_player_id_on,
            first_substitution_player_id_off = :first_substitution_player_id_off,
            updated_at = :updated_at
            
            WHERE id = :id");

        $stmt->execute(array(
            "id" => $this->id,
            "region_id" => $this->region_id,
            "tournament_id" => $this->tournament_id,
            "season_id" => $this->season_id,
            "stage_id" => $this->stage_id,
            "group_id" => $this->group_id,
            "week" => $this->week,
            "start" => $this->start,
            "start_set" => $this->start_set,
            "team_1_coach_id" => $this->team_1_coach_id,
            "team_2_coach_id" => $this->team_2_coach_id,
            "team_1_id" => $this->team_1_id,
            "team_2_id" => $this->team_2_id,
            "stadium_visitors" => $this->stadium_visitors,
            "stadium_id" => $this->stadium_id,
            "anonse" => $this->anonse,
            "status" => $this->status,
            "status_name" => $this->status_name,
            "content_type" => $this->content_type,
            "ref_main_id" => $this->ref_main_id,
            "ref_assist_1_id" => $this->ref_assist_1_id,
            "ref_assist_2_id" => $this->ref_assist_2_id,
            "ref_fourth_id" => $this->ref_fourth_id,
            "weather_id" => $this->weather_id,
            "weather_name" => $this->weather_name,
            "team_1_goals" => $this->team_1_goals,
            "team_2_goals" => $this->team_2_goals,
            "team_1_goals_p1" => $this->team_1_goals_p1,
            "team_2_goals_p1" => $this->team_2_goals_p1,
            "team_1_goals_p2" => $this->team_1_goals_p2,
            "team_2_goals_p2" => $this->team_2_goals_p2,
            "team_1_goals_p3" => $this->team_1_goals_p3,
            "team_2_goals_p3" => $this->team_2_goals_p3,
            "team_1_goals_p4" => $this->team_1_goals_p4,
            "team_2_goals_p4" => $this->team_2_goals_p4,
            "team_1_goals_p" => $this->team_1_goals_p,
            "team_2_goals_p" => $this->team_2_goals_p,
            "team_1_yellow_cards" => $this->team_1_yellow_cards,
            "team_2_yellow_cards" => $this->team_2_yellow_cards,
            "team_1_yellow_cards_p1" => $this->team_1_yellow_cards_p1,
            "team_2_yellow_cards_p1" => $this->team_2_yellow_cards_p1,
            "team_1_yellow_cards_p2" => $this->team_1_yellow_cards_p2,
            "team_2_yellow_cards_p2" => $this->team_2_yellow_cards_p2,
            "team_1_yellow_cards_p3" => $this->team_1_yellow_cards_p3,
            "team_2_yellow_cards_p3" => $this->team_2_yellow_cards_p3,
            "team_1_yellow_cards_p4" => $this->team_1_yellow_cards_p4,
            "team_2_yellow_cards_p4" => $this->team_2_yellow_cards_p4,
            "team_1_yellow_cards_p" => $this->team_1_yellow_cards_p,
            "team_2_yellow_cards_p" => $this->team_2_yellow_cards_p,
            "team_1_red_cards" => $this->team_1_red_cards,
            "team_2_red_cards" => $this->team_2_red_cards,
            "team_1_red_cards_p1" => $this->team_1_red_cards_p1,
            "team_2_red_cards_p1" => $this->team_2_red_cards_p1,
            "team_1_red_cards_p2" => $this->team_1_red_cards_p2,
            "team_2_red_cards_p2" => $this->team_2_red_cards_p2,
            "team_1_red_cards_p3" => $this->team_1_red_cards_p3,
            "team_2_red_cards_p3" => $this->team_2_red_cards_p3,
            "team_1_red_cards_p4" => $this->team_1_red_cards_p4,
            "team_2_red_cards_p4" => $this->team_2_red_cards_p4,
            "team_1_red_cards_p" => $this->team_1_red_cards_p,
            "team_2_red_cards_p" => $this->team_2_red_cards_p,
            "team_1_substitutions" => $this->team_1_substitutions,
            "team_2_substitutions" => $this->team_2_substitutions,
            "team_1_substitutions_p1" => $this->team_1_substitutions_p1,
            "team_2_substitutions_p1" => $this->team_2_substitutions_p1,
            "team_1_substitutions_p2" => $this->team_1_substitutions_p2,
            "team_2_substitutions_p2" => $this->team_2_substitutions_p2,
            "team_1_substitutions_p3" => $this->team_1_substitutions_p3,
            "team_2_substitutions_p3" => $this->team_2_substitutions_p3,
            "team_1_substitutions_p4" => $this->team_1_substitutions_p4,
            "team_2_substitutions_p4" => $this->team_2_substitutions_p4,
            "team_1_substitutions_p" => $this->team_1_substitutions_p,
            "team_2_substitutions_p" => $this->team_2_substitutions_p,
            "team_1_corners" => $this->team_1_corners,
            "team_2_corners" => $this->team_2_corners,
            "team_1_corners_p1" => $this->team_1_corners_p1,
            "team_2_corners_p1" => $this->team_2_corners_p1,
            "team_1_corners_p2" => $this->team_1_corners_p2,
            "team_2_corners_p2" => $this->team_2_corners_p2,
            "team_1_corners_p3" => $this->team_1_corners_p3,
            "team_2_corners_p3" => $this->team_2_corners_p3,
            "team_1_corners_p4" => $this->team_1_corners_p4,
            "team_2_corners_p4" => $this->team_2_corners_p4,
            "first_goal_time" => $this->first_goal_time,
            "first_goal_team_id" => $this->first_goal_team_id,
            "first_goal_player_id" => $this->first_goal_player_id,
            "first_yellow_card_time" => $this->first_yellow_card_time,
            "first_yellow_card_team_id" => $this->first_yellow_card_team_id,
            "first_yellow_card_player_id" => $this->first_yellow_card_player_id,
            "first_red_card_time" => $this->first_red_card_time,
            "first_red_card_team_id" => $this->first_red_card_team_id,
            "first_red_card_player_id" => $this->first_red_card_player_id,
            "first_corner_time" => $this->first_corner_time,
            "first_corner_team_id" => $this->first_corner_team_id,
            "first_corner_player_id" => $this->first_corner_player_id,
            "first_substitution_time" => $this->first_substitution_time,
            "first_substitution_team_id" => $this->first_substitution_team_id,
            "first_substitution_player_id_on" => $this->first_substitution_player_id_on,
            "first_substitution_player_id_off" => $this->first_substitution_player_id_off,
            "updated_at" => date('Y-m-d H:i:s')
        ));
        
        return true;
    }

}

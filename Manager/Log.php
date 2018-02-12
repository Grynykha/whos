<?php
namespace PRS\Manager;

use PRS\Model\DBL;

/////////////////////////////
   // Звіт роботи парсера //
  //////////////////////////////////////////////////////////////////////////////

class Log
{

//  ----------------------------------------------------------------------------        

    public $parserMode;
    public $timeStart;
    public $count_total = 1;
    public $subj_id = null;
    public $subj_str;
    public $nextDay = 0;
    public $nextWeek = 0;
    public $has_referee = 0;
    public $has_players = 0;
    public $has_coaches = 0;
    public $has_goals = 0;
    public $has_substitutions = 0;
    public $has_corners = 0;
    public $has_cards = 0;

    public $has_json = 0;
    public $error = 1;

    public $gameStart;

    // для тестування
    public $out;

//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    public function __construct()
    {
        $this->timeStart = date('Y-m-d H:i:s', time());
    }

//  ----------------------------------------------------------------------------        
//  Збереження логів
//  ----------------------------------------------------------------------------        
    public function save()
    {   
        $DBL = DBL::Instance()->GetConnect();
        $stmt = $DBL->prepare("INSERT INTO log_stats (parser, mode, start_time, count_total, subj_id, subj_str, next_day, next_week, has_players, has_referee, has_coaches, has_goals, has_substitutions, has_corners, has_cards, has_json, error, date_added) 
            VALUES('whoscored', :mode, :start_time, :count_total, :subj_id, :subj_str, :next_day, :next_week, :has_players, :has_referee, :has_coaches, :has_goals, :has_substitutions, :has_corners, :has_cards, :has_json, :error, :date_added)");
        $stmt->bindValue(':mode', $this->parserMode);
        $stmt->bindValue(':start_time', $this->timeStart);
        $stmt->bindValue(':count_total', $this->count_total);
        $stmt->bindValue(':subj_id', $this->subj_id);
        $stmt->bindValue(':subj_str', $this->subj_str);
        $stmt->bindValue(':next_day', $this->nextDay, \PDO::PARAM_INT);
        $stmt->bindValue(':next_week', $this->nextWeek, \PDO::PARAM_INT);
        $stmt->bindValue(':has_players', $this->has_players, \PDO::PARAM_INT);
        $stmt->bindValue(':has_referee', $this->has_referee, \PDO::PARAM_INT);
        $stmt->bindValue(':has_coaches', $this->has_coaches, \PDO::PARAM_INT);
        $stmt->bindValue(':has_goals', $this->has_goals, \PDO::PARAM_INT);
        $stmt->bindValue(':has_substitutions', $this->has_substitutions, \PDO::PARAM_INT);
        $stmt->bindValue(':has_corners', $this->has_corners, \PDO::PARAM_INT);
        $stmt->bindValue(':has_cards', $this->has_cards, \PDO::PARAM_INT);
        $stmt->bindValue(':has_json', $this->has_json, \PDO::PARAM_INT);
        $stmt->bindValue(':error', $this->error, \PDO::PARAM_INT);
        $stmt->bindValue(':date_added', date('Y-m-d H:i:s', time()));
        $stmt->execute();
    }

}
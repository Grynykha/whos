<?php
namespace PRS\Model;

////////////////
   // Заміна //
  //////////////////////////////////////////////////////////////////////////////

class Substitution
{
//  ----------------------------------------------------------------------------
//  Загальні дані (основна таблиця)

//  id
    public $id;
//  Назва
    public $game_id;
//  url
    public $team_id;
//  національність
    public $player_id_off;
//  дата народження
    public $player_id_on;
//  місце народження
    public $time;
//  країна народження
    public $period;

//  ----------------------------------------------------------------------------
//  Збереження
//  ----------------------------------------------------------------------------
    public function save()
    {   
        if ($this->ins())
            return true;
        return false;
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM game_substitution WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $num = $stmt->fetchColumn();
        if ($num){
            return true;
        }
        return false;
    }

//  ----------------------------------------------------------------------------
//  Добавлення в базу
//  ----------------------------------------------------------------------------
    protected function ins()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("INSERT INTO game_substitution(game_id, team_id, player_id_off, player_id_on, time, period) 
            VALUES(:game_id, :team_id, :player_id_off, :player_id_on, :time, :period)");
        $stmt->bindValue(':game_id', $this->game_id, \PDO::PARAM_INT);
        $stmt->bindValue(':team_id', $this->team_id, \PDO::PARAM_INT);
        $stmt->bindValue(':player_id_off', $this->player_id_off, \PDO::PARAM_INT);
        $stmt->bindValue(':player_id_on', $this->player_id_on, \PDO::PARAM_INT);
        $stmt->bindValue(':time', $this->time, \PDO::PARAM_INT);
        $stmt->bindValue(':period', $this->period, \PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

}

<?php
namespace PRS\Model;

////////////////
   // Картка //
  //////////////////////////////////////////////////////////////////////////////

class Card
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
    public $player_id;
//  дата народження
    public $time;
//  країна народження
    public $period;
//  місце народження
    public $attributes;
//  позиція
    public $extra_attributes;

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
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM game_cards WHERE id = :id");
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
        $stmt = $DB->prepare("INSERT INTO game_cards(game_id, team_id, player_id, time, period, attributes, extra_attributes) 
            VALUES(:game_id, :team_id, :player_id, :time, :period, :attributes, :extra_attributes)");
        $stmt->bindValue(':game_id', $this->game_id, \PDO::PARAM_INT);
        $stmt->bindValue(':team_id', $this->team_id, \PDO::PARAM_INT);
        $stmt->bindValue(':player_id', $this->player_id, \PDO::PARAM_INT);
        $stmt->bindValue(':time', $this->time, \PDO::PARAM_INT);
        $stmt->bindValue(':period', $this->period, \PDO::PARAM_INT);
        $stmt->bindValue(':attributes', $this->attributes, \PDO::PARAM_STR);
        $stmt->bindValue(':extra_attributes', $this->extra_attributes, \PDO::PARAM_STR);
        $stmt->execute();
        return true;

    }

}

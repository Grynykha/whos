<?php
namespace PRS\Model;

////////////////
   // Тренер //
  //////////////////////////////////////////////////////////////////////////////

class Coach
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  Назва
    public $name;
//  Назва
    public $alt_name;
//  національність
    public $nationality;
//  дата народження
    public $born_date;
//  країна народження
    public $born_region;
//  місце народження
    public $born_place;
//  позиція
    public $position;
//  зріст
    public $height;
//  вага
    public $weight;
//  якою ногою грає
    public $foot;
//  шлях до портрету
    public $img_src;
//  url
    public $href;

//  ----------------------------------------------------------------------------
//  Збереження
//  ----------------------------------------------------------------------------
    public function save()
    {   
        if ($this->ins()) return true;
        return false;
    }

//  ----------------------------------------------------------------------------
//  Збереження
//  ----------------------------------------------------------------------------
/*    public function saveToGame($game_id, $team_id)
    {   
        if (!$this->inDbToGame($game_id)) {
            if ($this->insToGame($game_id, $team_id)) {
                return true;
            }
        }
        return false;
    }*/

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM coaches WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $num = $stmt->fetchColumn();
        if ($num){
            return true;
        }
        return false;
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public static function getByName($name)
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT * FROM coaches_description LEFT JOIN coaches ON coaches_description.content_id = coaches.id WHERE coaches_description.name = :name LIMIT 1");
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();
        $coaches = $stmt->fetchAll(\PDO::FETCH_CLASS, 'PRS\Model\Coach');
        if (isset($coaches[0])){
            $coach = $coaches[0];
            return $coach;
        }
        
        $coach = new Coach;
        $coach->name = $name;
        $coach->save();
        return $coach;
    }

/*//  ----------------------------------------------------------------------------
//  Чи є в базі до матчу
//  ----------------------------------------------------------------------------
    public function inDbToGame($game_id)
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM coaches_to_games WHERE coach_id = :coach_id AND game_id = :game_id");
        $stmt->bindValue(':coach_id', $this->id, \PDO::PARAM_INT);
        $stmt->bindValue(':game_id', $game_id, \PDO::PARAM_INT);
        $stmt->execute();
        $num = $stmt->fetchColumn();
        if ($num){
            return true;
        }
        return false;
    }*/

/*//  ----------------------------------------------------------------------------
//  Зберегти зображення
//  ----------------------------------------------------------------------------
    public function saveImg()
    {   
        if ($this->img_src) {
            $ext = substr($this->img_src, strripos($this->img_src, '.'));
            $path = STORE_IMG_COACHES . 'coach_' . $this->id . $ext;
            file_put_contents($path, file_get_contents($this->img_src));
            return true;            
        }
        return false;            
    }*/

//  ----------------------------------------------------------------------------
//  Добавлення в базу
//  ----------------------------------------------------------------------------
    protected function ins()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("INSERT INTO coaches(nationality, born_date, born_region, born_place, position, height, weight, foot, img_src, date_added) 
            VALUES(:nationality, :born_date, :born_region, :born_place, :position, :height, :weight, :foot, :img_src, :date_added)");
        $stmt->bindValue(':nationality', $this->nationality, \PDO::PARAM_INT);
        $stmt->bindValue(':born_date', $this->born_date);
        $stmt->bindValue(':born_region', $this->born_region, \PDO::PARAM_INT);
        $stmt->bindValue(':born_place', $this->born_place, \PDO::PARAM_STR);
        $stmt->bindValue(':position', $this->position, \PDO::PARAM_STR);
        $stmt->bindValue(':height', $this->height, \PDO::PARAM_INT);
        $stmt->bindValue(':weight', $this->weight, \PDO::PARAM_INT);
        $stmt->bindValue(':foot', $this->foot, \PDO::PARAM_INT);
        $stmt->bindValue(':img_src', $this->img_src, \PDO::PARAM_STR);
        $stmt->bindValue(':date_added', date('Y-m-d H:i:s'));
        $stmt->execute();
        $this->id = $DB->lastInsertId();

        $stmt = $DB->prepare("INSERT INTO coaches_description(content_id, lang_id, name, alt_name) 
            VALUES(:content_id, :lang_id, :name, :alt_name)");
        $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
        $stmt->bindValue(':lang_id', 1);
        $stmt->bindValue(':name', $this->name, \PDO::PARAM_STR);
        $stmt->bindValue(':alt_name', $this->alt_name, \PDO::PARAM_STR);
        $stmt->execute();
        return true;
    }
/*
//  ----------------------------------------------------------------------------
//  Добавлення в базу до матчу
//  ----------------------------------------------------------------------------
    protected function insToGame($game_id, $team_id)
    {   
        $DB = DB::Instance()->GetConnect();
        if ($this->id) {
            $stmt = $DB->prepare("INSERT INTO coaches_to_games(game_id, coach_id, team_id) 
                VALUES(:game_id, :caoch_id, :team_id)");
            $stmt->bindValue(':game_id', $game_id, \PDO::PARAM_INT);
            $stmt->bindValue(':caoch_id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':team_id', $team_id, \PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } 
        return false;
    }    */

}

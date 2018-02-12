<?php
namespace PRS\Model;

/////////////////
   // Гравець //
  //////////////////////////////////////////////////////////////////////////////

class Player
{
//  ----------------------------------------------------------------------------
//  Загальні дані (основна таблиця)

//  id
    public $id;
//  Назва
    public $name;
//  url
    public $href;
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
//  чи потрібно апдейтити
    public $need_upd = false;


//  ----------------------------------------------------------------------------
//  Дані на матч (таблиця player_to_games)

//  стартовий склад(1), запасний(2) чи травмований(3)
    public $status;
//  номер гравця
    public $number;
//  травма
    public $injury = null;


//  ----------------------------------------------------------------------------
//  Збереження
//  ----------------------------------------------------------------------------
    public function save()
    {   
        if (!$this->inDB()) {
            return $this->ins();
            // Task::add... добавлення задачі для спаршування сторінки гравця
            // TODO
        } else {
            if ($this->need_upd)
                return $this->upd();
        }
        return true;
    }

//  ----------------------------------------------------------------------------
//  Збереження
//  ----------------------------------------------------------------------------
    public function saveToGame($game_id, $team_id)
    {   
        if (!$this->inDbToGame($game_id)) {
            if ($this->insToGame($game_id, $team_id)) {
                return true;
            }
        }
        return false;
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM players WHERE id = :id");
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
    public function inDbToGame($game_id)
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM players_to_games WHERE player_id = :player_id AND game_id = :game_id");
        $stmt->bindValue(':player_id', $this->id, \PDO::PARAM_INT);
        $stmt->bindValue(':game_id', $game_id, \PDO::PARAM_INT);
        $stmt->execute();
        $num = $stmt->fetchColumn();
        if ($num){
            return true;
        }
        return false;
    }

    /*
//  ----------------------------------------------------------------------------
//  Зберегти зображення
//  ----------------------------------------------------------------------------
    public function saveImg()
    {   
        if ($this->img_src) {
            $ext = substr($this->img_src, strripos($this->img_src, '.'));
            $path = STORE_IMG_PLAYERS . 'player_' . $this->id . $ext;
            file_put_contents($path, file_get_contents($this->img_src));
            return true;            
        }
        return false;            
    }
*/

//  ----------------------------------------------------------------------------
//  Добавлення в базу
//  ----------------------------------------------------------------------------
    protected function ins()
    {   
        $DB = DB::Instance()->GetConnect();
        if ($this->id) {
            $stmt = $DB->prepare("INSERT INTO players(id, nationality, born_date, born_region, born_place, position, height, weight, foot, img_src, date_added) 
                VALUES(:id, :nationality, :born_date, :born_region, :born_place, :position, :height, :weight, :foot, :img_src, :date_added)");
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':nationality', $this->nationality, \PDO::PARAM_STR);
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

            foreach ($this->name as $lang_id => $name) {
                $stmt = $DB->prepare("INSERT INTO players_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
                $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
                $stmt->bindValue(':lang_id', $lang_id);
                $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
                $stmt->execute();
            }
            return true;
        } 
        return false;
    }

//  ---------------------------------------------------------------------
//  Оновлення команди
//  ---------------------------------------------------------------------
    protected function upd()
    {
        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("UPDATE players SET 
            nationality = :nationality,
            born_date = :born_date,
            born_region = :born_region,
            born_place = :born_place,
            position = :position,
            height = :height,
            weight = :weight,
            foot = :foot,
            img_src = :img_src,
            date_added = :date_added          
              
            WHERE id = :id");

        $stmt->execute(array(
            "id" => $this->id,
            "nationality" => $this->nationality,
            "born_date" => $this->born_date,
            "born_region" => $this->born_region,
            "born_place" => $this->born_place,
            "position" => $this->position,
            "height" => $this->height,
            "weight" => $this->weight,
            "foot" => $this->foot,
            "img_src" => $this->img_src,
            "date_added" => date('Y-m-d H:i:s')
        ));

        $stmt = $DB->prepare("DELETE FROM players_description WHERE content_id = :content_id");
        $stmt->bindValue('content_id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        foreach ($this->name as $lang_id => $name) {
            $stmt = $DB->prepare("INSERT INTO players_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
            $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':lang_id', $lang_id);
            $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
            $stmt->execute();
        }

        return true;
    }

//  ----------------------------------------------------------------------------
//  Добавлення в базу
//  ----------------------------------------------------------------------------
    protected function insToGame($game_id, $team_id)
    {   
        $DB = DB::Instance()->GetConnect();
        if ($this->id) {
            $stmt = $DB->prepare("INSERT INTO players_to_games(game_id, player_id, team_id, number, status, injury) 
                VALUES(:game_id, :player_id, :team_id, :number, :status, :injury)");
            $stmt->bindValue(':game_id', $game_id, \PDO::PARAM_INT);
            $stmt->bindValue(':player_id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':team_id', $team_id, \PDO::PARAM_INT);
            $stmt->bindValue(':status', $this->status, \PDO::PARAM_INT);
            $stmt->bindValue(':number', (int)$this->number, \PDO::PARAM_INT);
            $stmt->bindValue(':injury', $this->injury, \PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } 
        return false;
    }
}

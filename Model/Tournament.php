<?php
namespace PRS\Model;

////////////////
   // Турнір //
  //////////////////////////////////////////////////////////////////////////////

class Tournament
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  Регіон
    public $region_id;
//  Тип турніру
    public $type;
//  
    public $is_primary;
//  Назва
    public $name;
    public $alt_name;

//  ----------------------------------------------------------------------------
//  Збереження
//  ----------------------------------------------------------------------------
    public function save()
    {   
        if (!$this->inDB()) {
            return $this->ins();
        }
        return false;
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM tournaments WHERE id = ?");
        $stmt->execute(array($this->id));
        $num = $stmt->fetchColumn();
        if ($num){
            return true;
        }
        return false;
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public static function getByStage($stage_id)
    {   
        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("SELECT seasons.tournament_id FROM stages LEFT JOIN seasons ON stages.season_id = seasons.id WHERE stages.id = ?");
        $stmt->execute(array($stage_id));
        $tour_id = $stmt->fetchColumn();
        return $tour_id;
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function getAllId()
    {   
        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("SELECT id FROM tournaments");
        $stmt->execute();
        $all_id = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        if ($all_id){
            return $all_id;
        }
        return false;
    }

//  ----------------------------------------------------------------------------
//  Добавлення в базу
//  ----------------------------------------------------------------------------
    protected function ins()
    {   
        $DB = DB::Instance()->GetConnect();
        if ($this->id) {
            $stmt = $DB->prepare("INSERT INTO tournaments(id, type, is_primary, region_id, date_added) VALUES(:id, :type, :is_primary, :region_id, :date_added)");
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':type', $this->type, \PDO::PARAM_INT);
            $stmt->bindValue(':is_primary', $this->is_primary, \PDO::PARAM_INT);
            $stmt->bindValue(':region_id', $this->region_id, \PDO::PARAM_INT);
            $stmt->bindValue(':date_added', date('Y-m-d H:i:s'));
            $stmt->execute();

            foreach ($this->name as $lang_id => $name) {
                $stmt = $DB->prepare("INSERT INTO tournaments_description(content_id, lang_id, name, alt_name) VALUES(:content_id, :lang_id, :name, :alt_name)");
                $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
                $stmt->bindValue(':lang_id', $lang_id);
                $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
                $stmt->bindValue(':alt_name', $this->alt_name[$lang_id], \PDO::PARAM_STR);
                $stmt->execute();
            }
            return true;
        } 
        return false;
    }

}

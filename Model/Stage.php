<?php
namespace PRS\Model;

//////////////
   // Етап //
  //////////////////////////////////////////////////////////////////////////////

class Stage
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  Регіон
    public $season_id;
//  Назва
    public $name;

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
//  --------------------------------------------------------------------------
//  Конструктор
//  --------------------------------------------------------------------------
    public function __construct()
    {
        $this->name[1] = 'Regular Season';
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM stages WHERE id = :id");
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
        if ($this->id) {
            $stmt = $DB->prepare("INSERT INTO stages(id, season_id, date_added) VALUES(:id, :season_id, :date_added)");
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':season_id', $this->season_id, \PDO::PARAM_INT);
            $stmt->bindValue(':date_added', date('Y-m-d H:i:s'));
            $stmt->execute();

            foreach ($this->name as $lang_id => $name) {
                $stmt = $DB->prepare("INSERT INTO stages_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
                $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
                $stmt->bindValue(':lang_id', $lang_id, \PDO::PARAM_INT);
                $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
                $stmt->execute();
            }
            return true;
        } 
        return false;
    }

}

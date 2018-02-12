<?php
namespace PRS\Model;

///////////////
   // Група //
  //////////////////////////////////////////////////////////////////////////////

class Group
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  Регіон
    public $stage_id;
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

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM groups WHERE id = :id");
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
            $stmt = $DB->prepare("INSERT INTO groups(id, stage_id, date_added) VALUES(:id, :stage_id, :date_added)");
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':stage_id', $this->stage_id, \PDO::PARAM_INT);
            $stmt->bindValue(':date_added', date('Y-m-d H:i:s'));
            $stmt->execute();

            $stmt = $DB->prepare("INSERT INTO groups_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
            $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':lang_id', 2);
            $stmt->bindValue(':name', $this->name, \PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } 
        return false;
    }

}

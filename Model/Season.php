<?php
namespace PRS\Model;

///////////////
   // Сезон //
  //////////////////////////////////////////////////////////////////////////////

class Season
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  турнір
    public $tournament_id;
//  назва
    public $years;

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
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM seasons WHERE id = :id");
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
            $stmt = $DB->prepare("INSERT INTO seasons(id, tournament_id, years) VALUES(:id, :tournament_id, :years)");
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':tournament_id', $this->tournament_id, \PDO::PARAM_INT);
            $stmt->bindValue(':years', $this->years, \PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } 
        return false;
    }


}

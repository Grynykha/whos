<?php
namespace PRS\Model;

////////////////
   // РЕГІОН //
  //////////////////////////////////////////////////////////////////////////////

class Region
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  назва регіону
    public $name;
    public $alt_name;
//  має ніціональні клубні турнірніри (1|0)
    public $club_domestic;
//  має міжнародні клубні турнірніри (1|0)
    public $club_international;
//  має турнірніри ніціональних збірних (1|0)
    public $national;

//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    public function __construct($id = null)
    {
    }

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
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM regions WHERE id = :id");
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
        $stmt = $DB->prepare("SELECT id, name, club_domestic, club_international, national FROM regions LEFT JOIN regions_description ON id = content_id WHERE name = ?");
        //$stmt->bindValue(':name', $this->name, \PDO::PARAM_STR);
        $stmt->execute([$name]);
       // $stadium = $stmt->fetch(\PDO::FETCH_LAZY);
        $regions = $stmt->fetchAll(\PDO::FETCH_CLASS, 'PRS\Model\Region');
        if (isset($regions[0])){
            return $regions[0];
        }
        return false;

    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public static function getByTourType($tour_type)
    {   
        $DB = DB::Instance()->GetConnect();
        $q_str = "SELECT id, name, club_domestic, club_international, national FROM regions LEFT JOIN regions_description ON id = content_id WHERE $tour_type = 1";
        $stmt = $DB->prepare($q_str);
        $stmt->execute();
        $regions = $stmt->fetchAll(\PDO::FETCH_CLASS, 'PRS\Model\Region');
        
        if (!$regions)
            return false;
        return $regions;
    }

//  ----------------------------------------------------------------------------
//  Добавлення в базу
//  ----------------------------------------------------------------------------
    protected function ins()
    {   
        $DB = DB::Instance()->GetConnect();
        if ($this->id) {
            $stmt = $DB->prepare("INSERT INTO regions(id) VALUES(:id)");
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->execute();

            foreach ($this->name as $lang_id => $name) {
                $stmt = $DB->prepare("INSERT INTO regions_description(content_id, lang_id, name, alt_name) VALUES(:content_id, :lang_id, :name, :alt_name)");
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

//  ----------------------------------------------------------------------------
//  Має клубні матчі
//  ----------------------------------------------------------------------------
    public function hasClubDomestic()
    {   

        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("UPDATE regions SET club_domestic = 1 WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

//  ----------------------------------------------------------------------------
//  Має клубні матчі
//  ----------------------------------------------------------------------------
    public function hasClubInternational()
    {   

        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("UPDATE regions SET club_international = 1 WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

//  ----------------------------------------------------------------------------
//  Має клубні матчі
//  ----------------------------------------------------------------------------
    public function hasNational()
    {   

        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("UPDATE regions SET national = 1 WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

}

<?php
namespace PRS\Model;

//////////////
   // МАТЧ //
  //////////////////////////////////////////////////////////////////////////////

class Team
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  назва команди
    public $name = [];
//  урл логотипа
    public $img_src;
//  Чи це національна збірна
    public $is_national;
//  Регіон
    public $region_id;
//  id турніру на сокервеї (c####)
    public $tournament_id;
//  id тренера
    public $coach_id;
//  рік відкриття
    public $year;
//  адреса
    public $address;
//  телефон
    public $phone;
//  факс
    public $fax;
//  сайт
    public $site;
//  емейл
    public $email;
//  стадіон
    public $stadium_id;
//  фан-сайти
    public $fans = [];
//  дата добавлення команди в базу
    public $date_added;

    public $date_upd;

    public $need_upd = false;



//  --------------------------------------------------------------------------
//  Конструктор
//  --------------------------------------------------------------------------
    public function __construct($id = null)
    {
    }

    /*
//  ---------------------------------------------------------------------
//  Чи є в базі
//  ---------------------------------------------------------------------
    public function needParse()
    {   
        $DB = DB::Instance()->GetConnect();
        //$stmt = $DB->prepare("SELECT COUNT(*) as count FROM teams WHERE id = :id");
        $stmt = $DB->prepare("SELECT date_upd FROM teams WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $date_upd = $stmt->fetchColumn();
        if ($date_upd){
            if (time() - strtotime($date_upd) > TEAM_UPD_DAY * 24 * 60 * 60) {
                $this->need_upd = true;
                return true;
            }
            return false;
        }
        $this->need_upd = false;
        return false;
    }

    */

//  ---------------------------------------------------------------------
//  Збереження
//  ---------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM teams WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $num = $stmt->fetchColumn();
        if ($num){
            return true;
        }
        return false;
    }

//  ---------------------------------------------------------------------
//  Збереження
//  ---------------------------------------------------------------------
    public function save()
    {
        if (!$this->inDB()){
            return $this->ins();
        } elseif ($this->need_upd)
            return $this->upd();
        return false;
    }

//  ---------------------------------------------------------------------
//  Збереження
//  ---------------------------------------------------------------------
//    public function save()
//    {   
//        if ($this->need_upd) {
//            $this->updTournament();
//            return true;
//        } else {
//            if ($this->ins()) return true;
//        }
//        return false;
//    }

/*
//  ----------------------------------------------------------------------------
//  Зберегти зображення
//  ----------------------------------------------------------------------------
    public function saveImg()
    {   
        if ($this->img_src) {
            $ext = substr($this->img_src, strripos($this->img_src, '.'));
            $path = STORE_IMG_TEAM . 'logo_team_' . $this->id . $ext;
            file_put_contents($path, file_get_contents($this->img_src));
            return true;            
        }
        return false;            
    }
*/


//  ---------------------------------------------------------------------
//  Добавлення матчу в базу
//  ---------------------------------------------------------------------
    protected function ins()
    {   
        $DB = DB::Instance()->GetConnect();
        if ($this->id) {
            $stmt = $DB->prepare("INSERT INTO teams 
                (id, is_national, img_src, region_id, tournament_id, coach_id, year, address, phone, fax, site, email, stadium_id, fans, date_added, date_upd) 
                VALUES 
                (:id, :is_national, :img_src, :region_id, :tournament_id, :coach_id, :year, :address, :phone, :fax, :site, :email, :stadium_id, :fans, :date_added, :date_upd)"
            );

            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':is_national', $this->is_national, \PDO::PARAM_INT);
            $stmt->bindValue(':img_src', $this->img_src, \PDO::PARAM_STR);
            $stmt->bindValue(':region_id', $this->region_id, \PDO::PARAM_INT);
            $stmt->bindValue(':tournament_id', $this->tournament_id, \PDO::PARAM_INT);
            $stmt->bindValue(':coach_id', $this->coach_id, \PDO::PARAM_INT);
            $stmt->bindValue(':year', $this->year, \PDO::PARAM_STR);
            $stmt->bindValue(':address', $this->address, \PDO::PARAM_STR);
            $stmt->bindValue(':phone', $this->phone, \PDO::PARAM_STR);
            $stmt->bindValue(':fax', $this->fax, \PDO::PARAM_STR);
            $stmt->bindValue(':site', $this->site, \PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, \PDO::PARAM_STR);
            $stmt->bindValue(':stadium_id', $this->stadium_id, \PDO::PARAM_INT);
            $stmt->bindValue(':fans', serialize($this->fans), \PDO::PARAM_STR);
            $stmt->bindValue(':date_added', date('Y-m-d H:i:s'));
            $stmt->bindValue(':date_upd', date('Y-m-d H:i:s'));
            $stmt->execute();

            foreach ($this->name as $lang_id => $name) {
                $stmt = $DB->prepare("INSERT INTO teams_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
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
//  Оновлення матчу
//  ---------------------------------------------------------------------
    protected function upd()
    {   
        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("UPDATE teams SET 
            is_national = :is_national,
            img_src = :img_src,
            region_id = :region_id,
            tournament_id = :tournament_id,
            coach_id = :coach_id,
            year = :year,
            address = :address,
            phone = :phone,
            fax = :fax,
            site = :site,
            email = :email,
            stadium_id = :stadium_id,
            fans = :fans,
            date_added = :date_added,
            date_upd = :date_upd
            
            WHERE id = :id");

        $stmt->execute(array(
            "id" => $this->id,
            "is_national" => $this->is_national,
            "img_src" => $this->img_src,
            "region_id" => $this->region_id,
            "tournament_id" => $this->tournament_id,
            "coach_id" => $this->coach_id,
            "year" => $this->year,
            "address" => $this->address,
            "phone" => $this->phone,
            "fax" => $this->fax,
            "site" => $this->site,
            "email" => $this->email,
            "stadium_id" => $this->stadium_id,
            "fans" => serialize($this->fans),
            "date_added" => date('Y-m-d H:i:s'),
            "date_upd" => date('Y-m-d H:i:s')
        ));

        $stmt = $DB->prepare("DELETE FROM teams_description WHERE content_id = :content_id");
        $stmt->bindValue('content_id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        foreach ($this->name as $lang_id => $name) {
            $stmt = $DB->prepare("INSERT INTO teams_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
            $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':lang_id', $lang_id);
            $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
            $stmt->execute();
        }

        return true;
    }

//  ---------------------------------------------------------------------
//  Оновлення матчу
//  ---------------------------------------------------------------------
    protected function updTournament()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("UPDATE teams SET tournament_id = :tournament_id, date_upd = :date_upd 
            WHERE id = :id");

        $stmt->execute(array(
            "id" => $this->id,
            "tournament_id" => $this->tournament_id,
            "date_upd" => date('Y-m-d H:i:s')
        ));
        
        return true;
    }

}

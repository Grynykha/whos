<?php
namespace PRS\Model;

/////////////////
   // Стадіон //
  //////////////////////////////////////////////////////////////////////////////

class Stadium
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  Назва
    public $name = [];
    public $alt_name = [];
//  місто
    public $city;
//  адреса
    public $address;
//  зіп
    public $zip;
//  телефон
    public $phone;
//  факс
    public $fax;
//  пошта
    public $email;
//  вмістимість
    public $max_visitors;
//  коли був відкритий
    public $opened;
//  покриття
    public $surface;
//  шлях до картинки
    public $img_src;

//  ----------------------------------------------------------------------------
//  Збереження
//  ----------------------------------------------------------------------------
    public function save()
    {   
        if (!$this->inDB()) {
           return $this->ins();
        }
        return true;
    }

//  ----------------------------------------------------------------------------
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        if ($this->name) {
            $DB = DB::Instance()->GetConnect();
            $stmt = $DB->prepare("SELECT content_id FROM stadiums_description WHERE name = ?");
            //$stmt->bindValue(':name', $this->name, \PDO::PARAM_STR);
            $stmt->execute([$this->name[1]]);
            $stadium = $stmt->fetch(\PDO::FETCH_LAZY);

            if ($stadium){
                $this->id = $stadium->content_id;
                return true;
            }
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
            $path = STORE_IMG_STADIUM . 'stadium_' . $this->id . $ext;
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
        if ($this->name) {
            $stmt = $DB->prepare("INSERT INTO stadiums(city, address, zip, phone, fax, email, max_visitors, opened, surface, date_added) 
                VALUES(:city, :address, :zip, :phone, :fax, :email, :max_visitors, :opened, :surface, :date_added)");
            $stmt->bindValue(':city', $this->city, \PDO::PARAM_INT);
            $stmt->bindValue(':address', $this->address, \PDO::PARAM_INT);
            $stmt->bindValue(':zip', $this->zip, \PDO::PARAM_INT);
            $stmt->bindValue(':phone', $this->phone, \PDO::PARAM_INT);
            $stmt->bindValue(':fax', $this->fax, \PDO::PARAM_INT);
            $stmt->bindValue(':email', $this->email, \PDO::PARAM_INT);
            $stmt->bindValue(':max_visitors', $this->max_visitors, \PDO::PARAM_INT);
            $stmt->bindValue(':opened', $this->opened, \PDO::PARAM_INT);
            $stmt->bindValue(':surface', $this->surface, \PDO::PARAM_INT);
            $stmt->bindValue(':date_added', date('Y-m-d H:i:s'));
            $stmt->execute();
            $this->id = $DB->lastInsertId();

            foreach ($this->name as $lang_id => $name) {
                $stmt = $DB->prepare("INSERT INTO stadiums_description(content_id, lang_id, name) 
                    VALUES(:content_id, :lang_id, :name)");
                $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
                $stmt->bindValue(':lang_id', $lang_id);
                $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
                $stmt->execute();
            }
            return true;
        } 
        return false;
    }

}

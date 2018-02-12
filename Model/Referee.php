<?php
namespace PRS\Model;

////////////////
   // Рефері //
  //////////////////////////////////////////////////////////////////////////////

class Referee
{
//  ----------------------------------------------------------------------------

//  id
    public $id;
//  Назва
    public $name;
//  національність
    public $nationality;
//  дата народження
    public $born_date;
//  країна народження
    public $born_region;
//  Місце народження
    public $born_place;
//  шлях до портрету
    public $img_src;
//  чи потрібно апдейтити
    public $need_upd = false;

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
//  Чи є в базі
//  ----------------------------------------------------------------------------
    public function inDB()
    {   
        $DB = DB::Instance()->GetConnect();
        $stmt = $DB->prepare("SELECT COUNT(*) as count FROM referee WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
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
            $path = STORE_IMG_REFEREE . 'referee_' . $this->id . $ext;
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
            $stmt = $DB->prepare("INSERT INTO referee(id, nationality, born_date, born_region, born_place, img_src, date_added) 
                VALUES(:id, :nationality, :born_date, :born_region, :born_place, :img_src, :date_added)");
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':nationality', $this->nationality, \PDO::PARAM_INT);
            $stmt->bindValue(':born_date', $this->born_date);
            $stmt->bindValue(':born_region', $this->born_region, \PDO::PARAM_INT);
            $stmt->bindValue(':born_place', $this->born_place, \PDO::PARAM_STR);
            $stmt->bindValue(':img_src', $this->img_src, \PDO::PARAM_STR);
            $stmt->bindValue(':date_added', date('Y-m-d H:i:s'));
            $stmt->execute();

            foreach ($this->name as $lang_id => $name) {
                $stmt = $DB->prepare("INSERT INTO referee_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
                $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
                $stmt->bindValue(':lang_id', $lang_id);
                $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
                $stmt->execute();
            }
            return true;
        } 
        return false;
    }

//  ----------------------------------------------------------------------------
//  Добавлення в базу
//  ----------------------------------------------------------------------------
    protected function upd()
    {
        $DB = DB::Instance()->GetConnect();

        $stmt = $DB->prepare("UPDATE referee SET 
            nationality = :nationality,
            born_date = :born_date,
            born_region = :born_region,
            born_place = :born_place,
            img_src = :img_src,
            date_added = :date_added          
            WHERE id = :id");

        $stmt->execute(array(
            "id" => $this->id,
            "nationality" => $this->nationality,
            "born_date" => $this->born_date,
            "born_region" => $this->born_region,
            "born_place" => $this->born_place,
            "img_src" => $this->img_src,
            "date_added" => date('Y-m-d H:i:s')
        ));

        $stmt = $DB->prepare("DELETE FROM referee_description WHERE content_id = :content_id");
        $stmt->bindValue('content_id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        foreach ($this->name as $lang_id => $name) {
            $stmt = $DB->prepare("INSERT INTO referee_description(content_id, lang_id, name) VALUES(:content_id, :lang_id, :name)");
            $stmt->bindValue(':content_id', $this->id, \PDO::PARAM_INT);
            $stmt->bindValue(':lang_id', $lang_id);
            $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
            $stmt->execute();
        }

        return true;
    }

}

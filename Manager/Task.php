<?php
namespace PRS\Manager;

use PRS\Model\DBA;
////////////////
   // Задача //
  //////////////////////////////////////////////////////////////////////////////

class Task
{
//  ----------------------------------------------------------------------------        

    public $id;
//  Назва парсера яким треба парсити
    public $parser;  // бувший type
//  Пріоритет задачі
    public $priority;
//  Для перепаршування
    public $reparse;
//  Ідентифікатор сторінки
    public $subj_id;
//  Ідентифікатор сторінки
    public $subj_str;
//  Статус виконання файла
    public $status;
//  Додаткові змінні
	public $vars;
//  УРЛ сторінки
	public $date_start;

//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    // function __construct($parser, $mode, $id, $url = null)
    // {
    // 	$this->parser = $parser;
    // 	$this->mode = $mode;
    // 	$this->id = $id;
    // 	$this->url = $url;
    // }


//  ----------------------------------------------------------------------------
//  таск "нічого не робити"
//  ----------------------------------------------------------------------------

    public static function getSleepTask():Task
    {
        $task = new Task();
        $task->parser = "sleep";
        return $task;
    }


//  ----------------------------------------------------------------------------
//  Status update
//  ----------------------------------------------------------------------------
    public function updStatus($status):bool
    {
        $this->status = $status;
        $DBA = DBA::Instance()->GetConnect();
        $stmt = $DBA->prepare("UPDATE a_tasks SET status = :status WHERE id = :id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
        return true;
    }

//  ----------------------------------------------------------------------------        
//  Status update
//  ----------------------------------------------------------------------------        
    public function clearOld():bool
    {
        $maxNum = 50000;
        $step = 500;
    	if ($this->id % $step == 0 and $this->id - $maxNum > 0) {
    	    $DBA = DBA::Instance()->GetConnect();
            $stmt = $DBA->prepare("DELETE FROM a_tasks WHERE id < :max_id");
            $stmt->bindValue(':max_id', $this->id - $maxNum);
            $stmt->execute();
        }
        return true;
    }

//  ----------------------------------------------------------------------------        
//  Add task
//  ----------------------------------------------------------------------------        
    public static function addTask($parser, $subj_id, $subj_str, $priority, $reparse, $date_start, $vars = ''): bool
    {
    	$DBA = DBA::Instance()->GetConnect();
        $stmt = $DBA->prepare("INSERT INTO a_tasks(parser, subj_id, subj_str, priority, reparse, status, date_start, vars) 
                                        VALUES(:parser, :subj_id, :subj_str, :priority, :reparse, :status, :date_start, :vars)");
        $stmt->bindValue(':parser', (string)$parser, \PDO::PARAM_STR);
        $stmt->bindValue(':subj_id', $subj_id);
        $stmt->bindValue(':subj_str', $subj_str);
        $stmt->bindValue(':priority', $priority);
        $stmt->bindValue(':reparse', $reparse);
        $stmt->bindValue(':status', 0);
        $stmt->bindValue(':date_start', $date_start);
        $stmt->bindValue(':vars', $vars);
        $stmt->execute();

        return true;
    }

//  ----------------------------------------------------------------------------
//  Get task
//  ----------------------------------------------------------------------------
    public static function getTask($status): Task
    {
        $DBA = DBA::Instance()->GetConnect();
        $now = date('Y-m-d H:i:s', time());
        $stmt = $DBA->prepare("SELECT * FROM a_tasks WHERE status = :status 
            AND date_start <= STR_TO_DATE(:now, '%Y-%m-%d %H:%i:%s') ORDER BY priority, id LIMIT 1");
        $stmt->bindValue(':now', $now);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        $tasks = $stmt->fetchAll(\PDO::FETCH_CLASS, 'PRS\Manager\Task');
        if (isset($tasks[0])) {
            $task = $tasks[0];
        } else {
            $task = self::getSleepTask();
        }
        return $task;
    }

//  ----------------------------------------------------------------------------
//  Get task by ID
//  ----------------------------------------------------------------------------
    public static function getTaskById($id): ?Task
    {
        $DBA = DBA::Instance()->GetConnect();
        $stmt = $DBA->prepare("SELECT * FROM a_tasks WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $tasks = $stmt->fetchAll(\PDO::FETCH_CLASS, 'PRS\Manager\Task');
        if (isset($tasks[0])) {
            return $tasks[0];
        } else {
            return null;
        }

    }

}
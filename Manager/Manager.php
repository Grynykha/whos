<?php
namespace PRS\Manager;

//use PRS\Manager\Task;
//use PRS\Manager\Response;
//use PRS\Manager\Report;

//use PRS\Model\DBL;
//use PRS\Model\DBA;
//use PRS\Model\DB;

use PRS\Parser\Parser;
use PRS\Model\Game;

/////////////////
   // MANAGER //
  //////////////////////////////////////////////////////////////////////////////

abstract class Manager
{
//  ----------------------------------------------------------------------------        
//    private $error;
//    private $count_total = 0;

//  ----------------------------------------------------------------------------
//  Віддаємо задачу в роботу
//  ----------------------------------------------------------------------------        
    public static function getTask()
    {
        $task = Task::getTask(0);
        if ($task != false) {
            $task->updStatus(1);
            $task->clearOld();
        }
//        echo json_encode($task);
        echo $task->id . '||' . $task->parser . '||' . $task->subj_id . '||' . $task->subj_str . '||' . $task->reparse . '||' . $task->vars;
        return true;
    }

    /**********************************
     * Встановлення задач для календаря
     * --------------------------------
     */
    public static function calendarSet()
    {
        // Добавлення задач для майбутніх матчів
        for ($delta = -1; $delta < CALENDAR_DEEP; $delta++) {
            Task::addTask('calendar', null, date('Y-M-d', time() + 60*60*24 * ($delta + 1)), 1, 0, date('Y-m-d H:i:s', time()));
        }

        // Добавлення задач для матчів, що вже відбулися
        $last_added_old_date = file_get_contents('Config/last_old_date.txt');
        for ($delta = 0; $delta < OLD_DEEP; $delta++) {
            $last_added_old_date = date('Y-M-d', strtotime($last_added_old_date) - 60*60*24);
            Task::addTask('calendar', null, $last_added_old_date, 1, 0, date('Y-m-d H:i:s', time()));
        }

        file_put_contents('Config/last_old_date.txt', $last_added_old_date);
    }

    /*************************************************
     * Викликається Зенкою після спаршування.
     * Запускає обробку PHP-скриптами спаршених даних.
     * Додає наступні задачі
     * -----------------------------------------------
     * @param $report
     * @return bool|string
     */
    public static function zennReport($report)
    {
        $out = true;
        $task = Task::getTaskById($report['taskId']);
        $task->updStatus(2);

        $zenn_status = $report['grabStatus'];

        $log = new Log();
        $log->parserMode = $task->parser;
        $log->subj_id = $task->subj_id;
        $log->subj_str = $task->subj_str;

        if ($zenn_status == 9) {
            $log->error = 0;
            switch ($task->parser) {

                case 'calendar':
                    $log->count_total = 0;
                    if (isset($report['gamesList'])) {
                        $games = explode('-', $report['gamesList']);
                        foreach ($games as $game_id) {
                            if (Game::getById($game_id) === null or $task->reparse == 1) {
                                Task::addTask('anonse', $game_id, null, 2, $task->reparse, date('Y-m-d H:i:s', time()));
                                $log->count_total++;
                            }
                        }
                    }
                    break;

                case 'anonse':
                    $log = Parser::gameAnonse($task);
                    $preview_date = date('Y-m-d H:i:s', strtotime($log->gameStart) - 60*60*24 * PREVIEW_DEEP);
                    $review_date = date('Y-m-d H:i:s', strtotime($log->gameStart) + 60 * REVIEW_DEEP);
                    if (strtotime($review_date) > time()){
                        Task::addTask('preview', $task->subj_id, null, 3, $task->reparse, $preview_date);
                    } else {
                        Task::addTask('review', $task->subj_id, null, 4, $task->reparse, $review_date);
                    }
                    break;

                case 'preview':
                    $log = Parser::gamePreview($task);
                    $review_date = date('Y-m-d H:i:s', strtotime($log->gameStart) + 60 * REVIEW_DEEP);
                    Task::addTask('review', $task->subj_id, null, 4, $task->reparse, $review_date);
                    break;

                case 'review':
                    $log = Parser::gameReview($task);


                    //////////////////////
                    // ТЕСТИЛКА
                    //////////////////////
                    ob_start();
                    echo "<pre>";
                    print_r($log);
                    echo "</pre>";
                    $out = ob_get_clean();
                    //////////////////////

                    break;
            }

        } else {
            $log->error = 1;
        }

        $task->updStatus($zenn_status);
        $log->save();

        return $out;
    }



/*
    //  ----------------------------------------------------------------------------
//  reparser
//  ----------------------------------------------------------------------------        
    public function reparser($vars)
    {

        $response = new Response;
        switch ($vars['object']) {
            
            case 'game':

                // id
                if (isset($vars['id'])) {
                    Task::addTask(0, $vars['id'], date('Y-m-d H:i:s', time()));
                    $response->success = true;

                // game_id
                } elseif (isset($vars['main_id'])) {
                    $DB = DB::Instance()->GetConnect();
                    $stmt = $DB->prepare("SELECT id FROM games WHERE main_id = :main_id");
                    $stmt->bindValue(':main_id', $vars['main_id']);
                    $stmt->execute();
                    $id = $stmt->fetchColumn();
                    if ($id) {
                        Task::addTask(0, $id, date('Y-m-d H:i:s', time()));
                        $response->success = true;
                    } else {
                        $response->success = false;
                        $response->errors[] = (object)["code" => 6, "msg" => "Нема таких матчів"];
                    }

                // dateform - dateto
                } elseif (isset($vars['datefrom']) and isset($vars['dateto'])) {
                    $DB = DB::Instance()->GetConnect();
                    $stmt = $DB->prepare("SELECT id FROM games WHERE start BETWEEN STR_TO_DATE(:from, '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(:to, '%Y-%m-%d %H:%i:%s')");
                    $stmt->bindValue(':from', $vars['datefrom'] . ' 00:00:00');
                    $stmt->bindValue(':to', $vars['dateto'] . ' 00:00:00');
                    $stmt->execute();
                    $k = 0;
                    while ($id = $stmt->fetchColumn()) {
                        Task::addTask(0, $id, date('Y-m-d H:i:s', time()));
                        $k++;
                    }
                    if ($k > 0) {
                        $response->success = true;
                    } else {
                        $response->success = false;
                        $response->errors[] = (object)["code" => 6, "msg" => "Нема таких матчів"];
                    }
                // error
                } else {
                    $response->success = false;
                    $response->errors[] = (object)["code" => 5, "msg" => "Не вірні параметри"];
                }
                break;
            case 'team':
                if (isset($vars['datefrom']) and isset($vars['dateto'])) {
                    if (isset($vars['id'])) {

                        $DB = DB::Instance()->GetConnect();
                        $stmt = $DB->prepare("SELECT id FROM games WHERE (team_1_id = :team_id OR team_2_id = :team_id) AND start BETWEEN STR_TO_DATE(:from, '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(:to, '%Y-%m-%d %H:%i:%s')");
                        $stmt->bindValue(':team_id', $vars['id'], \PDO::PARAM_INT);
                        $stmt->bindValue(':from', $vars['datefrom'] . ' 00:00:00');
                        $stmt->bindValue(':to', $vars['dateto'] . ' 00:00:00');
                        $stmt->execute();
                        $k = 0;
                        while ($id = $stmt->fetchColumn()) {
                            Task::addTask(0, $id, date('Y-m-d H:i:s', time()));
                            $k++;
                        }
                        if ($k > 0) {
                            $response->success = true;
                        } else {
                            $response->success = false;
                            $response->errors[] = (object)["code" => 6, "msg" => "Нема таких матчів"];
                        }
                        $response->success = true;
                        
                    } elseif (isset($vars['main_id'])) {

                        $DB = DB::Instance()->GetConnect();
                        $stmt = $DB->prepare("SELECT id FROM teams WHERE main_id = :main_id");
                        $stmt->bindValue(':main_id', $vars['main_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        $team_id = $stmt->fetchColumn();
                        
                        if ($team_id) {
                            $stmt = $DB->prepare("SELECT id FROM games WHERE (team_1_id = :team_id OR team_2_id = :team_id) AND start BETWEEN STR_TO_DATE(:from, '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(:to, '%Y-%m-%d %H:%i:%s')");
                            $stmt->bindValue(':team_id', $team_id, \PDO::PARAM_INT);
                            $stmt->bindValue(':from', $vars['datefrom'] . ' 00:00:00');
                            $stmt->bindValue(':to', $vars['dateto'] . ' 00:00:00');
                            $stmt->execute();
                            $k = 0;
                            while ($id = $stmt->fetchColumn()) {
                                Task::addTask(0, $id, date('Y-m-d H:i:s', time()));
                                $k++;
                            }
                            if ($k > 0) {
                                $response->success = true;
                            } else {
                                $response->success = false;
                                $response->errors[] = (object)["code" => 6, "msg" => "Нема таких матчів"];
                            }
                            $response->success = true;
                        } else {
                            $response->success = false;
                            $response->errors[] = (object)["code" => 8, "msg" => "немає команди з main_id = " . $vars['main_id']];
                        }
                    } else {
                        $response->success = false;
                        $response->errors[] = (object)["code" => 9, "msg" => "потрібно вказати id або main_id"];
                    }

                } else {
                    $response->success = false;
                    $response->errors[] = (object)["code" => 7, "msg" => "повинен бути вказаний діапазон дат"];
                }
                break;
            case 'tournament':
                if (isset($vars['datefrom']) and isset($vars['dateto'])) {
                    if (isset($vars['id'])) {

                        $DB = DB::Instance()->GetConnect();
                        $stmt = $DB->prepare("SELECT id FROM games WHERE tournament_id = :tournament_id AND start BETWEEN STR_TO_DATE(:from, '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(:to, '%Y-%m-%d %H:%i:%s')");
                        $stmt->bindValue(':tournament_id', $vars['id'], \PDO::PARAM_INT);
                        $stmt->bindValue(':from', $vars['datefrom'] . ' 00:00:00');
                        $stmt->bindValue(':to', $vars['dateto'] . ' 00:00:00');
                        $stmt->execute();
                        $k = 0;
                        while ($id = $stmt->fetchColumn()) {
                            Task::addTask(0, $id, date('Y-m-d H:i:s', time()));
                            $k++;
                        }
                        if ($k > 0) {
                            $response->success = true;
                        } else {
                            $response->success = false;
                            $response->errors[] = (object)["code" => 6, "msg" => "Нема таких матчів"];
                        }
                        $response->success = true;
                        
                    } elseif (isset($vars['main_id'])) {

                        $DB = DB::Instance()->GetConnect();
                        $stmt = $DB->prepare("SELECT id FROM tournaments WHERE main_id = :main_id");
                        $stmt->bindValue(':main_id', $vars['main_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                        $tournament_id = $stmt->fetchColumn();
                        
                        if ($tournament_id) {
                            $stmt = $DB->prepare("SELECT id FROM games WHERE tournament_id = :tournament_id AND start BETWEEN STR_TO_DATE(:from, '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(:to, '%Y-%m-%d %H:%i:%s')");
                            $stmt->bindValue(':tournament_id', $tournament_id, \PDO::PARAM_INT);
                            $stmt->bindValue(':from', $vars['datefrom'] . ' 00:00:00');
                            $stmt->bindValue(':to', $vars['dateto'] . ' 00:00:00');
                            $stmt->execute();
                            $k = 0;
                            while ($id = $stmt->fetchColumn()) {
                                Task::addTask(0, $id, date('Y-m-d H:i:s', time()));
                                $k++;
                            }
                            if ($k > 0) {
                                $response->success = true;
                            } else {
                                $response->success = false;
                                $response->errors[] = (object)["code" => 6, "msg" => "Нема таких матчів"];
                            }
                            $response->success = true;
                        } else {
                            $response->success = false;
                            $response->errors[] = (object)["code" => 8, "msg" => "немає команди з main_id = " . $vars['main_id']];
                        }
                    } else {
                        $response->success = false;
                        $response->errors[] = (object)["code" => 9, "msg" => "потрібно вказати id або main_id"];
                    }

                } else {
                    $response->success = false;
                    $response->errors[] = (object)["code" => 7, "msg" => "повинен бути вказаний діапазон дат"];
                }
                break;
            default:
                $response->errors[] = (object)["code" => 4, "msg" => "Не корректно вказано що саме парсити (object)"];
                $response->success = false;
                break;
        }
        return $response;
    }
*/

}

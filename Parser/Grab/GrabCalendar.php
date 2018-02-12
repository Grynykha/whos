<?php
namespace PRS\Parser\Grab;

use PRS\Parser\Engine\Start as Engine;
use PRS\Parser\Report;
use PRS\Model\Tournament;
use PRS\Model\Game;

///////////////////////////////////////////////////////////
   // ПАРСЕР НОВИХ МАТЧІВ ДЛЯ ОДНОГО ДНЯ ОДНОГО ТУРНІРУ //
  //////////////////////////////////////////////////////////////////////////////

class GrabCalendar
{   
    private $report;
    private $timer;
    private $task;

    private $parts = [];
    private $games = [];

    private $day;

//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    public function __construct()
    {
        // Включаємо таймер
        $this->timer = microtime(true);
    }
    
//  ----------------------------------------------------------------------------
//  Запуск
//  ----------------------------------------------------------------------------
    public function grab($task)
    {
        // створення звіту
        $this->report = new Report($task);
        
        $this->task = $task;

        // збереження потрібних частин дому
        $this->split();

        // запуск потрібного парсера
        $mode = $task->mode;
        $this->$mode();

        // точка завершення 
        $this->report->setTimeParse(round(microtime(true) - $this->timer, 3));
    }

//  ----------------------------------------------------------------------------
//  Парсер календар поточний
//  ----------------------------------------------------------------------------
    private function oneDay()
    {   
        // Запуск парсерів
        $this->parseNewGames();
        
        // ---------------------------------------------------------------------
        // збереження в БД
        foreach ($this->games as $game) {
            if (!$game->inDB()) {
                $this->report->countGame ++;
                if (strtotime($game->start) < time() + 60 * 60 * 24 && strtotime($game->start) > time())
                    $this->report->countNextDay++;
                if (strtotime($game->start) < time() + 60 * 60 * 24 * 7  && strtotime($game->start) > time())
                    $this->report->countNextWeek++; 
                $game->save();
                $this->report->addLog('Game = ' . $game->id . ' : : Team 1 = ' . $game->team_1_id . ' : : Team 2 = ' . $game->team_2_id . ' : : Date = ' . $game->start);
                // Добавлення задач
                $this->report->addTask("team", 'full', $game->team_1_id, $game->team_1_url);
                $this->report->addTask("team", 'full', $game->team_2_id, $game->team_2_url);
                //$this->report->addTask("game", 'anonse', $game->id, $game->url); 

            } else {
                $this->report->addLog('Матч ' . $game->id . ' : : вже є в базі');
            }
        }
    }

//  ----------------------------------------------------------------------------
//  Парсер календар поточний
//  ----------------------------------------------------------------------------
    private function oldDay()
    {   
        // Запуск парсерів
        $this->parseNewGames();
        
        // ---------------------------------------------------------------------
        // збереження в БД
        foreach ($this->games as $game) {
            if (!$game->inDB()) {
                $this->report->countGame ++;
                $game->save();
                $this->report->addLog('Game = ' . $game->id . ' : : Team 1 = ' . $game->team_1_id . ' : : Team 2 = ' . $game->team_2_id . ' : : Date = ' . $game->start);
                // Добавлення задач
                //$this->report->addTask("team", 'full', $game->team_1_id, $game->team_1_url);
                //$this->report->addTask("team", 'full', $game->team_2_id, $game->team_2_url);
                $this->report->addTask("game", 'review', $game->id, $game->url); 

            } else {
                $this->report->addLog('Матч ' . $game->id . ' : : вже є в базі');
            }
        }
    }

//  ----------------------------------------------------------------------------
//  Збір необхідних частин документу
//  ----------------------------------------------------------------------------
    private function split()
    {
        $curl_timer = microtime(true);
        $tournaments_id = [];

        $html = Engine::curlGet(TARGET_DOMAIN . $this->task->url);
        $dom = Engine::str_get_html($html);
        $trs = $dom->find('tr.group-head');
        $dom->clear();
        unset($dom);
        foreach ($trs as $tr) {
            $tournament = new Tournament;
            $tournament->id = explode('-', $tr->getAttribute('id'))[1];
            $tournaments_id[] = $tournament->id;

            // if ($tournament->inDB()) {
            //     $tournaments_id[] = $tournament->id;
            // } else {
            //     //$tournament->id = explode('-', $tr->getAttribute('id'))[1];
            // }

        }
        $base_url = str_replace('%date%', $this->task->id, TARGET_DOMAIN . URL_TEMPL_DAILYGAMES);
        if (is_array($tournaments_id)) {
            foreach ($tournaments_id as $tour_id) {
                $url = str_replace('%tournament_id%', $tour_id, $base_url);
                $json = Engine::curlGet($url);
                $arr = json_decode($json, true);
                $html = trim($arr['commands'][0]['parameters']['content']);
                if ($html !== '') {
                    $this->parts[$tour_id] = $html;
                }

            }
        }

        $this->report->setTimeCurl(round(microtime(true) - $curl_timer, 3));    
    }


//  ----------------------------------------------------------------------------
//  Парсинг основних даних матчу
//  ----------------------------------------------------------------------------
    private function parseNewGames()
    {
        foreach ($this->parts as $tournament_id => $html) {
            

            $dom = Engine::str_get_html($html);
            // Engine::dump_html_tree($dom);
            
            if (!$dom) continue;

            $one_game = $dom->find('tr.match');
            foreach ($one_game as $tr) {
                $game = new Game;
                
                @$game->url = substr($tr->find('td.info-button a', 0)->href, 1);
                @$game->id = (int)trim(array_reverse(explode('/', $game->url))[1]);
                if (!$game->id) continue;

                $game->tournament_id = $tournament_id;
                $game->start = date("Y-m-d H:i:s", $tr->getAttribute('data-timestamp'));
                $game->start_set = 0;
                $game->status = 1;


                $game->team_1_url = substr($tr->find('td.team-a a', 0)->href, 1);
                $game->team_2_url = substr($tr->find('td.team-b a', 0)->href, 1);
                
                $game->team_1_id = (int)trim(array_reverse(explode('/', $game->team_1_url))[1]);
                $game->team_2_id = (int)trim(array_reverse(explode('/', $game->team_2_url))[1]);
                
                $this->games[] = $game;
            }
            $dom->clear();
            unset($dom);
        }
    }

//  --------------------------------------------------------------------------
//  Повернути Репорт
//  --------------------------------------------------------------------------
    public function getReport()
    {
        return $this->report;
    }
}

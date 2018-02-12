<?php
namespace PRS\Parser\Grab;

use PRS\Parser\Engine\Start as Engine;
use PRS\Parser\Report;
use PRS\Model\Region;
use PRS\Model\Tournament;

//////////////////////////////
   // ПАРСЕР ВСІХ ТУРНІРІВ //
  //////////////////////////////////////////////////////////////////////////////

class GrabTournaments
{   
    private $report;
    private $timer;
    private $task;

    private $parts = [];
    private $tournaments = [];

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
//  Зібрати всі турніри
//  ----------------------------------------------------------------------------
    private function takeAll()
    {   
        // Запуск парсерів
        $this->parseTournaments();
        
        // ---------------------------------------------------------------------
        // збереження в БД
        foreach ($this->tournaments as $tour) {
            if ($tour->save()) {
                $this->report->addLog($tour->name . ' : : додано : : id = ' . $tour->id);
            } else {
                $this->report->addLog($tour->name . ' : : вже є в базі');
            }
        }
    }

//  ----------------------------------------------------------------------------
//  Збір необхідних частин документу
//  ----------------------------------------------------------------------------
    private function split()
    {
        $curl_timer = microtime(true);
        $base_url = str_replace('%tournament_type%', $this->task->id, TARGET_DOMAIN . URL_TEMPL_TOUR);
        $regions = Region::getByTourType($this->task->id);
        foreach ($regions as $region) {
            $url = str_replace('%region_id%', $region->id, $base_url);
            $json = Engine::curlGet($url);
            $arr = json_decode($json, true);
            $html = trim($arr['commands'][0]['parameters']['content']);
            $this->parts[$region->id] = $html;
        }
        $this->report->setTimeCurl(round(microtime(true) - $curl_timer, 3));
    }

//  ----------------------------------------------------------------------------
//  Парсер турнірів
//  ----------------------------------------------------------------------------
    private function parseTournaments()
    {
        foreach ($this->parts as $region_id => $html) {
            if (!$html) continue;
            $dom = Engine::str_get_html($html);
            if (!$dom) continue;
            $one_tour = $dom->find('li');
            unset($dom);
            foreach ($one_tour as $li) {
                $tournament = new Tournament;
                $tournament->region_id = $region_id;
                $tournament->id = (int)substr(trim(array_reverse(explode('/', $li->find('a',0)->href))[1]), 1);
                $tournament->name = trim($li->find('a',0)->innertext);
                switch ($this->task->id) {
                    case 'club_domestic':
                        $tournament->type = 1;
                        break;
                    case 'club_international':
                        $tournament->type = 2;
                        break;
                    case 'national':
                        $tournament->type = 3;
                        break;
                }
                $this->tournaments[] = $tournament;
            }
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

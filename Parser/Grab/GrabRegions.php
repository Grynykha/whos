<?php
namespace PRS\Parser\Grab;

use PRS\Parser\Engine\Start as Engine;
use PRS\Parser\Report;
use PRS\Model\Region;

//////////////////////////////
   // ПАРСЕР ВСІХ РЕГІОНІВ //
  //////////////////////////////////////////////////////////////////////////////

class GrabRegions
{   
    private $report;
    private $timer;
    private $task;

    private $regions = [];

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

        // запуск потрібного парсера
        $mode = $task->mode;
        $this->$mode();

        // точка завершення 
        $this->report->setTimeParse(round(microtime(true) - $this->timer, 3));
    }

//  ----------------------------------------------------------------------------
//  Парсер Анонс
//  ----------------------------------------------------------------------------
    private function takeAll()
    {   
        // Запуск парсерів
        $this->parseRegions();
        
        // ---------------------------------------------------------------------
        // збереження в БД
        foreach ($this->regions as $region) {
            if ($region->save()) {
                $this->report->addLog($region->name . ' : : додано : : id = ' . $region->id);
            } else {
                $this->report->addLog($region->name . ' : : вже є в базі');
            }

            if ($region->club_domestic === 1) {
                $region->hasClubDomestic();
                $this->report->addLog($region->name . ' : : має національні клубні турніри');
            }
            if ($region->club_international === 1) {
                $region->hasClubInternational();
                $this->report->addLog($region->name . ' : : має міжнародні клубні турніри');
            }
            if ($region->national === 1) {
                $region->hasNational();
                $this->report->addLog($region->name . ' : : має турніри національних збірних');
            }
        }
    }

//  ----------------------------------------------------------------------------
//  Збір необхідних частин документу
//  ----------------------------------------------------------------------------
    private function parseRegions()
    {
        // ---------------------------------------------------------------------
        // Завантаження сторінки
        // ---------------------------------------------------------------------
        $curl_timer = microtime(true);
        
        $html = Engine::curlGet(TARGET_DOMAIN . $this->task->url);
        if (!$html) return false;
        $this->report->setTimeCurl(round(microtime(true) - $curl_timer, 3));
        
        // ---------------------------------------------------------------------
        // Генеруємо дом
        // ---------------------------------------------------------------------
        $dom = Engine::str_get_html($html);
        if (!$dom) return false;
        
        // ---------------------------------------------------------------------
        if (in_array($this->task->mode, ['takeAll'])) {
            
            // -----------------------------------------------------------------
            // беремо 
            if (!$div = $dom->find('div#page_competitions_1_block_competitions_index_' . $this->task->id . '_4', 0)) {
                $this->report->addLog('Нема такого матчу :(');
                return false;
            } else {
                $all_li = $div->find('li');
                foreach ($all_li as $li) {
                    $region = new Region;
                    $region->id = (int)$li->getAttribute('data-area_id');
                    $region->name = trim($li->find('a', 0)->innertext);
                    $type = $this->task->id;
                    $region->$type = 1;

                    $this->regions[] = $region;
                }
            }
        }

        // ---------------------------------------------------------------------
        // Вичищаємо дом
        // ---------------------------------------------------------------------
        unset($dom);
    }

//  --------------------------------------------------------------------------
//  Повернути Репорт
//  --------------------------------------------------------------------------
    public function getReport()
    {
        return $this->report;
    }
}

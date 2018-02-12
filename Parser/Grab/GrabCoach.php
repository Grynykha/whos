<?php
namespace PRS\Parser\Grab;

use PRS\Parser\Engine\Start as Engine;
use PRS\Parser\Report;
use PRS\Model;

////////////////
   // Тренер //
  //////////////////////////////////////////////////////////////////////////////

class GrabCoach
{   
 
    private $parts;
    private $report;

    private $coach;

    private $timer;

//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    public function __construct()
    {
        // Включаємо таймер
        $this->timer = microtime(true);

        // Тренер
        $this->coach = new Model\Coach;
    }
    
//  ----------------------------------------------------------------------------
//  Запуск
//  ----------------------------------------------------------------------------
    public function grab($task)
    {
        // створення звіту
        $this->report = new Report($task);

        // Основні параметри
        $this->coach->id = $task->id;

        if (!$this->coach->inDB()) {
            $mode = $task->mode;
            // збереження потрібних частин дому
            $this->split($mode, $task->url);
            // запуск потрібного парсера
            if ($this->parts) $this->$mode();
        } else {
            $this->report->addLog('Тренер ' . $this->coach->id . ' вже є в базі');
        }
        // точка завершення 
        $this->report->setTimeParse(round(microtime(true) - $this->timer, 3));
    }

//  ----------------------------------------------------------------------------
//  Парсер всього
//  ----------------------------------------------------------------------------
    private function takeAll()
    {   
        // Запуск парсерів
        $this->parseMain();
        
        // збереження в БД
        $this->coach->save();
    }

//  ----------------------------------------------------------------------------
//  Збір необхідних частин документу
//  ----------------------------------------------------------------------------
    private function split($mode, $url)
    {
        $parts = new \stdClass();

        // ---------------------------------------------------------------------
        // Завантаження сторінки
        // ---------------------------------------------------------------------
        $curl_timer = microtime(true);
        
        $html = Engine::curlGet(TARGET_DOMAIN . $url);
        $this->report->setTimeCurl(round(microtime(true) - $curl_timer, 3));
        
        // ---------------------------------------------------------------------
        // Генеруємо дом
        // ---------------------------------------------------------------------
        $dom = Engine::str_get_html($html);
            
        // -----------------------------------------------------------------
        // основний верхній блок
        if (!$parts->top_block = $dom->find('#page_player_1_block_player_passport_3', 0)) {
            $this->report->addLog('Нема такого тренера:(');
            return false;
        }
                    
        // -----------------------------------------------------------------
        // Скорочене ім'я
        if (!$parts->h1 = $dom->find('h1', 0)) {
            return false;
        }


        // ---------------------------------------------------------------------
        // Вичищаємо дом
        // ---------------------------------------------------------------------
        unset($dom);
        $this->parts = $parts;

    }

//  ----------------------------------------------------------------------------
//  Парсинг основних даних команди
//  ----------------------------------------------------------------------------
    private function parseMain()
    {

        // Назва
        $this->coach->alt_name = $this->parts->h1->innertext;

        // Морда лиця
        $img_src = $this->parts->top_block->find('img', 0)->src;
        if ($img_src) {
            $this->coach->img_src = $img_src;
            $this->report->addLog('Фото тут = ' . $this->coach->img_src);
        } else {
            $this->report->addLog('Нема фото :(');
        }

        // Основна інфа
        $i = 0;
        while (@$param_n = $this->parts->top_block->find('dl', 0)->find('dt', $i)->innertext) {
            $param_n = trim($param_n);
            $paramNode = $this->parts->top_block->find('dl', 0)->find('dd', $i);
            $param_v = $paramNode->innertext;
            switch ($param_n) {
                case 'First name':
                    $full_name = $param_v;
                    break;
                case 'Last name':
                    $full_name .= ' ' . $param_v;
                    break;
                case 'Nationality':
                    $region = Model\Region::getByName($param_v);
                    $this->coach->nationality = $region->id;
                    $this->report->addLog("Nationality = " . $this->coach->nationality . ' : : ' . $param_v);
                    break;
                case 'Date of birth':
                    $date = date("Y-m-d H:i:s", strtotime($param_v));
                    $this->coach->born_date = $date;
                    $this->report->addLog("Born date = " . $this->coach->born_date);
                    break;
                case 'Country of birth':
                    $region = Model\Region::getByName($param_v);
                    $this->coach->born_region = $region->id;
                    $this->report->addLog("Born region = " . $this->coach->born_region . ' : : ' . $param_v);
                    break;
                case 'Place of birth':
                    $this->coach->born_place = $param_v;
                    $this->report->addLog("Born Place = " . $this->coach->born_place);
                    break;
                case 'Position':
                    $this->coach->position = $param_v;
                    $this->report->addLog("Position = " . $this->coach->position);
                    break;
                case 'Height':
                    $this->coach->height = (int)$param_v;
                    $this->report->addLog("Height = " . $this->coach->height);
                    break;
                case 'Weight':
                    $this->coach->weight = (int)$param_v;
                    $this->report->addLog("Weight = " . $this->coach->weight);
                    break;
                case 'Foot':
                    switch ($param_v) {
                        case 'Right':
                            $this->coach->foot = 1;
                            break;
                        case 'Left':
                            $this->coach->foot = 2;
                            break;
                        case 'Both':
                            $this->coach->foot = 3;
                            break;
                    }
                    $this->report->addLog("Foot = " . $this->coach->foot);
                    break;

            }
            //$this->report->addLog($param_n . ' = '. $param_v);
            $i++;
        }

        $this->coach->name = $full_name;
        $this->report->addLog("Name = ". $this->coach->name);
        $this->report->addLog("Alt name = " . $this->coach->alt_name);
        unset($this->parts->top_block);
    }

//  --------------------------------------------------------------------------
//  Повернути Репорт
//  --------------------------------------------------------------------------
    public function getReport()
    {
        return $this->report;
    }
}

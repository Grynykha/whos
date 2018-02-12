<?php
namespace PRS\Parser\Grab;

use PRS\Parser\Engine\Start as Engine;
use PRS\Parser\Report;
use PRS\Model;

////////////////
   // РЕФЕРІ //
  //////////////////////////////////////////////////////////////////////////////

class GrabReferee
{   
 
    private $parts;
    private $report;

    private $referee;

    private $timer;

//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    public function __construct()
    {
        // Включаємо таймер
        $this->timer = microtime(true);

        // Рефері
        $this->referee = new Model\Referee;
    }
    
//  ----------------------------------------------------------------------------
//  Запуск
//  ----------------------------------------------------------------------------
    public function grab($task)
    {
        // створення звіту
        $this->report = new Report($task);

        // Основні параметри
        $this->referee->id = $task->id;

        if (!$this->referee->inDB()) {
            $mode = $task->mode;
            // збереження потрібних частин дому
            $this->split($mode, $task->url);
            // запуск потрібного парсера
            if ($this->parts) $this->$mode();
        } else {
            $this->report->addLog('Рефері ' . $this->referee->id . ' вже є в базі');
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
        $this->referee->save();
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
            $this->report->addLog('Нема такого рефері:(');
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
        
        // Назва команди
        $this->referee->alt_name = $this->parts->h1->innertext;

        // Морда лиця
        $img_src = $this->parts->top_block->find('img', 0)->src;
        if ($img_src) {
            $this->referee->img_src = $img_src;
            $this->report->addLog('Фото тут = ' . $this->referee->img_src);
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
                    $this->referee->nationality = $region->id;
                    $this->report->addLog("Nationality = " . $this->referee->nationality . ' : : ' . $param_v);
                    break;
                case 'Date of birth':
                    $date = date("Y-m-d H:i:s", strtotime($param_v));
                    $this->referee->born_date = $date;
                    $this->report->addLog("Born date = " . $this->referee->born_date);
                    break;
                case 'Country of birth':
                    $region = Model\Region::getByName($param_v);
                    $this->referee->born_region = $region->id;
                    $this->report->addLog("Born region = " . $this->referee->born_region . ' : : ' . $param_v);
                    break;
                case 'Place of birth':
                    $this->referee->born_place = $param_v;
                    $this->report->addLog("Born Place = " . $this->referee->born_place);
                    break;

            }
            //$this->report->addLog($param_n . ' = '. $param_v);
            $i++;
        }

        $this->referee->name = $full_name;
        $this->report->addLog("Name = ". $this->referee->name);
        $this->report->addLog("Alt name = " . $this->referee->alt_name);
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

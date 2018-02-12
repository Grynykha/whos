<?php
namespace PRS\Parser\Grab;

use PRS\Parser\Engine\Start as Engine;
use PRS\Parser\Report;
use PRS\Model;

///////////////////////////////
   // ПАРСЕР СТОРІНКИ МАТЧУ //
  //////////////////////////////////////////////////////////////////////////////

class GrabTeam
{   
    use ParseStadium;
    
    private $parts;
    private $report;

    private $team;
    private $stadium;

    private $timer;

//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    public function __construct()
    {
        // Включаємо таймер
        $this->timer = microtime(true);

        // Модель команди
        $this->team = new Model\Team;
        // Модель стадіона
        $this->stadium = new Model\Stadium;
        
    }
    
//  ----------------------------------------------------------------------------
//  Запуск
//  ----------------------------------------------------------------------------
    public function grab($task)
    {
        // створення звіту
        $this->report = new Report($task);

        // Основні параметри
        $this->team->id = $task->id;
        $url_arr = explode('/', $task->url);
        $this->team->is_national = ($url_arr[2] == $url_arr[3]) ? 1 : 0;

        if ($this->team->needParse()) {
            $mode = $task->mode;
            // збереження потрібних частин дому
            if ($this->split($mode, $task->url)) $this->$mode();
        } else {
            $this->report->addLog('Команда ' . $this->team->id . ' вже є в базі і не потребує оновлення');
        }
        // точка завершення 
        $this->report->setTimeParse(round(microtime(true) - $this->timer, 3));
    }

//  ----------------------------------------------------------------------------
//  Парсер всього
//  ----------------------------------------------------------------------------
    private function full()
    {   
        // Запуск парсерів
        $this->parseMain();
        $this->parseStadium();
        
        // ---------------------------------------------------------------------
        // збереження в БД

        // стадіон
        $this->stadium->save();
        //$this->region->save();
        $this->team->stadium_id = $this->stadium->id;

        // КОМАНДА
        $this->team->save();
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
        $html_stadium = Engine::curlGet(TARGET_DOMAIN . $url . 'venue/');
        $this->report->setTimeCurl(round(microtime(true) - $curl_timer, 3));
        
        // ---------------------------------------------------------------------
        // Генеруємо дом
        // ---------------------------------------------------------------------
        $dom = Engine::str_get_html($html);
            
        // -----------------------------------------------------------------
        // основний верхній блок
        if (!$parts->top_block = $dom->find('#page_team_1_block_team_info_3', 0)) {
            $this->report->error = 1;
            $this->report->addLog('Нема такої команди :(');
            return false;
        }

        // -----------------------------------------------------------------
        // блок з таблицею турніру
        if (!$parts->tour_table = $dom->find('#page_team_1_block_team_table_9-wrapper', 0)) {
            $this->report->addLog('Не вказано турніру :(');
        }

        // -----------------------------------------------------------------
        // назва команди
        if (!$parts->h1 = $dom->find('h1', 0)) {
            $this->report->error = 1;
            $this->report->addLog('В команди на знайдена назва :(');
            return false;
        }

        // -----------------------------------------------------------------
        // фансайти
        $parts->fan_site = $dom->find('#page_team_1_block_team_fansites_10', 0);
    
        // ---------------------------------------------------------------------
        // Перегенеровуємо дом
        // ---------------------------------------------------------------------
        unset($dom);
        $dom = Engine::str_get_html($html_stadium);
        
        // ---------------------------------------------------------------------
        // Стадіон
        $parts->stadium = $dom->find('div.block_venue_info-wrapper', 0);

        // ---------------------------------------------------------------------
        // Вичищаємо дом
        // ---------------------------------------------------------------------
        unset($dom);
        $this->parts = $parts;
        return true;
    }

//  ----------------------------------------------------------------------------
//  Парсинг основних даних команди
//  ----------------------------------------------------------------------------
    private function parseMain()
    {
        
        // Назва команди
        $this->team->name = $this->parts->h1->innertext;
        $this->report->addLog('Назва команди = ' . $this->team->name);

        // Логотип
        $logo_src = $this->parts->top_block->find('img', 0)->src;
        if ($logo_src && substr(array_reverse(explode('/', $logo_src))[1], 0, strripos($logo_src, '.') - 1) != 'generic') {
            $this->team->img_src = $logo_src;
            $this->report->addLog('Логотип тут = ' . $this->team->img_src);
        } else {
            $this->report->addLog('Логотипа нема :(');
        }

        // Сайт
        if (@$this->team->site = $this->parts->top_block->find('p.website a', 0)->href) {
            $this->report->addLog('Сайт команди = ' . $this->team->site);
        }

        // Фансайти
        if (@$fans = $this->parts->fan_site->find('ul.tree', 0)) {
            foreach ($fans->find('li a') as $a) {
                $this->team->fans[] = $a->href;
            }
            $this->report->addLog('Фансайти = ' . implode(' | ', $this->team->fans));
        }

        // tournament
        if ($this->parts->tour_table) {
            $href_tour =$this->parts->tour_table->find('h2 a', 0)->href;
            $stage = substr(array_reverse(explode('/', $href_tour))[1], 1);
            $this->team->tournament_id = Model\Tournament::getByStage($stage);
            $this->report->addLog('Турнір = ' . $this->team->tournament_id);
        }

        $i = 0;
        while (@$param_n = $this->parts->top_block->find('dl', 0)->find('dt', $i)->innertext) {
            $param_n = trim($param_n);
            $paramNode = $this->parts->top_block->find('dl', 0)->find('dd', $i);
            switch ($param_n) {
                case 'Founded':
                    $param_v = $paramNode->innertext;
                    $this->team->year = $param_v;
                    break;
                case 'Address':
                    $addr_str = explode('<br />', trim($paramNode->innertext));
                    $param_v = [];
                    foreach ($addr_str as $str) {
                        $param_v[] = trim($str);
                    }
                    $param_v = implode(' ', $param_v);
                    $this->team->address = $param_v;
                    break;
                case 'Country':
                    $param_v = $paramNode->innertext;
                    $region = Model\Region::getByName($param_v);
                    $this->team->region_id = $region->id;
                    break;
                case 'Phone':
                    //var_dump($paramNode);
                    $param_v = $paramNode->innertext;
                    $this->team->phone = $param_v;
                    break;
                case 'Fax':
                    $param_v = $paramNode->innertext;
                    $this->team->fax = $param_v;
                    break;
                case 'E-mail':
                    $param_v = trim($paramNode->find('a', 0)->innertext);
                    $this->team->email = $param_v;
                    break;

            }
            $this->report->addLog($param_n . ' = '. $param_v);
            $i++;
        }



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

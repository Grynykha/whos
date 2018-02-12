<?php
namespace PRS\Parser\Grab;

trait ParseStadium 
{

//  ----------------------------------------------------------------------------
//  Парсинг стадіону
//  ----------------------------------------------------------------------------
    function parseStadium()
    {
        if (!$this->parts->stadium) {
            $this->report->addLog('Стадіон не вказаний');
            return false;
        }
        $this->stadium->name = trim($this->parts->stadium->find('h2', 0)->innertext);

        // Фотка
        @$img_src = $this->parts->stadium->find('img', 0)->src;
        if ($img_src && substr(array_reverse(explode('/', $img_src))[1], 0, strripos($img_src, '.') - 1) != 'generic') {
            $this->stadium->img_src = $img_src;
            $this->report->addLog('Картинка стадіону тут = ' . $this->stadium->img_src);
        } else {
            $this->report->addLog('Картинки нема');
        }

        $i = 0;
        $table = $this->parts->stadium->find('dl.details', 0);
        while (@$param_name = $table->find('dt', $i)->innertext) {
            $param_name = trim($param_name);
            $param_v = $table->find('dd', $i);
            switch ($param_name) {
                case 'Address:':
                    $this->stadium->address = $param_v->innertext;
                    break;
                case 'Zip code:':
                    $this->stadium->zip = $param_v->innertext;
                    break;
                case 'City:':
                    $this->stadium->city = $param_v->innertext;
                    break;
                case 'Phone:':
                    $this->stadium->phone = $param_v->innertext;
                    break;
                case 'Fax:':
                    $this->stadium->fax = $param_v->innertext;
                    break;
                case 'E-mail:':
                    $this->stadium->email = $param_v->find('a', 0)->innertext;
                    break;
                case 'Opened:':
                    $this->stadium->opened = $param_v->innertext;
                    break;
                case 'Capacity:':
                    $this->stadium->max_visitors = $param_v->innertext;
                    break;
                case 'Surface:':
                    $this->stadium->surface = $param_v->innertext;
                    break;
            }
            $i++;
        }
        $this->report->addLog('Стадіон: ' . $this->stadium->name . ' : : ' . $this->stadium->opened . ' : : ' . $this->stadium->city);
        //$this->parts->stadium

        unset($this->parts->stadium);
        return true;
    }

}
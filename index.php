<?php
namespace PRS;

use PRS\Manager\Manager;
//use PRS\Parser\Grabber;
//use PRS\Parser\Planner;

include_once 'Config/Autoloader.php';
include_once 'Config/Base.php';
include_once 'Config/Engine.php';
include_once 'Config/Parser.php';

// автогол (айді гравця з іншої команди)
// матчі, що не відбулись (перенесені, технічна поразка)


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>
</html>
<?

    if (isset($_GET['command'])) {
        
        $command = $_GET['command'];
        $vars = [];
        foreach ($_GET as $n => $v) {
            if ($n != 'command') {
                $vars[$n] = $v;
            }
        }

        if (method_exists('PRS\Manager\Manager', $command)) {
            $result = Manager::$command($vars);
            if($result !== true) echo $result;
        } else {
            echo 'err_2: Нема команди: ' . $command;
        }

    } else {
        echo 'err_1: Не вказана команда';
    }

//  END ========================================================================
?>







<?
//  ============================================================================
//  ECHO
//  ============================================================================

    // $s = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3);
    // $time_str = $s;
    // if ($s > 60) {
    //     $m = (int)($s / 60);
    //     $s -= $m * 60;
    //     $time_str = $m . ':' . round($s,3);
    // }
    // if (isset($m) && $m > 60) {
    //     $h = (int)($m / 60);
    //     $m -= $h * 60;
    //     $time_str = $h . ':' . $m . ':' . round($s,3);
    // }
    // echo '<hr>';
    // echo '<hr>';
    // echo 'Час виконання: ' . $time_str;
    // echo '<hr>';
    // echo '<hr>';
    // echo 'MEMORY <br>';
    // echo '<hr>';
    // echo 'Fin: ' . round(memory_get_usage() / 1024 / 1024, 3) . ' Mb <br>';
    // echo 'Max: ' . round(memory_get_peak_usage() / 1024 / 1024, 3) . ' Mb <br>';
    // echo '<hr><hr><hr><hr>';
    
    //echo '<pre>';
    //print_r(urldecode('http://int.soccerway.com/a/block_home_matches?block_id=block_home_matches_29&callback_params=%7B%22bookmaker_urls%22%3A%7B%2213%22%3A%5B%7B%22link%22%3A%22http%3A%2F%2Fwww.bet365.com%2Fhome%2F%3Faffiliate%3D365_179024%22%2C%22name%22%3A%22Bet%20365%22%7D%5D%7D%2C%22block_service_id%22%3A%22home_index_block_homematches%22%2C%22date%22%3A%222017-10-14%22%2C%22display%22%3A%22all%22%2C%22stage-value%22%3A%229%22%7D&action=showMatches&params=%7B%22competition_id%22%3A578%7D'));
    //echo '<br>';
    //print_r(urldecode('http://int.soccerway.com/a/block_date_matches?block_id=page_matches_1_block_date_matches_1&callback_params=%7B%22bookmaker_urls%22%3A%7B%2213%22%3A%5B%7B%22link%22%3A%22http%3A%2F%2Fwww.bet365.com%2Fhome%2F%3Faffiliate%3D365_179024%22%2C%22name%22%3A%22Bet%20365%22%7D%5D%7D%2C%22block_service_id%22%3A%22matches_index_block_datematches%22%2C%22date%22%3A%222017-10-15%22%2C%22stage-value%22%3A%2215%22%7D&action=showMatches&params=%7B%22competition_id%22%3A87%7D'));
    //echo '<br>';
    //echo '</pre>';

    //ob_start();
    //Engine::dump_html_tree($this->parts->top_block, $show_attr=true, $deep=0);
    //$node = ob_get_clean();
    //$this->report->addLog($node);
//  ============================================================================
?>

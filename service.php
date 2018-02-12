<?php
namespace PRS;
use PRS\Manager\Task;
use PRS\Manager\Manager;

include_once 'Config/Autoloader.php';
include_once 'Config/Base.php';
include_once 'Config/Engine.php';
include_once 'Config/Parser.php';

$vars = [];
foreach ($_GET as $n => $v) {
    if ($n != 'parser') {
        $vars[$n] = $v;
    }
}

Task::addTask('calendar', null, '2018-01-02', 1, 0, date('Y-m-d H:i:s', time()));

//$result = Manager::reparseBadGames($vars);
//var_dump($result);

// $response = Manager::reparser($vars);
// $response->returnJson();

//  END ========================================================================
?>

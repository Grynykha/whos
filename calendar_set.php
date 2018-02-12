<?php
namespace PRS;
use PRS\Manager\Task;

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

// Добавлення задач для майбутніх матчів
for ($delta = -1; $delta < CALENDAR_DEEP; $delta++) {
    Task::addTask('calendar', null, date('Y-M-d', time() + 60*60*24 * ($delta + 1)), 1, 0, date('Y-m-d H:i:s', time()));
}

// Добавлення задач для матчів, що вже відбулися
$last_added_old_date = file_get_contents('Config/last_old_date.txt');
echo 'start: ' . $last_added_old_date . '<br>';
for ($delta = 0; $delta < OLD_DEEP; $delta++) {
    $last_added_old_date = date('Y-M-d', strtotime($last_added_old_date) - 60*60*24);
    echo 'delta = ' . $delta . '  DATE: ' . $last_added_old_date . '<br>';
    Task::addTask('calendar', null, $last_added_old_date, 1, 0, date('Y-m-d H:i:s', time()));
}

echo 'SAVE: ' . $last_added_old_date . '<br>';
file_put_contents('Config/last_old_date.txt', $last_added_old_date);

//  END ========================================================================
?>

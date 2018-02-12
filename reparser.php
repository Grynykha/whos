<?php
namespace PRS;

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
$response = Manager::reparser($vars);
$response->returnJson();

//  END ========================================================================
?>

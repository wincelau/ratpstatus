<?php

date_default_timezone_set('Europe/Paris');

require __DIR__.'/app/Day.php';
require __DIR__.'/app/Disruption.php';
require __DIR__.'/app/File.php';

if(isset($argv[1]) && $argv[1]) {
    $_GET['date'] = $argv[1];
}

if(isset($_GET['date'])) {
    $_GET['date'] .= ' 05:00:00';
} else {
    $_GET['date'] = date('Y-m-d H:i:s');
}

if(isset($argv[2]) && $argv[2]) {
    $_GET['mode'] = $argv[2];
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'metros';

$day = new Day($_GET['date']);

$tomorowIsToday = date_format((clone $day->getDateStart())->modify('+1 day'), "Ymd") == date_format((new DateTime()), "Ymd");

$GLOBALS['isStaticResponse'] = isset($_SERVER['argv']) && !is_null($_SERVER['argv']);

function url($url) {
    if($GLOBALS['isStaticResponse']) {

        return $url;
    }

    preg_match('|/?([^/]*)/([^/]*).html|', $url, $matches);

    return "?".http_build_query(['date' => $matches[1], 'mode' => $matches[2]]);
}

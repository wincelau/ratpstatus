<?php

date_default_timezone_set('Europe/Paris');

require __DIR__.'/app/Config.php';
require __DIR__.'/app/Day.php';
require __DIR__.'/app/File.php';
require __DIR__.'/app/Line.php';
require __DIR__.'/app/Impact.php';
require __DIR__.'/app/Disruption.php';

if(isset($argv[1]) && $argv[1]) {
    $_GET['date'] = $argv[1];
}

if(isset($argv[2]) && $argv[2]) {
    $_GET['mode'] = $argv[2];
}

if(!isset($_GET['date'])) {
    $_GET['date'] = null;
}
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'metros';

if($_GET['date'] && getenv('USECACHE')) {
    $cacheFile = '/tmp/cacheratpstatus_'.$_GET['date'].'.object';
}

if(isset($cacheFile) && file_exists($cacheFile) && !getenv('RESETCACHE')) {
    $day = unserialize(file_get_contents($cacheFile));
} else {
    $day = new Day($_GET['date']);
    if(isset($cacheFile)) {
        file_put_contents($cacheFile, serialize($day));
    }
}

$pourcentages = $day->getPourcentages($mode);

$GLOBALS['isStaticResponse'] = isset($_SERVER['argv']) && !is_null($_SERVER['argv']);

function url($url) {
    if($GLOBALS['isStaticResponse']) {

        return $url;
    }

    preg_match('|/?([^/]*)/([^/]*).html|', $url, $matches);

    return "?".http_build_query(['date' => $matches[1], 'mode' => $matches[2]]);
}

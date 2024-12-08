<?php

date_default_timezone_set('Europe/Paris');

require __DIR__.'/app/File.php';
require __DIR__.'/app/Impact.php';

header('Content-Type: text/plain');

$today = (new DateTime())->modify('-3 minutes');

foreach(scandir(__DIR__.'/datas/json', SCANDIR_SORT_DESCENDING) as $folder) {
    if(!is_dir(__DIR__.'/datas/json/'.$folder) ){
        continue;
    }
    foreach(scandir(__DIR__.'/datas/json/'.$folder, SCANDIR_SORT_DESCENDING) as $filename) {
        $file = new File(__DIR__.'/datas/json/'.$folder.'/'.$filename);
        if(!$file->isValid()) {
            http_response_code('500');
            echo "Le dernier fichier est conforme : ERROR ($filename)\n";
            exit;
        }
        echo "Le dernier fichier est conforme : SUCCESS ($filename)\n";
        if($today > $file->getDate()) {
            http_response_code('500');
            echo "Le dernier fichier récupéré date d'il y a moins de 2 minutes : ERROR (".$file->getDate()->format('Y-m-d H:i:s').")\n";
            exit;
        }
        echo "Le dernier fichier récupéré date d'il y a moins de 2 minutes : SUCCESS (".$file->getDate()->format('Y-m-d H:i:s').")\n";
        break;
    }
    break;
}

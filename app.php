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

$day = new Day($_GET['date']);

if(isset($argv[2]) && $argv[2]) {
    $_GET['mode'] = $argv[2];
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'metros';

$tomorowIsToday = date_format((clone $day->getDateStart())->modify('+1 day'), "Ymd") == date_format((new DateTime()), "Ymd");
$isToday = date_format($day->getDateStart(), "Ymd") == date_format($day->getDateStart(), "Ymd");
$disruptions_message = [];
$disruptions_doublons = [];
foreach($day->getDistruptions() as $disruption) {
    $disruption = $disruption->data;
    $key = $disruption->title.$disruption->cause.preg_replace("/[\-_,]*/", "", $disruption->message).$disruption->severity.$disruption->applicationPeriods[0]->begin;
    if(isset($disruptions_doublons[$key]) && $disruption->lastUpdate < $disruptions_doublons[$key]->lastUpdate) {
        continue;
    }
    if(isset($disruptions_doublons[$key]) && $disruption->lastUpdate > $disruptions_doublons[$key]->lastUpdate) {
        $disruptions_message[$disruptions_doublons[$key]->id] = "\n";
    }

    $disruptions_message[$disruption->id] = "# ".$disruption->title."\n\n".str_replace('"', '', html_entity_decode(strip_tags($disruption->message)));
    $disruptions_doublons[$key] = $disruption;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>

<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, target-densitydpi=device-dpi">
<title>Suivi du trafic des <?php echo $modesLibelle[$mode] ?> du <?php echo date_format($day->getDateStart(), "d/m/Y"); ?> - RATP Status</title>
<meta name="description" content="Page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France">
<link rel="stylesheet" href="/css/style.css">
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if(document.querySelector('.ligne .e')) {
            window.scrollTo({ left: document.querySelector('.ligne .e').offsetLeft - window.innerWidth + 66 });
        }
        document.querySelector('#lignes').addEventListener('mouseover', function(e) {
            if(e.target.title) {
                replaceMessage(e.target);
            }
        })
        document.querySelector('#lignes').addEventListener('mouseout', function(e) {
            if(e.target.title) {
                e.target.title = e.target.dataset.title
                delete e.target.dataset.title
            }
        })
        document.querySelector('#lignes').addEventListener('click', function(e) {
            if(e.target.title) {
                replaceMessage(e.target);

                modal.innerText = e.target.title
                modal.showModal()
            }
        })
        const modal = document.getElementById('tooltipModal')
        modal.addEventListener('click', function(event) {
            modal.close();
        });
        modal.addEventListener('close', function(event) {
            const item = document.querySelector('[data-title]')
            if(item && item.title) {
                item.title = item.dataset.title
                delete item.dataset.title
            }
        })
    })

    function replaceMessage(item) {
        item.dataset.title = item.title;
        for(let disruptionId of item.title.split("\n")) {
            if(disruptionId.match(/^%/)) {
                disruptionId=disruptionId.replace(/%/g, '')
                if(disruptionId && disruptions[disruptionId]) {
                    item.title = item.title.replace('%'+disruptionId+'%', disruptions[disruptionId])
                }
            }
        }
        item.title = item.title.replace(/\n\n\n/g, '')
    }
</script>
</head>
<body>
<div id="container">
<div id="header">
<a id="lien_infos" href="https://github.com/wincelau/ratpstatus">?</a>
<a id="lien_refresh" href="" onclick="location.reload(); return false;">↻</a>
<h1><a style="<?php if((clone $day->getDateStart())->modify('-1 day') < new DateTime('2024-04-23')): ?>visibility: hidden;<?php endif; ?>" href="/<?php echo date_format($day->getDateStart(), "Ymd"); ?>/<?php echo $mode ?>.html"><</a> Suivi trafic du <?php echo date_format($day->getDateStart(), "d/m/Y"); ?> <a style="<?php if((clone $day->getDateStart())->modify('+1 day') > (new DateTime())->modify('+2 hour')): ?>visibility: hidden;<?php endif; ?>" href="/<?php if(!$tomorowIsToday): ?><?php echo date_format((clone $day->getDateStart())->modify('+1 day'), "Ymd"); ?>/<?php endif; ?><?php echo $mode ?>.html">></a></h1>
<div id="nav_mode"><?php foreach($day->getLignes() as $m => $ligne): ?><a style="<?php if($mode == $m): ?>font-weight: bold;<?php endif; ?>" href="/<?php if(!$isToday): ?><?php echo (new DateTime($dateStart))->format('Ymd') ?>/<?php endif; ?><?php echo $m ?>.html"><?php echo $modesLibelle[$m] ?></a><?php endforeach; ?></div>
<div class="hline"><?php for($i = 0; $i <= 1260; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</div>
<div id="lignes">
<?php foreach($day->getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne"><div class="logo"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" /></div>
<?php for($i = 0; $i < 1260; $i = $i + 2): ?><a class="i <?php echo $day->getColorClass($i, $ligne) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?>" title="<?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?><?php echo $day->getInfo($i, $ligne) ?>"></a>
<?php endfor; ?></div>
<?php endforeach; ?>
</div>
</div>
<p id="legende"><span class="ok"></span> Rien à signaler <span class="perturbe" style="margin-left: 20px;"></span> Perturbation <span class="bloque" style="margin-left: 20px;"></span> Blocage / Interruption <span class="travaux" style="margin-left: 20px;"></span> Travaux</p>
<p id="footer">
Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a> <small>(récupérées toutes le 2 minutes)</small><br /><br />
Projet publié sous licence libre AGPL-3.0 (<a href="https://github.com/wincelau/ratpstatus">voir les sources</a>) initié par <a href="https://piaille.fr/@winy">winy</a>
</p>
<script>
const disruptions=<?php echo json_encode($disruptions_message, JSON_UNESCAPED_UNICODE); ?>;
</script>
<dialog id="tooltipModal"></dialog>
</body>
</html>

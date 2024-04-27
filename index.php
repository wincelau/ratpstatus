<?php

date_default_timezone_set('Europe/Paris');
if(isset($argv[1]) && $argv[1]) {
    $_GET['date'] = $argv[1];
}
if(isset($argv[2]) && $argv[2]) {
    $_GET['mode'] = $argv[2];
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'metros';

$datePage = new DateTime(isset($_GET['date']) ? $_GET['date'].' 05:00:00' : date('Y-m-d H:i:s'));
$datePage->modify('-3 hours');
$dateStart = $datePage->format('Ymd').'T050000';
$datePage->modify('+ 1 day');
$dateEnd = $datePage->format('Ymd').'T020000';

$disruptions = [];

$previousDisruptions = [];
$currentDisruptions = [];

foreach(scandir('datas/json') as $file) {
  if(!is_file('datas/json/'.$file)) {
      continue;
  }
  $dateFile = preg_replace("/^([0-9]{8})/", '\1T', preg_replace("/_.*.json/", "", $file));
  $datas = json_decode(file_get_contents('datas/json/'.$file));
  foreach($datas->disruptions as $disruption) {
      if(preg_match('/modifications horaires/', $disruption->title)) {
          $disruption->cause = 'INFORMATION';
      }

      if(preg_match('/Modification de desserte/', $disruption->title)) {
          $disruption->cause = 'INFORMATION';
      }

      if(preg_match('/train court/', $disruption->title)) {
          $disruption->cause = 'INFORMATION';
      }
      if(isset($disruptions[$disruption->id])) {
          $disruptions[$disruption->id] = $disruption;
          $currentDisruptions[$disruption->id] = $disruption;
          continue;
      }
      $isInPeriod = false;
      foreach($disruption->applicationPeriods as $period) {
          if($dateStart >= $period->begin || $dateEnd >= $period->begin) {
              $isInPeriod = true;
              break;
          }
      }

      if(!$isInPeriod) {
          continue;
      }

      $isLine = false;
      foreach($datas->lines as $line) {
          foreach($line->impactedObjects as $object) {
              if($object->type != "line") {
                continue;
              }
              if(in_array($disruption->id, $object->disruptionIds)) {
                $isLine = true;
              }
          }
      }

      if(!$isLine) {
          continue;
      }

      $disruptions[$disruption->id] = $disruption;
      $currentDisruptions[$disruption->id] = $disruption;
  }
  foreach($previousDisruptions as $previousDisruption) {
      if(!isset($currentDisruptions[$previousDisruption->id]) && $disruptions[$previousDisruption->id]->applicationPeriods[0]->end > $dateFile) {
          $disruptions[$previousDisruption->id]->applicationPeriods[0]->end = $dateFile;
      }
  }
  $previousDisruptions = $currentDisruptions;
  $currentDisruptions = [];
}

function get_color_class($nbMinutes, $disruptions, $ligne) {
    $datePage = new DateTime(isset($_GET['date']) ? $_GET['date'].' 05:00:00' : date('Y-m-d H:i:s'));
    $datePage->modify('-3 hours');
    $dateStart = $datePage->format('Ymd').'T050000';
    $dateStartObject = new DateTime($dateStart);
    $dateStartObject->modify("+ ".$nbMinutes." minutes");
    $now = new DateTime();
    $severity = null;
    if($dateStartObject->format('YmdHis') > $now->format('YmdHis')) {
        return 'e';
    }
    $dateCurrent = $dateStartObject->format('Ymd\THis');
    foreach($disruptions as $disruption) {
        if(!preg_match('/'.$ligne.'[^0-9A-Z]+/', $disruption->title)) {
            continue;
        }
        if($disruption->severity == 'INFORMATION') {
            continue;
        }

        foreach($disruption->applicationPeriods as $period) {
            if($dateCurrent >= $period->begin && $dateCurrent <= $period->end && $disruption->cause == "PERTURBATION" && $severity != "BLOQUANTE") {
                $severity = $disruption->severity;
            }
        }
    }

    if($severity && $severity == 'BLOQUANTE') {
        return 'bloque';
    } elseif($severity) {
        return 'perturbe';
    }

    return "ok";
}

function get_infos($nbMinutes, $disruptions, $ligne) {
    $datePage = new DateTime(isset($_GET['date']) ? $_GET['date'].' 05:00:00' : date('Y-m-d H:i:s'));
    $datePage->modify('-3 hours');
    $dateStart = $datePage->format('Ymd').'T050000';
    $dateStartObject = new DateTime($dateStart);
    $dateStartObject->modify("+ ".$nbMinutes." minutes");
    $now = new DateTime();
    $message = null;
    //echo $dateStartObject->format('YmdHis')."\n";
    if($dateStartObject->format('YmdHis') > $now->format('YmdHis')) {
      return null;
    }
    $dateCurrent = $dateStartObject->format('Ymd\THis');
    foreach($disruptions as $disruption) {
      if(!preg_match('/'.$ligne.'[^0-9A-Z]+/', $disruption->title)) {
          continue;
      }
      if($disruption->severity == 'INFORMATION') {
          continue;
      }
      foreach($disruption->applicationPeriods as $period) {
          if($dateCurrent >= $period->begin && $dateCurrent <= $period->end && $disruption->cause == "PERTURBATION") {
            if(!$message) {
                $message .= "\n";
            }
            $message .= "\n".$disruption->id."\n";
          }
      }
    }

    if($message) {
        return strip_tags($message);
    }

    return "\n\nRien à signaler";
}

$baseUrlLogo = "/images/lignes/";
$modesLibelle = ["metros" => "Ⓜ️ Métros", "trains" => "🚆 RER/Trains", "tramways" => "🚈 Tramways"];
$lignes = [
    "metros" => [
        "Métro 1" => $baseUrlLogo."/1.svg",
        "Métro 2" => $baseUrlLogo."/2.svg",
        "Métro 3" => $baseUrlLogo."/3.svg",
        "Métro 3B" => $baseUrlLogo."/3b.svg",
        "Métro 4" => $baseUrlLogo."/4.svg",
        "Métro 5" => $baseUrlLogo."/5.svg",
        "Métro 6" => $baseUrlLogo."/6.svg",
        "Métro 7" => $baseUrlLogo."/7.svg",
        "Métro 7B" => $baseUrlLogo."/7b.svg",
        "Métro 8" => $baseUrlLogo."/8.svg",
        "Métro 9" => $baseUrlLogo."/9.svg",
        "Métro 10" => $baseUrlLogo."/10.svg",
        "Métro 11" => $baseUrlLogo."/11.svg",
        "Métro 12" => $baseUrlLogo."/12.svg",
        "Métro 13" => $baseUrlLogo."/13.svg",
        "Métro 14" => $baseUrlLogo."/14.svg",
    ],
    "trains" => [
        "Ligne A" => $baseUrlLogo."/a.svg",
        "Ligne B" => $baseUrlLogo."/b.svg",
        "Ligne C" => $baseUrlLogo."/c.svg",
        "Ligne D" => $baseUrlLogo."/d.svg",
        "Ligne E" => $baseUrlLogo."/e.svg",
        "Ligne H" => $baseUrlLogo."/h.svg",
        "Ligne J" => $baseUrlLogo."/j.svg",
        "Ligne K" => $baseUrlLogo."/k.svg",
        "Ligne L" => $baseUrlLogo."/l.svg",
        "Ligne N" => $baseUrlLogo."/n.svg",
        "Ligne P" => $baseUrlLogo."/p.svg",
        "Ligne R" => $baseUrlLogo."/r.svg",
        "Ligne U" => $baseUrlLogo."/u.svg",
    ],
    "tramways" => [
        "Tramway T1" => $baseUrlLogo."/t1.svg",
        "Tramway T2" => $baseUrlLogo."/t2.svg",
        "Tramway T3a" => $baseUrlLogo."/t3a.svg",
        "Tramway T3b" => $baseUrlLogo."/t3b.svg",
        "Tramway T4" => $baseUrlLogo."/t4.svg",
        "Tramway T5" => $baseUrlLogo."/t5.svg",
        "Tramway T6" => $baseUrlLogo."/t6.svg",
        "Tramway T7" => $baseUrlLogo."/t7.svg",
        "Tramway T8" => $baseUrlLogo."/t8.svg",
        "Tramway T9" => $baseUrlLogo."/t9.svg",
        "Tramway T10" => $baseUrlLogo."/t10.svg",
        "Tramway T11" => $baseUrlLogo."/t11.svg",
        "Tramway T12" => $baseUrlLogo."/t12.svg",
        "Tramway T13" => $baseUrlLogo."/t13.svg",
    ]
];

$tomorowIsToday = date_format((new DateTime($dateStart))->modify('+1 day'), "Ymd") == date_format((new DateTime()), "Ymd");
$isToday = date_format((new DateTime($dateStart)), "Ymd") == date_format((new DateTime()), "Ymd");
$disruptions_message = [];
foreach($disruptions as $disruption) {
    $disruptions_message[$disruption->id] = "# ".$disruption->title."\n\n".str_replace('"', '', html_entity_decode(strip_tags($disruption->message)));
}

?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>

<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, target-densitydpi=device-dpi">
<title>Suivi du trafic des <?php echo $modesLibelle[$mode] ?> du <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?> - RATP Status</title>
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
        for(const disruptionId of item.title.split("\n")) {
            if(disruptionId && disruptions[disruptionId]) {
                item.title = item.title.replace(disruptionId, disruptions[disruptionId]);
            }
        }
    }
</script>
</head>
<body>
<div id="container">
<header role="banner" id="header">
<a id="lien_infos" href="https://github.com/wincelau/ratpstatus">
    <span aria-hidden="true">?</span>
    <span class="visually-hidden">Informations sur le projet</span>
</a>
<a id="lien_refresh" href="" onclick="location.reload(); return false;">
    <span aria-hidden="true">↻</span>
    <span class="visually-hidden">Rafraîchir</span>
</a>
<h1>
    <a style="<?php if((new DateTime($dateStart))->modify('-1 day') < new DateTime('2024-04-23')): ?>visibility: hidden;<?php endif; ?>" href="/<?php echo date_format((new DateTime($dateStart))->modify('-1 day'), "Ymd"); ?>/<?php echo $mode ?>.html">
        <span aria-hidden="true"><</span>
        <span class="visually-hidden">Jour précédent</span>
    </a>

    Suivi trafic du <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?>
    <a style="<?php if((new DateTime($dateStart))->modify('+1 day') > new DateTime())->modify('+2 hour')): ?>visibility: hidden;<?php endif; ?>" href="/<?php if(!$tomorowIsToday): ?><?php echo date_format((new DateTime($dateStart))->modify('+1 day'), "Ymd"); ?>/<?php endif; ?><?php echo $mode ?>.html">
        <span aria-hidden="true">></span>
        <span class="visually-hidden">Jour suivant</span>
    </a>
</h1>
<div id="nav_mode"><?php foreach($lignes as $m => $ligne): ?><a style="<?php if($mode == $m): ?>font-weight: bold;<?php endif; ?>" href="/<?php if(!$isToday): ?><?php echo (new DateTime($dateStart))->format('Ymd') ?>/<?php endif; ?><?php echo $m ?>.html"><?php echo $modesLibelle[$m] ?></a><?php endforeach; ?></div>
<div class="hline"><?php for($i = 0; $i <= 1260; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</header>
<main role="main" id="lignes">
<div id="lignes">
<?php foreach($lignes[$mode] as $ligne => $logo): ?>
<div class="ligne"><div class="logo"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" /></div>
<?php for($i = 0; $i < 1260; $i = $i + 2): ?><a class="i <?php echo get_color_class($i, $disruptions, $ligne) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?>" title="<?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?><?php echo get_infos($i, $disruptions, $ligne) ?>"></a>
<?php endfor; ?></div>
<?php endforeach; ?>
</div>
</div>
<p id="legende"><span class="ok"></span> Rien à signaler <span class="perturbe" style="margin-left: 20px;"></span> Perturbation <span class="bloque" style="background: red; margin-left: 20px;"></span> Blocage / Interruption</p>
</main>
<footer role="contentinfo" id="footer">
<p>
Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a> <small>(récupérées toutes le 2 minutes)</small><br /><br />
Projet publié sous licence libre AGPL-3.0 (<a href="https://github.com/wincelau/ratpstatus">voir les sources</a>) initié par <a href="https://piaille.fr/@winy">winy</a>
</p>
</footer>
<script>
const disruptions=<?php echo json_encode($disruptions_message, JSON_UNESCAPED_UNICODE); ?>;
</script>
<dialog id="tooltipModal"></dialog>
</body>
</html>

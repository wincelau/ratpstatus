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
  if($dateFile < $dateStart) {
      continue;
  }
  if($dateFile > $dateEnd) {
      continue;
  }
  $datas = json_decode(file_get_contents('datas/json/'.$file));
  foreach($datas->disruptions as $disruption) {
      if(preg_match('/(modifications horaires|horaires modifiés)/', $disruption->title)) {
          $disruption->severity = 'INFORMATION';
      }

      if(preg_match('/Modification de desserte/', $disruption->title)) {
          $disruption->severity = 'INFORMATION';
      }

      if(preg_match('/train court/', $disruption->title)) {
          $disruption->severity = 'INFORMATION';
      }

      if($disruption->cause == "TRAVAUX" && $disruption->severity == "PERTURBEE" && preg_match('/Ligne D/', $disruption->title)) {
           $disruption->severity = 'INFORMATION';
      }

      if(isset($disruptions[$disruption->id])) {
          $disruptions[$disruption->id] = $disruption;
          $currentDisruptions[$disruption->id] = $disruption;
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
    $hasTravaux = false;
    foreach($disruptions as $disruption) {
        if(!preg_match('/(^| )'.$ligne.'[^0-9A-Z]+/', $disruption->title)) {
            continue;
        }
        if($disruption->severity == 'INFORMATION') {
            continue;
        }
        foreach($disruption->applicationPeriods as $period) {
            if($dateCurrent >= $period->begin && $dateCurrent <= $period->end && $disruption->cause == "TRAVAUX") {
                $hasTravaux = $disruption->severity;
            }
            if($dateCurrent >= $period->begin && $dateCurrent <= $period->end && $disruption->cause == "PERTURBATION" && $severity != "BLOQUANTE") {
                $severity = $disruption->severity;
            }
        }
    }

    if($severity && $severity == 'BLOQUANTE') {
        return 'bloque';
    } elseif($severity) {
        return 'perturbe';
    } elseif($hasTravaux) {
        return 'travaux';
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
          if($dateCurrent >= $period->begin && $dateCurrent <= $period->end) {
            if(!$message) {
                $message .= "\n";
            }
            $message .= "\n%".$disruption->id."%\n";
          }
      }
    }

    if($message) {
        return strip_tags($message);
    }

    return "\n\nRien à signaler";
}

$baseUrlLogo = "/images/lignes/";
$modesLibelle = ["metros" => "Ⓜ️ <span>Métros</span>", "trains" => "🚆 <span>RER/Trains</span>", "tramways" => "🚈 <span>Tramways</span>"];
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
        "T1" => $baseUrlLogo."/t1.svg",
        "T2" => $baseUrlLogo."/t2.svg",
        "T3A" => $baseUrlLogo."/t3a.svg",
        "T3B" => $baseUrlLogo."/t3b.svg",
        "T4" => $baseUrlLogo."/t4.svg",
        "T5" => $baseUrlLogo."/t5.svg",
        "T6" => $baseUrlLogo."/t6.svg",
        "T7" => $baseUrlLogo."/t7.svg",
        "T8" => $baseUrlLogo."/t8.svg",
        "T9" => $baseUrlLogo."/t9.svg",
        "T10" => $baseUrlLogo."/t10.svg",
        "T11" => $baseUrlLogo."/t11.svg",
        "T12" => $baseUrlLogo."/t12.svg",
        "T13" => $baseUrlLogo."/t13.svg",
    ]
];

$tomorowIsToday = date_format((new DateTime($dateStart))->modify('+1 day'), "Ymd") == date_format((new DateTime()), "Ymd");
$isToday = date_format((new DateTime($dateStart)), "Ymd") == date_format((new DateTime()), "Ymd");
$disruptions_message = [];
$disruptions_doublons = [];
foreach($disruptions as $disruption) {
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

function url($url) {
    if(!is_null($_SERVER['argv'])) {

        return $url;
    }

    preg_match('|/?([^/]*)/([^/]*).html|', $url, $matches);

    return "?".http_build_query(['date' => $matches[1], 'mode' => $matches[2]]);
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, target-densitydpi=device-dpi">
<title><?php echo strip_tags($modesLibelle[$mode]) ?> le <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?> - Suivi de l'état du trafic - RATP Status</title>
<meta name="description" content="Page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France">
<link rel="stylesheet" href="/css/style.css?202404300341">
<script src="/js/main.js"></script>
</head>
<body>
<div id="container">
<header role="banner" id="header">
<a id="lien_infos" onclick="document.getElementById('helpModal').showModal(); return false;" href="https://github.com/wincelau/ratpstatus" title="Aide et informations">
    <span aria-hidden="true">?</span>
    <span class="visually-hidden">Aide et informations</span>
</a>
<a id="lien_refresh" href="" onclick="location.reload(); return false;">
    <span aria-hidden="true">🔃</span>
    <span class="visually-hidden">Rafraîchir</span>
</a>
<h1>Suivi de l'état du trafic</h1>
<h2>
    <?php if((new DateTime($dateStart))->modify('-1 day') < new DateTime('2024-04-23')): ?>
    <a class="disabled">⬅️</a>
    <?php else: ?>
    <a title="Voir le jour précédent" href="<?php echo url("/".date_format((new DateTime($dateStart))->modify('-1 day'), "Ymd")."/".$mode.".html") ?>">
        <span aria-hidden="true">⬅️</span>
        <span class="visually-hidden">Voir le jour précédent</span>
    </a>
    <?php endif; ?>
    <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?>
    <?php if((new DateTime($dateStart))->modify('+1 day') > (new DateTime())->modify('+2 hour')): ?>
    <a class="disabled">➡️</a>
    <?php else: ?>
    <a title="Voir le jour suivant" style="" href="<?php echo url("/".((!$tomorowIsToday) ? date_format((new DateTime($dateStart))->modify('+1 day'), "Ymd")."/" : null).$mode.".html") ?>">
        <span aria-hidden="true">➡️</span>
        <span class="visually-hidden">Voir le jour suivant</span>
    </a>
    <?php endif; ?>
</h2>
<nav id="nav_mode"><?php foreach($lignes as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo url("/".((!$isToday) ? (new DateTime($dateStart))->format('Ymd')."/" : null).$m.".html") ?>"><?php echo $modesLibelle[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php for($i = 0; $i <= 1260; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</header>
<main role="main">
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
    Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a> <small>(récupérées toutes le 2 minutes)</small>
</p>
<p>
    Projet publié sous licence libre AGPL-3.0 (<a href="https://github.com/wincelau/ratpstatus">voir les sources</a>) initié par <a href="https://piaille.fr/@winy">winy</a>
</p>
<p>Ce site n'est pas un site officiel de la <a href="https://ratp.fr/">RATP</a></p>
</footer>
<script>
const disruptions=<?php echo json_encode($disruptions_message, JSON_UNESCAPED_UNICODE); ?>;
</script>
<dialog id="tooltipModal"></dialog>
<dialog id="helpModal">
    <h3>Aide et informations</h3>
    <p>RATPstatus.fr est une page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France</p>
    <p>L'état du trafic est récupéré toutes les 2 minutes à partir du 23 avril 2024.</p>
    <p>Chaque bloc répresente une durée de 2 minutes, les couleurs ont la signification suivante :<br /><br />
        <span class="ok"></span> Rien à signaler<br />
        <span class="perturbe"></span> Perturbation<br />
        <span class="bloque"></span> Blocage / Interruption<br />
        <span class="travaux"></span> Travaux
    </p>
    <p>Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a>.</p>
    <p>Le projet initié par <a href="https://piaille.fr/@winy">winy</a> est publié sous licence libre AGPL-3.0 : <a href="https://github.com/wincelau/ratpstatus">https://github.com/wincelau/ratpstatus</a></p>
</dialog>
</body>
</html>

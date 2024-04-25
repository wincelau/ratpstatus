<?php

date_default_timezone_set('Europe/Paris');
if(isset($argv[1]) && $argv[1]) {
    $_GET['date'] = $argv[1];
}
if(isset($argv[2]) && $argv[2]) {
    $_GET['mode'] = $argv[2];
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'metro';

$datePage = new DateTime(isset($_GET['date']) ? $_GET['date'].' 05:00:00' : date('Y-m-d H:i:s'));
$datePage->modify('-3 hours');
$dateStart = $datePage->format('Ymd').'T050000';
$datePage->modify('+ 1 day');
$dateEnd = $datePage->format('Ymd').'T020000';

$disruptions = [];

$previousDisruptions = [];
$currentDisruptions = [];

foreach(scandir('datas') as $file) {
  if(!is_file('datas/'.$file)) {
      continue;
  }
  if(!preg_match('/optimized/', $file)) {
      continue;
  }
  $datas = json_decode(file_get_contents('datas/'.$file));
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

      //$disruption->id = preg_replace('/^[a-z0-9]+-/', '', $disruption->id);
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
      $dateFile = preg_replace("/^([0-9]{8})/", '\1T', str_replace("_disruptions.json", "", $file));
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
      return "À venir";
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

            $message .= $disruption->title." (".$disruption->id." - ".$disruption->severity.")\n";
          }
      }
    }

    if($message) {
        return strip_tags($message);
    }

    return "OK";
}

$baseUrlLogo = "https://www.ratp.fr/sites/default/files/lines-assets/picto";
$modesLibelle = ["metros" => "Ⓜ️ Métros", "trains" => "🚆 RER/Trains", "tramways" => "🚈 Tramways"];
$lignes = [
    "metros" => [
        "Métro 1" => $baseUrlLogo."/metro/picto_metro_ligne-1.svg",
        "Métro 2" => $baseUrlLogo."/metro/picto_metro_ligne-2.svg",
        "Métro 3" => $baseUrlLogo."/metro/picto_metro_ligne-3.svg",
        "Métro 3B" => $baseUrlLogo."/metro/picto_metro_ligne-3b.svg",
        "Métro 4" => $baseUrlLogo."/metro/picto_metro_ligne-4.svg",
        "Métro 5" => $baseUrlLogo."/metro/picto_metro_ligne-5.svg",
        "Métro 6" => $baseUrlLogo."/metro/picto_metro_ligne-6.svg",
        "Métro 7" => $baseUrlLogo."/metro/picto_metro_ligne-7.svg",
        "Métro 7B" => $baseUrlLogo."/metro/picto_metro_ligne-7b.svg",
        "Métro 8" => $baseUrlLogo."/metro/picto_metro_ligne-8.svg",
        "Métro 9" => $baseUrlLogo."/metro/picto_metro_ligne-9.svg",
        "Métro 10" => $baseUrlLogo."/metro/picto_metro_ligne-10.svg",
        "Métro 11" => $baseUrlLogo."/metro/picto_metro_ligne-11.svg",
        "Métro 12" => $baseUrlLogo."/metro/picto_metro_ligne-12.svg",
        "Métro 13" => $baseUrlLogo."/metro/picto_metro_ligne-13.svg",
        "Métro 14" => $baseUrlLogo."/metro/picto_metro_ligne-14.svg",
    ],
    "trains" => [
        "Ligne A" => $baseUrlLogo."/rer/picto_rer_ligne-a.svg",
        "Ligne B" => $baseUrlLogo."/rer/picto_rer_ligne-b.svg",
        "Ligne C" => $baseUrlLogo."/rer/picto_rer_ligne-c.svg",
        "Ligne D" => $baseUrlLogo."/rer/picto_rer_ligne-d.svg",
        "Ligne E" => $baseUrlLogo."/rer/picto_rer_ligne-e.svg",
        "Ligne H" => $baseUrlLogo."/sncf/picto_sncf_ligne-h.svg",
        "Ligne J" => $baseUrlLogo."/sncf/picto_sncf_ligne-j.svg",
        "Ligne K" => $baseUrlLogo."/sncf/picto_sncf_ligne-k.svg",
        "Ligne L" => $baseUrlLogo."/sncf/picto_sncf_ligne-l.svg",
        "Ligne N" => $baseUrlLogo."/sncf/picto_sncf_ligne-n.svg",
        "Ligne P" => $baseUrlLogo."/sncf/picto_sncf_ligne-p.svg",
        "Ligne R" => $baseUrlLogo."/sncf/picto_sncf_ligne-r.svg",
        "Ligne U" => $baseUrlLogo."/sncf/picto_sncf_ligne-u.svg",
    ],
    "tramways" => [
        "Tramway T1" => $baseUrlLogo."/tram/picto_tram_ligne-t1.svg",
        "Tramway T2" => $baseUrlLogo."/tram/picto_tram_ligne-t2.svg",
        "Tramway T3a" => $baseUrlLogo."/tram/picto_tram_ligne-t3a.svg",
        "Tramway T3b" => $baseUrlLogo."/tram/picto_tram_ligne-t3b.svg",
        "Tramway T4" => $baseUrlLogo."/tram/picto_tram_ligne-t4.svg",
        "Tramway T5" => $baseUrlLogo."/tram/picto_tram_ligne-t5.svg",
        "Tramway T6" => $baseUrlLogo."/tram/picto_tram_ligne-t6.svg",
        "Tramway T7" => $baseUrlLogo."/tram/picto_tram_ligne-t7.svg",
        "Tramway T8" => $baseUrlLogo."/tram/picto_tram_ligne-t8.svg",
        "Tramway T9" => $baseUrlLogo."/tram/picto_tram_ligne-t9.svg",
        "Tramway T10" => $baseUrlLogo."/tram/picto_tram_ligne-t10.svg",
        "Tramway T11" => $baseUrlLogo."/tram/picto_tram_ligne-t11.svg",
        "Tramway T12" => $baseUrlLogo."/tram/picto_tram_ligne-t12.svg",
        "Tramway T13" => $baseUrlLogo."/tram/picto_tram_ligne-t13.svg",
    ]
]
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>

<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, target-densitydpi=device-dpi">

<title>Statut du trafic RATP / SNCF des <?php echo $modesLibelle[$mode] ?> du <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?></title>
<style>
    body {
        margin:0; padding: 0;
    }
    #container {
        width: 1428px; margin: 0 auto;
    }
    #header {
        position:sticky; top: 0; margin: 0; padding-top: 5px; background: white; z-index: 102;
    }
    #header h1 {
        display: inline-block; white-space: nowrap; margin-top: 17px; top: 0;position: fixed; left: 50%; transform: translate(-50%,-50%) !important; font-weight: normal; text-align: center; color: grey; font-size: 14px; font-family: monospace; text-transform: uppercase;
    }
    #header h1 a{
        text-decoration: none; color: grey;
    }
    #header #nav_mode {
        display: inline-block;
        top: 43px;
        position: fixed;
        left: 50%; transform: translate(-50%,-50%) !important;
        width: 100%;
        text-align: center;
    }
    #header #nav_mode a {
        font-size: 14px;
        text-transform: uppercase;
        color: grey;
        font-family: monospace;
        text-decoration: none;
    }
    #header #nav_mode a:not(:first-child):not(:last-child) {
        margin-right: 30px;
        margin-left: 30px;
    }
    #header a#lien_refresh {
        position:fixed; right: 10px; top: 10px; display: inline-block; color: grey; text-decoration: none; font-family: monospace; font-size: 14px; font-weight: bold;
    }
    .ligne {
        margin-bottom: 5px;
    }
    .ligne:after {
        content:" ";
        display:block;
        clear:both;
    }
    .logo {
        display: block; position:sticky; left:0; background-color: white; z-index: 101; padding-left: 5px; background-color: rgba(255, 255, 255, .5);
        float:left;
    }
    .logo img {
        width: 30px;
        margin-right: 5px;
    }
    .i:hover {
        background-color: #000 !important;
    }
    .i {
        display: block;
        float:left;
        height: 30px;
        width: 2px;
        opacity: 0.85;
        background-color: #e2e2e2;
    }
    .i10m {
        border-left: 1px solid #def2ca;
    }
    .i1h {
        border-left: 1px solid #fff;
    }
    .hline {
        margin-top: 60px; padding-left: 40px;
        margin-bottom: 4px;
    }
    .hline:after {
        content:" ";
        display:block;
        clear:both;
    }
    .ih {
        float:left;
        height: 14px;
        color: grey;
        width: 66px;
        position:relative;
        margin-bottom: 5px;
    }
    .ih:last-child {
        width: 0;
    }
    .ih small {
        position: absolute;left: -8px; top: 0; font-size: 11px; font-family: monospace;
    }
    .bloque {
        background-color: #f32626;
    }
    .perturbe {
        background-color: orange;
    }
    .ok {
        background-color: #b6df8c;
    }
    .ligne > .ok+.e {
        background-color: #65b613 !important;
    }
    .ligne > .bloque+.e {
        background-color: #a00 !important;
    }
    .ligne > .perturbe+.e {
        background-color: #d47006 !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if(document.querySelector('.ligne .e')) {
            window.scrollTo({ left: document.querySelector('.ligne .e').offsetLeft - window.innerWidth + 66 });
        }
    })
</script>
</head>
<body>
<div id="container">
<div id="header">
<a id="lien_refresh" href="" onclick="location.reload(); return false;">⭮</a>
<h1><a href="/<?php echo date_format((new DateTime($dateStart))->modify('-1 day'), "Ymd"); ?>/<?php echo $mode ?>.html"><</a> Statut du trafic du <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?> <a href="/<?php echo date_format((new DateTime($dateStart))->modify('+1 day'), "Ymd"); ?>/<?php echo $mode ?>.html">></a></h1>
<div id="nav_mode"><?php foreach($lignes as $m => $ligne): ?><a style="<?php if($mode == $m): ?>font-weight: bold;<?php endif; ?>" href="/<?php echo (new DateTime($dateStart))->format('Ymd') ?>/<?php echo $m ?>.html"><?php echo $modesLibelle[$m] ?></a><?php endforeach; ?></div>
<div class="hline"><?php for($i = 0; $i <= 1260; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</div>
<div id="lignes">
<?php foreach($lignes[$mode] as $ligne => $logo): ?>
<div class="ligne"><div class="logo"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" /></div>
<?php for($i = 0; $i < 1260; $i = $i + 2): ?><a class="i <?php echo get_color_class($i, $disruptions, $ligne) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?>" title="<?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?> - <?php echo get_infos($i, $disruptions, $ligne) ?>"></a>
<?php endfor; ?></div>
<?php endforeach; ?>
</div>
</div>
</body>
</html>

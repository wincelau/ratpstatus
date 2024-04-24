<?php

date_default_timezone_set('Europe/Paris');
if(isset($argv[1]) && $argv[1]) {
    $_GET['date'] = $argv[1];
}
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

      if(!preg_match('/Métro /', $disruption->title)) {
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

function get_color_class($nbMinutes, $disruptions, $metro) {
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
        if(!preg_match('/Métro '.$metro.'[^0-9]+/', $disruption->title)) {
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

function get_infos($nbMinutes, $disruptions, $metro) {
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
      if(!preg_match('/Métro '.$metro.'[^0-9]+/', $disruption->title)) {
          continue;
      }

      foreach($disruption->applicationPeriods as $period) {
          if($dateCurrent >= $period->begin && $dateCurrent <= $period->end && $disruption->cause == "PERTURBATION") {

            $message .= $disruption->title."\n\n".$disruption->message."\n\n".$disruption->id." - ".$disruption->severity."\n\n-----\n\n";
          }
      }
    }

    if($message) {
        return $message;
    }

    return "OK";
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>

<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, target-densitydpi=device-dpi">

<title>Statut du trafic RATP du <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?></title>
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
        width: 1px;
        opacity: 0.85;
        background-color: #e2e2e2;
    }
    .i10m {
        border-right: 1px solid #def2ca;
    }
    .i1h {
        border-right: 1px solid #fff;
    }
    .hline {
        margin-top: 30px; padding-left: 40px;
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
            window.scrollTo({ left: document.querySelector('.ligne .e').offsetLeft });
        }
    })
</script>
</head>
<body>
<div id="container">
<div id="header">
<h1><a href="<?php echo date_format((new DateTime($dateStart))->modify('-1 day'), "Ymd"); ?>.html"><</a> Statut du trafic RATP du <?php echo date_format(new DateTime($dateStart), "d/m/Y"); ?> <a href="<?php echo date_format((new DateTime($dateStart))->modify('+1 day'), "Ymd"); ?>.html">></a></h1>
<div class="hline"><?php for($i = 0; $i <= 1260; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</div>
<div id="lignes">
<?php for($j = 1; $j <= 14; $j++): ?>
<div class="ligne"><div class="logo"><img src="https://www.ratp.fr/sites/default/files/lines-assets/picto/metro/picto_metro_ligne-<?php echo $j; ?>.svg" /></div>
<?php for($i = 1; $i <= 1260; $i++): ?><a class="i <?php echo get_color_class($i, $disruptions, $j) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?>" title="<?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?> - <?php echo get_infos($i, $disruptions, $j) ?>"></a>
<?php endfor; ?></div>
<?php endfor; ?>
</div>
</div>
</body>
</html>

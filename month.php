<?php
require __DIR__.'/app/Config.php';

$handle = fopen(__DIR__.'/datas/export/historique_statuts.csv', "r");

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

$statuts = [];
while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    if(strpos($data[0], 'date') === 0) {
        continue;
    }
    if(strpos(str_replace("-", "", $data[0]), $_GET['date']) !== 0) {
        continue;
    }
    $dateStart = new DateTime($data[3]);
    $dateEnd = new DateTime($data[4]);
    $duration = $dateEnd->diff($dateStart);
    if(!isset($statuts[$data[2]][$data[0]]["minutes"][$data[5]])) {
        $statuts[$data[2]][$data[0]]["minutes"][$data[5]] = 0;
    }

    $statuts[$data[2]][$data[0]]["minutes"][$data[5]] += ($duration->d * 24 * 60) + ($duration->h * 60) + $duration->i;
}
foreach($statuts as $ligne => $dates) {
    $dispoOK = 0;
    $nbDates = 0;
    foreach($dates as $date => $data) {
        $total = array_sum($data["minutes"]);
        $pourcentages = array_map(function($a) use ($total) { return $total > 0 ? round($a / $total * 100) : 0; }, $data["minutes"]);
        if(!isset($pourcentages["OK"])) {
            $pourcentages["OK"] = 0;
        }
        if(!isset($pourcentages["PB"])) {
            $pourcentages["PB"] = 0;
        }
        if(!isset($pourcentages["TX"])) {
            $pourcentages["TX"] = 0;
        }
        if(!isset($pourcentages["BQ"])) {
            $pourcentages["BQ"] = 0;
        }
        $pourcentages["OK"] = round(100 - $pourcentages["PB"] - $pourcentages["BQ"] - $pourcentages["TX"], 2);
        $statuts[$ligne][$date]["pourcentages"] = $pourcentages;
        $dispoOK+=$pourcentages["OK"];
        $nbDates++;
    }
    $statuts[$ligne]["total"] = round($dispoOK / $nbDates);
}
fclose($handle);

$GLOBALS['isStaticResponse'] = isset($_SERVER['argv']) && !is_null($_SERVER['argv']);
function url($url) {
    if($GLOBALS['isStaticResponse']) {

        return $url;
    }

    preg_match('|/?([^/]*)/([^/]*).html|', $url, $matches);

    $script = "index.php";

    if(strlen($matches[1]) == "6") {
        $script = "month.php";
    }

    return $script."?".http_build_query(['date' => $matches[1], 'mode' => $matches[2]]);
}
$dateMonth = DateTime::createFromFormat("Ymd", $_GET['date'].'01');
$datePreviousMonth = (clone $dateMonth)->modify('-1 month');
$dateNextMonth = (clone $dateMonth)->modify('+1 month');
$date = DateTime::createFromFormat("Ymd", $_GET['date'].'01');
$dates = [];
$nbDays = cal_days_in_month(CAL_GREGORIAN, $date->format('n'), $date->format('Y'));
for($i = 0; $i < $nbDays; $i++) {
    $dates[] = clone $date;
    $date->modify('+1 day');
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0">
<title>Suivi de l'état du trafic - RATP Status</title>
<meta name="description" content="Page de suivi et d'historisation de l'état du trafic et des incidents des Métros, RER / Transiliens et Tramways d'Île de France">
<link rel="icon" href="/images/favicon_<?php echo $mode ?>.ico" />
<link rel="icon" type="image/png" sizes="192x192" href="/images/favicon_<?php echo $mode ?>.png" />
<link rel="stylesheet" href="/css/style.css?<?php echo filemtime(__DIR__.'/css/style.css') ?>">
<script src="/js/main.js?<?php echo filemtime(__DIR__.'/js/main.js') ?>"></script>
</head>
<body>
<div id="container_month">
<header role="banner" id="header">
<nav id="nav_liens">
<a id="btn_help" href="#aide" title="Aide et informations">ℹ️<i class="mobile_hidden"> </i><span class="mobile_hidden">Aide et Infos</span></a>
</nav>
<nav id="nav_liens_right">
</nav>
<h1><span class="mobile_hidden">Suivi de l'état du trafic<span> des transports IDF</span></span><span class="mobile_visible">État du trafic</span></h1>
<h2><a title="Voir le mois précédent" href="<?php echo url("/".$datePreviousMonth->format('Ym')."/".$mode.".html") ?>">⬅️<span class="visually-hidden">Voir le mois précédent</span></a>&nbsp;&nbsp;<?php echo $dateMonth->format('M Y') ?>&nbsp;&nbsp;<a title="Voir le jour suivant" href="<?php echo url("/".$dateNextMonth->format('Ym')."/".$mode.".html") ?>">➡️<span class="visually-hidden">Voir le jour suivant</span></a><select style="position: absolute; margin-left: 10px; background: #fff; border: none; cursor: pointer;" onchange="document.location.href=this.value" autocomplete="off">
    <option value="<?php echo url("/".$dateMonth->format('Ym')."01/".$mode.".html") ?>">VUE JOUR</option>
    <option value="" selected>VUE MOIS</option>
</select></h2>
<nav id="nav_mode"><?php foreach(Config::getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo url("/".$dateMonth->format('Ym')."/".$m.".html") ?>"><?php echo Config::getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php foreach($dates as $date): ?><div class="ih <?php if($date->format('N') == 7): ?>ihew<?php endif; ?>"><small><span><?php if($date->format('N') ==  1): ?>Lun<?php elseif($date->format('N') ==  3): ?>Mer<?php elseif($date->format('N') ==  5): ?>Ven<?php elseif($date->format('N') ==  7): ?>Dim<?php endif; ?></span><?php echo $date->format('j') ?></small></div><?php endforeach; ?></div>
</header>
<main role="main">
<div id="lignes">
<?php foreach(Config::getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne" data-id="<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><div class="logo"><a href="#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" width="30" height="30" /></a></div>
<?php $j=1; ?>
<?php foreach($dates as $date): ?>
<?php if($date == "total"): continue; endif; ?>
<?php $data = (isset($statuts[$ligne][$date->format('Y-m-d')])) ? $statuts[$ligne][$date->format('Y-m-d')] : null; ?>
<a class="bm <?php if($date->format('N') ==  7): ?>bmew<?php endif; ?>" href="<?php echo url("/".$date->format('Ymd')."/".$mode.".html") ?>#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>" title="<?php echo $date->format('d/m/Y'); ?>">
<?php $rest = 0; ?>
<?php if(!$data): ?><div class="no"></div><?php endif; ?>
<?php if($data): ?>
<?php foreach(["OK", "TX", "PB", "BQ"] as $statut): ?>
<?php if($rest > 0 && $data["pourcentages"][$statut] > $rest): ?>
<div class="<?php echo strtolower($statut) ?> bml" style="width: <?php echo $rest * 4 ?>px;"></div>
<?php $data["pourcentages"][$statut] = $data["pourcentages"][$statut] - $rest; $rest = 0; ?>
<?php endif; ?>
<?php if(intdiv($data["pourcentages"][$statut], 10) > 0): ?>
<div class="<?php echo strtolower($statut) ?> bmb" style="height: <?php echo 4*(intdiv($data["pourcentages"][$statut], 10)) ?>px;"></div>
<?php endif; ?>
<?php if($data["pourcentages"][$statut] % 10 > 0): ?>
<div class="<?php echo strtolower($statut) ?> bml" style="width: <?php echo ($data["pourcentages"][$statut] % 10) * 4 ?>px;"></div>
<?php endif; ?>
<?php if($rest > 0): ?>
<?php $rest = $rest - ($data["pourcentages"][$statut] % 10); ?>
<?php else: ?>
<?php $rest = 10 - ($data["pourcentages"][$statut] % 10); ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
</a>
<?php $j++; ?>
<?php endforeach; ?>
<span class="dispoligne" title="Aucune perturbation pour <?php echo $statuts[$ligne]["total"] ?>% du trafic de toute la journée"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" /><?php echo str_replace(" ", "&nbsp;", sprintf("% 3d", $statuts[$ligne]["total"])) ?>%</span></div>
<?php endforeach; ?>
</div>
</main>
</div>
<div id="legende">
<p><span class="ok"></span> % Rien à signaler <span class="pb" style="margin-left: 20px;"></span> % Perturbation <span class="bq" style="margin-left: 20px;"></span> % Blocage / Interruption <span class="tx" style="margin-left: 20px;"></span> % Travaux <span class="no" style="margin-left: 20px;"></span> Aucune donnée</p>
<p></p>
</div>
<footer role="contentinfo" id="footer">
<p>
    <a href="">RATPStatus.fr</a> est publié sous licence libre AGPL-3.0 (<a href="https://github.com/wincelau/ratpstatus">voir les sources</a>), ce n'est pas un site officiel de la <a href="https://www.ratp.fr/">RATP</a>.
</p>
</footer>
<dialog id="modalHelp">
    <?php include(__DIR__.'/templates/_help.php') ?>
</dialog>
</body>
</html>

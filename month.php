<?php
require __DIR__.'/app/Config.php';
require __DIR__.'/app/View.php';

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
    if($data[1] != $mode) {
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
    if(!isset($statuts[$data[2]]["total"]["minutes"][$data[5]])) {
        $statuts[$data[2]]["total"]["minutes"][$data[5]] = 0;
    }
    if(!isset($statuts["total"][$data[0]]["minutes"][$data[5]])) {
        $statuts["total"][$data[0]]["minutes"][$data[5]] = 0;
    }
    if(!isset($statuts["total"]["total"]["minutes"][$data[5]])) {
        $statuts["total"]["total"]["minutes"][$data[5]] = 0;
    }

    $nbMinutes = ($duration->d * 24 * 60) + ($duration->h * 60) + $duration->i;
    $statuts[$data[2]][$data[0]]["minutes"][$data[5]] += $nbMinutes;
    $statuts[$data[2]]["total"]["minutes"][$data[5]] += $nbMinutes;
    $statuts["total"][$data[0]]["minutes"][$data[5]] += $nbMinutes;
    $statuts["total"]["total"]["minutes"][$data[5]] += $nbMinutes;
}
foreach($statuts as $ligne => $dates) {
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
    }
}
fclose($handle);

$GLOBALS['isStaticResponse'] = isset($_SERVER['argv']) && !is_null($_SERVER['argv']);

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

$handle = fopen(__DIR__.'/datas/export/historique_incidents.csv', "r");
$motifs = [];
while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    if(strpos($data[0], 'date') === 0) {
        continue;
    }
    if(strpos(str_replace("-", "", $data[0]), $_GET['date']) !== 0) {
        continue;
    }
    if($data[10] != 0) {
        continue;
    }
    if($mode != $data[1]) {
        continue;
    }

    $motifs["TOTAL"]["TOTAL"]['count']++;
    $motifs["TOTAL"][$data[9]]['count']++;
    $motifs["TOTAL"][$data[9]]['total_duration']+=floatval($data[5]);
    $motifs["TOTAL"][$data[9]]['total_duration_bloquant']+=floatval($data[7]);

    $motifs[$data[2]]["TOTAL"]['count']++;
    $motifs[$data[2]][$data[9]]['count']++;
    $motifs[$data[2]][$data[9]]['total_duration']+=floatval($data[5]);
    $motifs[$data[2]][$data[9]]['total_duration_bloquant']+=floatval($data[7]);
}
fclose($handle);
foreach($motifs as $ligne => $motifsLigne) {
    $motifs[$ligne] = array_map(function($a) {
        $a['total_duration'] = round($a['total_duration']);
        $a['total_duration_bloquant'] = round($a['total_duration_bloquant']);
        $a['average_duration'] = round($a['total_duration'] / $a['count']);
        $a['average_duration_bloquant'] = round($a['total_duration_bloquant'] / $a['count']);
        return $a;}, $motifsLigne);
    uasort($motifs[$ligne], function($a, $b) { return $a['count'] < $b['count']; });
}

uksort($motifs, function($a, $b) use ($mode) {
    if($a == "TOTAL") {
        return false;
    }

    if($b == "TOTAL") {
        return true;
    }

    $indexA = array_search($a, array_keys(Config::getLignes()[$mode]));
    $indexB = array_search($b, array_keys(Config::getLignes()[$mode]));

    return $indexA > $indexB;
});

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
<style>
    .donutG:before {
        content: "<?php echo round($statuts["total"]["total"]["pourcentages"]['OK']) ?>";
    }
    .donutG {
        background: radial-gradient(white 45%, transparent 41%), conic-gradient(#c0e39d 0% <?php echo $statuts["total"]["total"]["pourcentages"]['OK'] ?>%, #ffb225 <?php echo $statuts["total"]["total"]["pourcentages"]['OK'] ?>% <?php echo $statuts["total"]["total"]["pourcentages"]['OK'] + $statuts["total"]["total"]["pourcentages"]['PB'] ?>%, #f44646 <?php echo $statuts["total"]["total"]["pourcentages"]['OK'] + $statuts["total"]["total"]["pourcentages"]['PB'] ?>% <?php echo $statuts["total"]["total"]["pourcentages"]['OK'] + $statuts["total"]["total"]["pourcentages"]['PB'] + $statuts["total"]["total"]["pourcentages"]['BQ'] ?>%, #aeaeae <?php echo $statuts["total"]["total"]["pourcentages"]['OK'] + $statuts["total"]["total"]["pourcentages"]['PB'] + $statuts["total"]["total"]["pourcentages"]['BQ'] ?>% 100%);
    }
</style>
</head>
<body>
<div id="container_month">
<header role="banner" id="header">
<nav id="nav_liens">
<a id="btn_help" href="#aide" title="Aide et informations">ℹ️<i class="mobile_hidden"> </i><span class="mobile_hidden">Aide et Infos</span></a>
</nav>
<nav id="nav_liens_right">
<a id="btn_list" class="badge openincident" href="#incidents" title="Voir la liste des incidents de la journée"><span title="Aucune perturbation pour <?php echo $statuts["total"]["total"]["pourcentages"]["OK"] ?>% du trafic de tout la journée" class="donutG"></span><span class="picto">📅</span><span class="text_incidents"><?php echo $motifs["TOTAL"]["TOTAL"]['count'] ?><span class="long"> incidents</span><span class="short">inc.</span></span></a>
</nav>
<h1><span class="mobile_hidden">Suivi de l'état du trafic<span> des transports IDF</span></span><span class="mobile_visible">État du trafic</span></h1>
<h2><a title="Voir le mois précédent" href="<?php echo View::url("/".$datePreviousMonth->format('Ym')."/".$mode.".html") ?>">⬅️<span class="visually-hidden">Voir le mois précédent</span></a>
    <select id="select-day" style="<?php if($dateMonth->format('Ym') == date('Ym')):?>font-weight: bold;<?php endif;?>" onchange="document.location.href=this.value; this.value='';" autocomplete="off">
        <option style="display: none;" value="" selected="selected"><?php echo View::displayDateMonthToFr($dateMonth, 4); ?></option>
        <?php foreach(View::getDatesChoices() as $dateChoiceKey => $dateChoiceLibelle): ?>
        <option value="<?php echo View::url("/".$dateChoiceKey."/".$mode.".html") ?>"><?php echo $dateChoiceLibelle ?></option>
        <?php endforeach; ?>
    </select>
<?php if($dateMonth->format('Ym') >= date('Ym')):?>
<a class="disabled">➡️</a>
<?php else: ?>
<a title="Voir le jour suivant" href="<?php echo View::url("/".$dateNextMonth->format('Ym')."/".$mode.".html") ?>">➡️<span class="visually-hidden">Voir le jour suivant</span></a><?php
endif; ?></h2>
<nav id="nav_mode"><?php foreach(Config::getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo View::url("/".$dateMonth->format('Ym')."/".$m.".html") ?>"><?php echo Config::getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php foreach($dates as $date): ?><div class="ih <?php if($date->format('N') == 7): ?>ihew<?php endif; ?>"><small><span><?php if($date->format('N') ==  1): ?>Lun<?php elseif($date->format('N') ==  3): ?>Mer<?php elseif($date->format('N') ==  5): ?>Ven<?php elseif($date->format('N') ==  7): ?>Dim<?php endif; ?></span><?php echo sprintf("%02d", $date->format('j')) ?></small></div><?php endforeach; ?></div>
</header>
<main role="main">
<div id="lignes">
<?php foreach(Config::getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne" data-id="<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><div class="logo"><a href="#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" width="30" height="30" /></a></div>
<?php $j=1; ?>
<?php foreach($dates as $date): ?>
<?php if($date == "total"): continue; endif; ?>
<?php $data = (isset($statuts[$ligne][$date->format('Y-m-d')])) ? $statuts[$ligne][$date->format('Y-m-d')] : null; ?>
<a class="bm <?php if($date->format('N') ==  7): ?>bmew<?php endif; ?>" href="<?php echo View::url("/".$date->format('Ymd')."/".$mode.".html") ?>#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>" title="<?php echo $date->format('d/m/Y'); ?>">
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
<span class="dispoligne" title="Aucune perturbation pour <?php echo $statuts[$ligne]["total"]["pourcentages"]["OK"] ?>% du trafic de toute la journée"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" /><?php echo str_replace(" ", "&nbsp;", sprintf("% 3d", $statuts[$ligne]["total"]["pourcentages"]["OK"])) ?>%</span></div>
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
<dialog id="listModal">
    <h2><span id="listModal_title_all"><?php echo Config::getModeLibelles()[$mode] ?></span> - Incidents du mois de <?php echo View::displayDateMonthToFr($dateMonth); ?></h2>

    <div id="tabLigneContainer">
    <div id="tabLigne">
        <a class="active" href="#incidents">Tous</a>
    <?php foreach(Config::getLignes()[$mode] as $ligne => $img): ?>
        <a href="#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><img height="24" src="<?php echo $img ?>" alt="<?php echo $ligne ?>" /><div class="barre"></div></a>
    <?php endforeach; ?>
    </div>
    </div>
    <?php foreach($motifs as $ligne => $motifsLigne): ?>
    <div id="liste_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>" style="<?php if($ligne != "TOTAL"): ?>display: none;<?php endif; ?>" class="liste_ligne">
    <?php if($ligne != "TOTAL"): ?>
    <h3 style="margin-bottom: 0;"><?php if(isset(Config::getLignes()[$mode][$ligne])): ?><img height="20" src="<?php echo Config::getLignes()[$mode][$ligne] ?>" alt="<?php echo $ligne ?>" /> <?php endif; ?><?php echo $ligne; ?></h3>
    <?php endif; ?>
    <table style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="text-align: left;">Motif</th>
                <th style="text-align: center;">Nombre</th>
                <th style="text-align: center;" colspan="2">Durée Moyenne</th>
                <th style="text-align: center;" colspan="2">Durée Totale</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach($motifsLigne as $motif => $stats): ?>
        <?php if($motif == "TOTAL"): continue; endif; ?>
        <tr>
            <td><?php if($motif): ?><?php echo $motif; ?><?php else: ?><em style="color: #444;">Aucun motif détécté</em><?php endif; ?></td>
            <td class="num-right"><?php echo $stats['count']; ?></td>
            <td class="num-right"><?php echo intdiv($stats['average_duration'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration'] % 60); ?></td>
            <td class="num-left"><?php if($stats['average_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['average_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration_bloquant'] % 60); ?> en moyenne de blocage ou d'interruption"><i class="bq"></i><?php echo intdiv($stats['average_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration_bloquant'] % 60); ?></small><?php endif; ?></td>
            <td class="num-right"><?php echo intdiv($stats['total_duration'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration'] % 60); ?></td>
            <td class="num-left"><?php if($stats['total_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['total_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration_bloquant'] % 60); ?> de blocage ou d'interruption"><i class="bq"></i><?php echo intdiv($stats['total_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration_bloquant'] % 60); ?></small><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endforeach; ?>
</dialog>
</body>
</html>

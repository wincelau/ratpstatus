<?php
require __DIR__.'/app/Config.php';
require __DIR__.'/app/Period.php';
require __DIR__.'/app/MonthPeriod.php';
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


$GLOBALS['isStaticResponse'] = isset($_SERVER['argv']) && !is_null($_SERVER['argv']);

$period = new MonthPeriod($_GET['date']);
$statuts = $period->getStatuts($mode);

$datePreviousMonth = (clone $period->getDateStart())->modify('-1 month');
$dateNextMonth = (clone $period->getDateStart())->modify('+1 month');

$nbDays = cal_days_in_month(CAL_GREGORIAN, $period->getDateStart()->format('n'), $period->getDateStart()->format('Y'));
$date = clone $period->getDateStart();
$dates = [];
for($i = 0; $i < $nbDays; $i++) {
    $dates[] = clone $date;
    $date->modify('+1 day');
}
$motifs = $period->getMotifs($mode);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
<title>Suivi de l'état du trafic - RATP Status</title>
<?php include(__DIR__.'/templates/_header.php') ?>
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
<?php include(__DIR__.'/templates/_nav.php') ?>
</nav>
<nav id="nav_liens_right">
<a id="btn_list" class="badge openincident" href="#incidents" title="Voir la liste des incidents de la journée"><span title="Aucune perturbation pour <?php echo $statuts["total"]["total"]["pourcentages"]["OK"] ?>% du trafic de tout la journée" class="donutG"></span><span class="picto">📅</span><span class="text_incidents"><?php echo $motifs["TOTAL"]["TOTAL"]['count'] ?><span class="long"> incidents</span><span class="short">inc.</span></span></a>
</nav>
<?php include(__DIR__.'/templates/_navDate.php') ?>
<nav id="nav_mode"><?php foreach(Config::getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo View::url("/".$period->getDateStart()->format('Ym')."/".$m.".html") ?>"><?php echo Config::getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
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
    <?php include(__DIR__.'/templates/_footer.php') ?>
</footer>
<dialog id="modalHelp">
    <?php include(__DIR__.'/templates/_help.php') ?>
</dialog>
<dialog id="listModal">
    <h2><span id="listModal_title_all"><?php echo Config::getModeLibelles()[$mode] ?></span> - Incidents du mois de <?php echo View::displayDateMonthToFr($period->getDateStart()); ?></h2>
    <?php include(__DIR__.'/templates/_navLignes.php') ?>
    <?php foreach($motifs as $ligne => $motifsLigne): ?>
    <div id="liste_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>" style="<?php if($ligne != "TOTAL"): ?>display: none;<?php endif; ?>" class="liste_ligne">
    <table>
        <thead>
            <tr>
                <th style="text-align: left;">Motif</th>
                <th style="text-align: center;">Nombre</th>
                <th style="text-align: center;">Durée Moyenne</th>
                <th style="text-align: center; font-weight: normal;"><small>dont bloquant</small></th>
                <th style="text-align: center;">Durée Totale</th>
                <th style="text-align: center; font-weight: normal;"><small>dont bloquant</small></th>
            </tr>
        </thead>
        <tbody>
    <?php foreach($motifsLigne as $motif => $stats): ?>
        <?php if($motif == "TOTAL"): continue; endif; ?>
        <tr>
            <td><?php if($motif): ?><?php echo $motif; ?><?php else: ?><em style="color: #444;">Aucun motif détécté</em><?php endif; ?></td>
            <td class="num-right"><?php echo $stats['count']; ?></td>
            <td class="num-right"><?php echo intdiv($stats['average_duration'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration'] % 60); ?></td>
            <td class="num-left"><?php if($stats['average_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['average_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration_bloquant'] % 60); ?> en moyenne de blocage ou d'interruption"><?php echo intdiv($stats['average_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration_bloquant'] % 60); ?><i class="bq"></i></small><?php endif; ?></td>
            <td class="num-right"><?php echo intdiv($stats['total_duration'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration'] % 60); ?></td>
            <td class="num-left"><?php if($stats['total_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['total_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration_bloquant'] % 60); ?> de blocage ou d'interruption"><?php echo intdiv($stats['total_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration_bloquant'] % 60); ?><i class="bq"></i></small><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endforeach; ?>
</dialog>
</body>
</html>

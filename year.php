<?php
require __DIR__.'/app/Config.php';
require __DIR__.'/app/Period.php';
require __DIR__.'/app/YearPeriod.php';
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

$period = new YearPeriod($_GET['date']);
$statuts = $period->getStatuts($mode);

$nbMonths = 12;
$date = clone $period->getDateStart();
$dates = [];
for($i = 0; $i < $nbMonths; $i++) {
    $dates[] = clone $date;
    $date->modify('+1 month');
}
$motifs = $period->getMotifs($mode);
$wblock = 7;
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
<div id="container_month" class="container_year">
<header role="banner" id="header">
<nav id="nav_liens">
<?php include(__DIR__.'/templates/_nav.php') ?>
</nav>
<nav id="nav_liens_right">
<a id="btn_list" class="badge openincident" href="#incidents" title="Voir la liste des incidents de la journée"><span title="Aucune perturbation pour <?php echo $statuts["total"]["total"]["pourcentages"]["OK"] ?>% du trafic de tout la journée" class="donutG"></span><span class="picto">📅</span><span class="text_incidents"><?php echo $motifs["TOTAL"]["TOTAL"]['count'] ?><span class="long"> incidents</span><span class="short">inc.</span></span></a>
</nav>
<?php include(__DIR__.'/templates/_navDate.php') ?>
<nav id="nav_mode"><?php foreach(Config::getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo View::url("/".$period->getDateStartKey()."/".$m.".html") ?>"><?php echo Config::getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php $isFirst = true; foreach($dates as $date): ?><div class="ih <?php if($date->format('m') == 12): ?>ihew<?php endif; ?>"><small><span><?php if($date->format('m') == 1 || $isFirst): ?><?php echo $date->format('Y') ?><?php endif; ?></span><?php echo View::displayDateMonthToFr($date, true) ?></small></div><?php $isFirst = false; endforeach; ?></div>
</header>
<main role="main">
<div id="lignes">
<?php foreach(Config::getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne" data-id="<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><div class="logo"><a href="#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" width="30" height="30" /></a></div>
<?php $j=1; ?>
<?php foreach($dates as $date): ?>
<?php if($date == "total"): continue; endif; ?>
<?php $data = (isset($statuts[$ligne][$date->format('Y-m')])) ? $statuts[$ligne][$date->format('Y-m')] : null; ?>
<a class="bm <?php if($date->format('N') ==  12): ?>bmew<?php endif; ?>" href="<?php echo View::url("/".$date->format('Ym')."/".$mode.".html") ?>" title="Voir en <?php echo strtolower(View::displayDateMonthToFr($date)) ?> <?php echo $date->format('Y'); ?>">
<?php $rest = 0; ?>
<?php if(!$data): ?><div class="no"></div><?php endif; ?>
<?php if($data): ?>
<?php foreach(["OK", "TX", "PB", "BQ"] as $statut): ?>
<?php if($rest > 0 && $data["pourcentages"][$statut] > $rest): ?>
<div class="<?php echo strtolower($statut) ?> bml" style="width: <?php echo $rest * $wblock ?>px;"></div>
<?php $data["pourcentages"][$statut] = $data["pourcentages"][$statut] - $rest; $rest = 0; ?>
<?php endif; ?>
<?php if(intdiv($data["pourcentages"][$statut], 10) > 0): ?>
<div class="<?php echo strtolower($statut) ?> bmb" style="height: <?php echo 4 * (intdiv($data["pourcentages"][$statut], 10)) ?>px;"></div>
<?php endif; ?>
<?php if($data["pourcentages"][$statut] % 10 > 0): ?>
<div class="<?php echo strtolower($statut) ?> bml" style="width: <?php echo ($data["pourcentages"][$statut] % 10) * $wblock ?>px;"></div>
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
    <div class="modalHeader">
    <span class="modalClose">🞪</span>
    <h2><span id="listModal_title_all"><?php echo Config::getModeLibelles()[$mode] ?></span> - <?php echo $period->getTitle(); ?></h2>
    <?php include(__DIR__.'/templates/_navLignes.php') ?>
    </div>
    <?php foreach($motifs as $ligne => $motifsLigne): ?>
    <div id="liste_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>" style="<?php if($ligne != "TOTAL"): ?>display: none;<?php endif; ?>" class="liste_ligne">
        <?php include(__DIR__.'/templates/_motifs.php') ?>
    </div>
    <?php endforeach; ?>
</dialog>
</body>
</html>

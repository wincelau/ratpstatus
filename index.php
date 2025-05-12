<?php require __DIR__.'/day.php'; ?>
<?php $statuts = $period->getStatuts($mode); ?>
<?php $motifs = $period->getMotifs($mode); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
<title><?php echo preg_replace("/^[^ ]+ /", "", strip_tags(Config::getModeLibelles()[$mode])) ?><?php if(!$day->isToday()): ?> le <?php echo $day->getDateStart()->format("d/m/Y"); ?><?php endif; ?> - Suivi de l'état du trafic - RATP Status</title>
<?php include(__DIR__.'/templates/_header.php') ?>
<script>
    const urlJson = '/<?php echo ($GLOBALS['isStaticResponse']) ? $day->getDateStart()->format('Ymd').".json" : "json.php?".http_build_query(['date' => $day->getDateStart()->format('Y-m-d')]) ?>';
</script>
<style>
    .donutG:before {
        content: "<?php echo round($pourcentages[$mode]['OK']) ?>";
    }
    .donutG {
        background: radial-gradient(white 45%, transparent 41%), conic-gradient(#c0e39d 0% <?php echo $pourcentages[$mode]['OK'] ?>%, #ffb225 <?php echo $pourcentages[$mode]['OK'] ?>% <?php echo $pourcentages[$mode]['OK'] + $pourcentages[$mode]['PB'] ?>%, #f44646 <?php echo $pourcentages[$mode]['OK'] + $pourcentages[$mode]['PB'] ?>% <?php echo $pourcentages[$mode]['OK'] + $pourcentages[$mode]['PB'] + $pourcentages[$mode]['BQ'] ?>%, #aeaeae <?php echo $pourcentages[$mode]['OK'] + $pourcentages[$mode]['PB'] + $pourcentages[$mode]['BQ'] ?>% 100%);
    }
</style>
</head>
<body class="<?php if($day->isToday()): ?>istoday<?php endif; ?>">
<div id="container">
<header role="banner" id="header">
<nav id="nav_liens">
<?php include(__DIR__.'/templates/_nav.php') ?>
<?php if($day->isToday()): ?>
<a id="lien_refresh" href="" onclick="location.reload(); return false;">🔃</a>
<?php endif; ?>
</nav>
<nav id="nav_liens_right">
    <?php if(count($statutsCount)): ?>
    <a id="btn_list_now" class="badge openincident" href="#incidents" title="Voir la liste des incidents"><span class="picto">🔥</span><?php foreach($statutsCount as $statut => $count): ?><strong><?php echo $count ?></strong><span class="<?php echo $statut ?> barre">&nbsp;</span><?php endforeach ?></a>
    <?php endif; ?>
    <a id="btn_list" class="badge openincident" href="#incidents" title="Voir la liste des incidents de la journée"><span title="Aucune perturbation pour <?php echo $pourcentages[$mode]['OK'] ?>% du trafic de tout la journée" class="donutG"></span><span class="picto">📅</span><span class="text_incidents"><?php echo count($day->getDisruptions($mode)) ?><span class="long"> incidents</span><span class="short">inc.</span></span></a>
</nav>
<?php include(__DIR__.'/templates/_navDate.php') ?>
<nav id="nav_mode"><?php foreach(Config::getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo View::url("/".((!$day->isToday()) ? $period->getDateStartKey()."/" : null).$m.".html") ?>"><?php echo Config::getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php for($i = 0; $i <= 1380; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 4) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</header>
<main role="main">
<div id="lignes">
<?php foreach(Config::getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne" data-id="<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><div class="logo"><a href="#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" width="30" height="30"/></a></div>
<?php for($i = 0; $i < 1380; $i = $i + 2): $isSameForFive = ($i % 10 == 0 && $day->isSameColorClassForFive($i, $ligne)); $info = $day->getInfo($i, $ligne, ($isSameForFive) ? 5 : 1); ?><i class="i <?php echo $day->getColorClass($i, $ligne) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?><?php if($isSameForFive): ?> i5sa<?php endif; ?>" <?php if(!is_null($info) && count($info[0])): ?>data-ids="<?php echo implode(";", $info[0]) ?>" title="<?php /*echo $ligne.' - ' ?><?php echo sprintf("%02d", (intval($i / 60) + 4) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?><?php if($isSameForFive): ?> à <?php echo sprintf("%02d", (intval(($i+(5*2)) / 60) + 4) % 24) ?>h<?php echo sprintf("%02d", (($i+(5*2)) % 60)) ?><?php endif; ?><?php echo "\n\n"; */ ?><?php echo implode("\n", $info[1]) ?>"<?php endif; ?>></i>
<?php if($isSameForFive): $i=$i+(4*2); endif;endfor; ?><span class="dispoligne" title="Aucune perturbation pour <?php echo $pourcentages[$ligne]['OK'] ?>% du trafic de toute la journée"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" /><?php echo str_replace(" ", "&nbsp;", sprintf("% 3d", $pourcentages[$ligne]['OK'])) ?>%</span></div>

<?php endforeach; ?>
</div>
</main>
</div>
<div id="legende">
<p><span class="ok"></span> Rien à signaler <span class="pb" style="margin-left: 20px;"></span> Perturbation <span class="bq" style="margin-left: 20px;"></span> Blocage / Interruption <span class="tx" style="margin-left: 20px;"></span> Travaux <span class="no" style="margin-left: 20px;"></span> Service terminé ou non commencé</p>
<p>
    L'état du trafic est récupéré toutes les 2 minutes depuis le portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a>.<?php if($day->getLastFile()): ?> <br /><br />La dernière récupération pour ce jour date du <a href="https://github.com/wincelau/ratpstatus/blob/main/<?php echo str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $day->getLastFile()->getFilePath()) ?>"><?php echo $day->getLastFile()->getDate()->format('d/m/Y à H:i:s') ?></a><?php endif; ?>
</p>
</div>
<footer role="contentinfo" id="footer">
    <?php include(__DIR__.'/templates/_footer.php') ?>
</footer>
<dialog id="listModal">
<h2><?php echo Config::getModeLibelles()[$mode] ?> - Incidents du <?php echo $day->getDateStart()->format("d/m/Y"); ?></h2>
<?php include(__DIR__.'/templates/_navLignes.php') ?>
<?php $disruptions = array_filter($day->getDisruptions($mode), function($d) { return $d->isInProgress();}) ?>
<?php if($day->isToday()): ?>
<h3 id="title_disruptions_inprogress">En cours <span class="badge hide">0 incidents</span></h3>
<div id="disruptions_inprogress">
<?php foreach($disruptions as $disruption): ?>
<?php include(__DIR__.'/_disruption.php') ?>
<?php endforeach; ?>
<p id="sentence_nothing_disruptions" class="hide">Il n'y a aucun incident</p>
</div>
<?php endif; ?>
<?php $disruptions = array_filter($day->getDisruptions($mode), function($d) { return $d->isPast();}); ?>
<h3 id="title_disruptions_finishes">Terminés <span class="badge hide">0 incidents</span></h3>
<div id="disruptions_finishes">
<?php foreach($disruptions as $disruption): ?>
<?php include(__DIR__.'/_disruption.php') ?>
<?php endforeach; ?>
<p id="sentence_nothing_disruptions_finish" class="hide">Il n'y a aucun incident</p>
</div>
</dialog>
<dialog id="modalHelp">
    <?php include(__DIR__.'/templates/_help.php') ?>
</dialog>
</body>
</html>

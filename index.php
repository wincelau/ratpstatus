<?php require __DIR__.'/day.php'; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, target-densitydpi=device-dpi">
<title><?php echo preg_replace("/^[^ ]+ /", "", strip_tags(Config::getModeLibelles()[$mode])) ?><?php if(!$day->isToday()): ?> le <?php echo $day->getDateStart()->format("d/m/Y"); ?><?php endif; ?> - Suivi de l'état du trafic - RATP Status</title>
<meta name="description" content="Page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France">
<link rel="icon" href="/images/favicon_<?php echo $mode ?>.ico" />
<link rel="icon" type="image/png" sizes="192x192" href="/images/favicon_<?php echo $mode ?>.png" />
<link rel="stylesheet" href="/css/style.css?202411202354">
<script>
    const urlJson = '/<?php echo ($GLOBALS['isStaticResponse']) ? $day->getDateStart()->format('Ymd').".json" : "json.php?".http_build_query(['date' => $day->getDateStart()->format('Y-m-d')]) ?>';
</script>
<script src="/js/main.js?202411202354"></script>
</head>
<body>
<div id="container">
<header role="banner" id="header">
<nav id="nav_liens">
<a id="btn_help" href="#aide" title="Aide et informations">ℹ️<i class="mobile_hidden"> </i><span class="mobile_hidden">Aide et Infos</span></a>
<a id="btn_list" href="#incidents" title="Liste des incidents">📑<i class="mobile_hidden"> </i><span class="mobile_hidden">Liste des incidents</span></a>
</nav>
<a id="lien_refresh" href="" onclick="location.reload(); return false;">🔃</a>
<h1><span class="mobile_hidden">Suivi de l'état du trafic des transports IDF</span><span class="mobile_visible">État du trafic</span></h1>
<h2>
    <?php if($day->getDateStartYesterday() < new DateTime('2024-04-23')): ?>
    <a class="disabled">⬅️</a>
    <?php else: ?>
    <a title="Voir le jour précédent" href="<?php echo url("/".$day->getDateStartYesterday()->format('Ymd')."/".$mode.".html") ?>">
        ⬅️
        <span class="visually-hidden">Voir le jour précédent</span>
    </a>
    <?php endif; ?>
    <span class="<?php if($day->isToday()):?>strong<?php endif;?>"><?php echo $day->getDateStart()->format("d/m/Y"); ?></span>
    <?php if($day->isTomorrow()): ?>
    <a class="disabled">➡️</a>
    <?php else: ?>
    <a title="Voir le jour suivant" style="" href="<?php echo url("/".((!$day->isTodayTomorrow()) ? $day->getDateStartTomorrow()->format('Ymd')."/" : null).$mode.".html") ?>">
        ➡️
        <span class="visually-hidden">Voir le jour suivant</span>
    </a>
    <?php endif; ?>
</h2>
<nav id="nav_mode"><?php foreach(Config::getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo url("/".((!$day->isToday()) ? $day->getDateStart()->format('Ymd')."/" : null).$m.".html") ?>"><?php echo Config::getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php for($i = 0; $i <= 1380; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 4) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</header>
<main role="main">
<div id="lignes">
<?php foreach(Config::getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne" data-id="<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><div class="logo"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>"/></div>
<?php for($i = 0; $i < 1380; $i = $i + 2): ?><a class="i <?php echo $day->getColorClass($i, $ligne) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?>" title="<?php echo sprintf("%02d", (intval($i / 60) + 4) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?><?php echo $day->getInfo($i, $ligne) ?>"></a>
<?php endfor; ?></div>
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
<p>
    <a href="">RATPStatus.fr</a> est publié sous licence libre AGPL-3.0 (<a href="https://github.com/wincelau/ratpstatus">voir les sources</a>), ce n'est pas un site officiel de la <a href="https://www.ratp.fr/">RATP</a>.
</p>
</footer>
<dialog id="listModal">
<h2>Incidents du <?php echo $day->getDateStart()->format("d/m/Y"); ?></h2>
<?php $disruptions = array_filter($day->getDisruptions($mode), function($d) { return $d->isInProgress();}) ?>
<?php if(count($disruptions)): ?>
<h3>En cours</h3>
<?php foreach($disruptions as $disruption): ?>
<?php include(__DIR__.'/_disruption.php') ?>
<?php endforeach; ?>
<?php endif; ?>
<?php $disruptions = array_filter($day->getDisruptions($mode), function($d) { return $d->isInFuture();}); ?>
<?php if(count($disruptions)): ?>
<h3>À venir</h3>
<?php foreach($disruptions as $disruption): ?>
<?php include(__DIR__.'/_disruption.php') ?>
<?php endforeach; ?>
<?php endif; ?>
<?php $disruptions = array_filter($day->getDisruptions($mode), function($d) { return $d->isPast();}); ?>
<?php if(count($disruptions)): ?>
<h3>Terminés</h3>
<?php foreach($disruptions as $disruption): ?>
<?php include(__DIR__.'/_disruption.php') ?>
<?php endforeach; ?>
<?php endif; ?>
</dialog>
<dialog id="helpModal">
    <h2>Aide et informations</h2>
    <p>RATPstatus.fr est une page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France.</p>
    <p>L'état du trafic est récupéré toutes les 2 minutes à partir du 23 avril 2024.</p>
    <p>Chaque bloc répresente une durée de 2 minutes, les couleurs ont la signification suivante :<br /><br />
        <span class="ok"></span> Rien à signaler<br />
        <span class="pb"></span> Perturbation<br />
        <span class="bq"></span> Blocage / Interruption<br />
        <span class="tx"></span> Travaux<br />
        <span class="no"></span> Service terminé ou non commencé
    </p>
    <p>Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a>.</p>
    <?php if($day->getLastFile()): ?>
    <p>La dernière récupération pour ce jour date du <a href="https://github.com/wincelau/ratpstatus/blob/main/<?php echo str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $day->getLastFile()->getFilePath()) ?>"><?php echo $day->getLastFile()->getDate()->format('d/m/Y H:i:s') ?></a>.</p>
    <?php endif; ?>
    <p>Le projet initié par <a href="https://piaille.fr/@winy">winy</a> est publié sous licence libre AGPL-3.0 : <a href="https://github.com/wincelau/ratpstatus">https://github.com/wincelau/ratpstatus</a>.</p>
    <p>Ce site n'est pas un site officiel de la <a href="https://www.ratp.fr/">RATP</a>.</p>
</dialog>
</body>
</html>

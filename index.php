<?php require __DIR__.'/day.php'; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, target-densitydpi=device-dpi">
<title><?php echo strip_tags($day->getModeLibelles()[$mode]) ?> le <?php echo $day->getDateStart()->format("d/m/Y"); ?> - Suivi de l'état du trafic - RATP Status</title>
<meta name="description" content="Page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France">
<link rel="stylesheet" href="/css/style.css?202404300341">
<script>
    const urlJson = '/<?php echo ($GLOBALS['isStaticResponse']) ? $day->getDateStart()->format('Ymd').".json" : "json.php?".http_build_query(['date' => $day->getDateStart()->format('Y-m-d')]) ?>';
</script>
<script src="/js/main.js?202405020029"></script>
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
    <?php if($day->getDateStartYesterday() < new DateTime('2024-04-23')): ?>
    <a class="disabled">⬅️</a>
    <?php else: ?>
    <a title="Voir le jour précédent" href="<?php echo url("/".$day->getDateStartYesterday()->format('Ymd')."/".$mode.".html") ?>">
        <span aria-hidden="true">⬅️</span>
        <span class="visually-hidden">Voir le jour précédent</span>
    </a>
    <?php endif; ?>
    <span class="<?php if($day->isToday()):?>strong<?php endif;?>"><?php echo $day->getDateStart()->format("d/m/Y"); ?></span>
    <?php if($day->getDateStartTomorrow() > (new DateTime())->modify('+2 hour')): ?>
    <a class="disabled">➡️</a>
    <?php else: ?>
    <a title="Voir le jour suivant" style="" href="<?php echo url("/".((!$tomorowIsToday) ? $day->getDateStartTomorrow()->format('Ymd')."/" : null).$mode.".html") ?>">
        <span aria-hidden="true">➡️</span>
        <span class="visually-hidden">Voir le jour suivant</span>
    </a>
    <?php endif; ?>
</h2>
<nav id="nav_mode"><?php foreach($day->getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href="<?php echo url("/".((!$day->isToday()) ? $day->getDateStart()->format('Ymd')."/" : null).$m.".html") ?>"><?php echo $day->getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php for($i = 0; $i <= 1260; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</header>
<main role="main">
<div id="lignes">
<?php foreach($day->getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne"><div class="logo"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>"/></div>
<?php for($i = 0; $i < 1260; $i = $i + 2): ?><a class="i <?php echo $day->getColorClass($i, $ligne) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?>" title="<?php echo sprintf("%02d", (intval($i / 60) + 5) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?><?php echo $day->getInfo($i, $ligne) ?>"></a>
<?php endfor; ?></div>
<?php endforeach; ?>
</div>
</main>
<p id="legende"><span class="ok"></span> Rien à signaler <span class="pb" style="margin-left: 20px;"></span> Perturbation <span class="bq" style="margin-left: 20px;"></span> Blocage / Interruption <span class="tx" style="margin-left: 20px;"></span> Travaux</p>
<footer role="contentinfo" id="footer">
<p>
    Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a> <small>(récupérées toutes le 2 minutes)</small>
</p>
<p>
    Projet publié sous licence libre AGPL-3.0 (<a href="https://github.com/wincelau/ratpstatus">voir les sources</a>) initié par <a href="https://piaille.fr/@winy">winy</a>
</p>
<p>Ce site n'est pas un site officiel de la <a href="https://ratp.fr/">RATP</a></p>
</footer>
<dialog id="tooltipModal"></dialog>
<dialog id="helpModal">
    <h3>Aide et informations</h3>
    <p>RATPstatus.fr est une page de suivi et d'historisation de l'état du trafic des Ⓜ️ Métros, 🚆 RER / Transiliens et 🚈 Tramways d'Île de France</p>
    <p>L'état du trafic est récupéré toutes les 2 minutes à partir du 23 avril 2024.</p>
    <p>Chaque bloc répresente une durée de 2 minutes, les couleurs ont la signification suivante :<br /><br />
        <span class="ok"></span> Rien à signaler<br />
        <span class="pb"></span> Perturbation<br />
        <span class="bq"></span> Blocage / Interruption<br />
        <span class="tx"></span> Travaux
    </p>
    <p>Les informations présentées proviennent des données open data du portail <a href="https://prim.iledefrance-mobilites.fr/">PRIM Île-de-France mobilités</a>.</p>
    <p>Le projet initié par <a href="https://piaille.fr/@winy">winy</a> est publié sous licence libre AGPL-3.0 : <a href="https://github.com/wincelau/ratpstatus">https://github.com/wincelau/ratpstatus</a></p>
</dialog>
</body>
</html>

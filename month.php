<?php
require __DIR__.'/app/Config.php';

$handle = fopen(__DIR__.'/datas/export/historique_statuts.csv', "r");
$mode="trains";
if(!isset($_GET['date'])) {
    $_GET['date'] = date('Ym');
}
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
function url($url) {
    if($GLOBALS['isStaticResponse']) {

        return $url;
    }

    preg_match('|/?([^/]*)/([^/]*).html|', $url, $matches);

    return "index.php?".http_build_query(['date' => $matches[1], 'mode' => $matches[2]]);
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
<div id="container" style="width: 1460px;">
<header role="banner" id="header">
<nav id="nav_liens">
<a id="btn_help" href="#aide" title="Aide et informations">ℹ️<i class="mobile_hidden"> </i><span class="mobile_hidden">Aide et Infos</span></a>
</nav>
<nav id="nav_liens_right">
</nav>
<h1><span class="mobile_hidden">Suivi de l'état du trafic<span> des transports IDF</span></span><span class="mobile_visible">État du trafic</span></h1>
<h2>Mai 2024</h2>
<nav id="nav_mode"><?php foreach(Config::getLignes() as $m => $ligne): ?><a class="<?php if($mode == $m): ?>active<?php endif; ?>" href=""><?php echo Config::getModeLibelles()[$m] ?></a><?php endforeach; ?></nav>
<div class="hline"><?php for($i = 0; $i <= 30; $i = $i + 60): ?><div class="ih"><?php if($i % 60 == 0): ?><small><?php echo sprintf("%02d", (intval($i / 60) + 4) % 24) ?>h</small><?php endif; ?></div><?php endfor; ?></div>
</header>
<main role="main">
<div id="lignes">
<?php foreach(Config::getLignes()[$mode] as $ligne => $logo): ?>
<div class="ligne" data-id="<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><div class="logo"><a href="#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" width="30" height="30" style="margin-top: 5px;"/></a></div>
<?php $j=1; ?>
<?php foreach($statuts[$ligne] as $date => $data): ?>
    <?php if($date == "total"): continue; endif; ?>
    <a href="<?php echo url("/".str_replace('-', '', $date)."/".$mode.".html") ?>#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>" style="display: block; float:left; height: 40px; width: 40px; <?php if($j % 7 == 0): ?>border-right: 4px solid #fff;<?php else: ?>border-right: 1px solid #fff;<?php endif; ?> position: relative;" title="<?php echo $date; ?>">
        <?php $rest = 0; ?>
        <?php foreach(["OK", "TX", "PB", "BQ"] as $statut): ?>
            <?php if($rest > 0 && $data["pourcentages"][$statut] > $rest): ?>
                <div class="<?php echo strtolower($statut) ?>" style="display: block; float:right; height: 4px; width: <?php echo $rest * 4 ?>px;"></div>
                <?php $data["pourcentages"][$statut] = $data["pourcentages"][$statut] - $rest; $rest = 0; ?>
            <?php endif; ?>
            <?php if(intdiv($data["pourcentages"][$statut], 10) > 0): ?>
            <div class="<?php echo strtolower($statut) ?>" style="display: block; float:right; height: <?php echo 4*(intdiv($data["pourcentages"][$statut], 10)) ?>px; width: 40px;"></div>
            <?php endif; ?>
            <?php if($data["pourcentages"][$statut] % 10 > 0): ?>
            <div class="<?php echo strtolower($statut) ?>" style="display: block; float:right; height: 4px; width: <?php echo ($data["pourcentages"][$statut] % 10) * 4 ?>px;"></div>
            <?php endif; ?>
            <?php if($rest > 0): ?>
            <?php $rest = $rest - ($data["pourcentages"][$statut] % 10); ?>
            <?php else: ?>
            <?php $rest = 10 - ($data["pourcentages"][$statut] % 10); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </a>
    <?php $j++; ?>
<?php endforeach; ?>
<?php /*for($i = 0; $i < 1380; $i = $i + 2): $isSameForFive = ($i % 10 == 0 && $day->isSameColorClassForFive($i, $ligne)); ?><i class="i <?php echo $day->getColorClass($i, $ligne) ?> <?php if($i % 60 == 0): ?>i1h<?php elseif($i % 10 == 0): ?>i10m<?php endif; ?><?php if($isSameForFive): ?> i5sa<?php endif; ?>" title="<?php echo sprintf("%02d", (intval($i / 60) + 4) % 24) ?>h<?php echo sprintf("%02d", ($i % 60) ) ?><?php if($isSameForFive): ?> - <?php echo sprintf("%02d", (intval(($i+(5*2)) / 60) + 4) % 24) ?>h<?php echo sprintf("%02d", (($i+(5*2)) % 60)) ?><?php endif; ?><?php echo $day->getInfo($i, $ligne, ($isSameForFive) ? 5 : 1) ?>"></i>
<?php if($isSameForFive): $i=$i+(4*2); endif;endfor; */ ?><span class="dispoligne" style="padding-top: 5px; padding-bottom: 5px;" title="Aucune perturbation pour <?php echo $statuts[$ligne]["total"] ?>% du trafic de toute la journée"><img alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>" style="height: 24px; left: -18px; top: 7px;" /><?php echo str_replace(" ", "&nbsp;", sprintf("% 3d", $statuts[$ligne]["total"])) ?>%</span></div>

<?php endforeach; ?>
</div>
</main>
</div>
<div id="legende">
<p><span class="ok"></span> % Rien à signaler <span class="pb" style="margin-left: 20px;"></span> % Perturbation <span class="bq" style="margin-left: 20px;"></span> % Blocage / Interruption <span class="tx" style="margin-left: 20px;"></span> % Travaux <span class="no" style="margin-left: 20px;"></span> Aucune donnée</p>
</div>
<footer role="contentinfo" id="footer">
<p>
    <a href="">RATPStatus.fr</a> est publié sous licence libre AGPL-3.0 (<a href="https://github.com/wincelau/ratpstatus">voir les sources</a>), ce n'est pas un site officiel de la <a href="https://www.ratp.fr/">RATP</a>.
</p>
</footer>
</body>
</html>

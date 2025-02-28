<div id="tabLigneContainer">
<div id="tabLigne">
    <a class="active" href="#incidents" style="font-weight: bold;">Tous</a>
<?php foreach(Config::getLignes()[$mode] as $ligne => $img): ?>
    <a href="#incidents_<?php echo str_replace(["Métro ","Ligne "], "", $ligne) ?>"><img height="24" src="<?php echo $img ?>" alt="<?php echo $ligne ?>" /></a>
<?php endforeach; ?>
</div>
</div>

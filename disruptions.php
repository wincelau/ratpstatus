<?php require __DIR__.'/day.php'; ?>
<?php
$file = __DIR__.'/datas/json_userinfos/'.$day->getDateStart()->format('Ymd').'.json';

$userDisruptions = [];

if(is_file($file)) {
    $userDisruptions = (array) json_decode(file_get_contents($file));
}

if(count($_POST)) {
foreach($_POST['type'] as $id => $type) {
    if(!$type && !isset($userDisruptions[$id])) {
        continue;
    }
    if(!$type) {
        $type = null;
    }
    if(!isset($userDisruptions[$id])) {
        $userDisruptions[$id] = new stdClass();
    }
    $userDisruptions[$id]->type = $type;
}

foreach($_POST['origine'] as $id => $origine) {
    if(!$origine && !isset($userDisruptions[$id])) {
        continue;
    }
    if(!$origine) {
        $origine = null;
    }
    if(!isset($userDisruptions[$id])) {
        $userDisruptions[$id] = new stdClass();
    }
    $userDisruptions[$id]->origine = $origine;
}

file_put_contents($file, json_encode($userDisruptions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
header('Location: /disruptions.php?date='.$day->getDateStart()->format('Ymd'));
exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/favicon.ico" />
    <link rel="icon" type="image/png" href="/images/favicon.png" />
    <title>Disruptions - RATP Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container">
    <form id="form_disruptions" action="" method="POST">
    <?php foreach(Config::getLignes() as $mode => $lignes): ?>
        <?php foreach($lignes as $ligne => $logo) :?>
            <?php $disruptions = $day->getDistruptionsByLigne($ligne); ?>
            <?php foreach($disruptions as $d): ?>
            <div class="row mb-4">
                <div class="col-6">
                <div class="card my-3 h-100 <?php if(isset($userDisruptions[$d->getId()]->type)): ?>border-success<?php endif; ?>">
                    <div class="card-header">
                        <h5 class="card-title"><img style="height: 18px; display: inline-block;" alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>"/> <?php echo $d->getTitle() ?></h5>
                        <h5 class="card-subtitle mt-2 text-body-secondary"><?php echo $d->getDateStart()->format("H\hi") ?> - <?php echo $d->getDateEnd()->format("H\hi") ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(html_entity_decode(strip_tags(str_replace(["<br>", "</p>"], "\n", $d->getMessage())))) ?></p>
                    </div>
                    <div class="card-footer <?php if(isset($userDisruptions[$d->getId()])): ?>bg-success<?php endif; ?> text-body-secondary small">
                        ID : <?php echo $d->getId(); ?>
                        <span title="<?php echo str_replace('"', '', print_r($d, true)) ?>" class="float-end badge text-bg-light">json</span>
                        <span class="float-end">Dernière mise à jour : <?php echo $d->getLastUpdate()->format("d/m/Y à H\hi\ss"); ?></span>

                    </div>
                </div>
                </div>
                <div class="col-5">
                    <div class="form-floating mt-3">
                      <select id="input_type_<?php echo $d->getId(); ?>" class="form-select" name="type[<?php echo $d->getId(); ?>]">
                        <option <?php if(!isset($userDisruptions[$d->getId()]->type) || !$userDisruptions[$d->getId()]->type): ?>selected<?php endif; ?> selected></option>
                        <?php foreach(Config::getTypesPerturbation() as $type => $typeLibelle): ?>
                        <option <?php if(isset($userDisruptions[$d->getId()]) && $userDisruptions[$d->getId()]->type == $type): ?>selected<?php endif; ?> value="<?php echo $type ?>"><?php echo $typeLibelle ?></option>
                        <?php endforeach; ?>
                      </select>
                      <label>Type de perturbation</label>
                    </div>
                    <?php if($d->getSuggestionType()): ?>
                    <div class="text-muted small mt-2">
                        Suggestion : <code data-input="input_type_<?php echo $d->getId(); ?>" onclick="document.getElementById(this.dataset.input).value = this.innerText;"><?php echo $d->getSuggestionType(); ?></code>
                    </div>
                    <?php endif; ?>
                    <div class="form-floating mt-3">
                        <input id="input_origine_<?php echo $d->getId(); ?>" type="text" name="origine[<?php echo $d->getId(); ?>]" value="<?php if(isset($userDisruptions[$d->getId()])): echo $userDisruptions[$d->getId()]->origine; endif; ?>" class="form-control">
                        <label>Origine de la perturbation</label>
                    </div>
                    <?php if($d->getSuggestionOrigine()): ?>
                    <div class="text-muted small mt-2">
                        Suggestion : <code data-input="input_origine_<?php echo $d->getId(); ?>" onclick="document.getElementById(this.dataset.input).value = this.innerText;"><?php echo $d->getSuggestionOrigine(); ?></code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if($disruptions): ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </form>
    </div>
    <div class="position-fixed bottom-0 p-2 bg-white shadow w-100 text-center" style="z-index: 100;">
    <button form="form_disruptions" type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</body>
</html>

<?php require __DIR__.'/day.php'; ?>
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
    <?php foreach(Config::getLignes() as $mode => $lignes): ?>
        <?php foreach($lignes as $ligne => $logo) :?>
            <?php $disruptions = $day->getDistruptionsByLigne($ligne); ?>
            <?php foreach($disruptions as $d): ?>
            <div class="row mb-4">
                <div class="col-6">
                <div class="card my-3 h-100">
                    <div class="card-header">
                        <h5 class="card-title"><img style="height: 18px; display: inline-block;" alt="<?php echo $ligne ?>" title="<?php echo $ligne ?>" src="<?php echo $logo ?>"/> <?php echo $d->getTitle() ?></h5>
                        <h5 class="card-subtitle mt-2 text-body-secondary"><?php echo $d->getDateStart()->format("H\hi") ?> - <?php echo $d->getDateEnd()->format("H\hi") ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(html_entity_decode(strip_tags(str_replace(["<br>", "</p>"], "\n", $d->getMessage())))) ?></p>
                    </div>
                    <div class="card-footer text-body-secondary small">
                        ID : <?php echo $d->getId(); ?>

                        <span class="float-end">Dernière mise à jour : <?php echo $d->getLastUpdate()->format("d/m/Y à H\hi"); ?></span>
                    </div>
                </div>
                </div>
                <div class="col-5">
                    <div class="form-floating mt-3">
                      <select class="form-select" name="type[<?php echo $d->getId(); ?>]">
                        <option selected></option>
                        <option value="1">Perturbation partielle</option>
                        <option value="1">Perturbée</option>
                        <option value="2">Fortement perturbée</option>
                        <option value="3">Interruption partielle</option>
                        <option value="3">Interruption sur l'ensemble de la ligne</option>
                        <option value="3">Station(s) non desservis</option>
                        <option value="3">Trains supprimés</option>
                        <option value="1">Changement d'horaires</option>
                        <option value="1">Changement de composition</option>
                        <option value="1">Aucune perturbation en cours</option>
                      </select>
                      <label>Type de perturbation</label>
                    </div>
                    <div class="form-floating mt-3">
                      <input type="text" name="origine[<?php echo $d->getId(); ?>]" class="form-control">
                      <label>Origine de la perturbation</label>
                    </div>
                    <label class="text-muted mt-3">Pérturbations liées</label>
                    <div class="mt-2 border p-2" style="max-height: 120px; overflow: scroll;">
                    <?php foreach($day->getDistruptionsByLigne($ligne) as $dother): ?>
                        <div class="form-check">
                          <input class="form-check-input" name="liaisons[<?php echo $d->getId(); ?>][<?php echo $dother->getId() ?>]" type="checkbox" value="" id="checkbox_liaisons_<?php echo $d->getId(); ?>_<?php echo $dother->getId(); ?>">
                          <label title="<?php echo $dother->getMessagePlainText() ?>" class="form-check-label" for="checkbox_liaisons_<?php echo $d->getId(); ?>_<?php echo $dother->getId(); ?>">
                            <span class="text-muted"><?php echo $dother->getDateStart()->format("H\hi") ?> - <?php echo $dother->getDateEnd()->format("H\hi") ?></span> : <?php echo $dother->getTitle() ?>
                          </label>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if($disruptions): ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </div>
</body>
</html>

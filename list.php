<?php require __DIR__.'/day.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/favicon.ico" />
    <link rel="icon" type="image/png" href="/images/favicon.png" />
    <title>Liste des incidents - RATP Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css?202405081203">
</head>
<body>
    <div class="container-md" style="max-width: 800px;">
<?php foreach($day->getDisruptions() as $disruption): ?>
        <div class="card my-3 me-4">
            <div class="card-header">
                <h5><img style="height: 20px;" src="<?php echo $disruption->getLigne()->getImage(); ?>" />
                    <span class="badge text-bg-light"><?php if($disruption->getCause() == Impact::CAUSE_TRAVAUX): ?>🚧<?php else: ?>⚠️<?php endif; ?></span>
                <span class="badge text-bg-light">🕥 <?php if($disruption->getDateEnd() < new DateTime()): ?>De <?php echo $disruption->getDateStart()->format("H\hi") ?> à <?php echo $disruption->getDateEnd()->format("H\hi") ?><?php else: ?>Depuis <?php echo $disruption->getDateStart()->format("H\hi") ?><?php endif; ?></span>
                <span class="badge text-bg-light">⌛ <?php echo $disruption->getDuration()->format("%hh%I"); ?></span>
                </h5>
            </div>
            <ul class="list-group list-group-flush">
            <?php foreach($disruption->getImpacts() as $i): ?>
                    <li class="list-group-item">
                        <span class="badge text-bg-light"><?php echo $i->getDateStart()->format("H\hi") ?></span> <span class="<?php echo $i->getColorClass() ?> text-white strong float-end rounded px-2 small"><?php echo $i->getDuration()->format("%hh%I"); ?></span> <?php echo $i->getTitle() ?>
                        <!-- <p><?php echo nl2br(html_entity_decode(strip_tags(str_replace(["<br>", "</p>"], "\n", $i->getMessage())))) ?></p> -->
                        <!-- <small class="text-muted">MAJ <?php echo $i->getLastUpdate()->format('d/m/Y H:i:s') ?></small>
                        <small class="text-muted"> - FILE <?php echo $i->getDateCreation()->format('d/m/Y H:i:s') ?></small> -->
                    </li>
            <?php endforeach; ?>
            </ul>
        </div>
<?php endforeach; ?>
</div>
</body>
</html>

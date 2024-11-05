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
    <div class="container-sm" style="max-width: 720px;">
        <h1 class="mt-3">Incident du <?php echo $day->getDateStart()->format('d/m/Y'); ?></h1>
        <h2 class="mt-3">En cours</h2>
        <?php foreach($day->getDisruptions() as $disruption): ?>
                <?php if($disruption->getDateEnd() < new DateTime()): continue; endif; ?>
                <?php include(__DIR__.'/_disruption.php') ?>
        <?php endforeach; ?>
        <h2 class="mt-3">Terminé</h2>
        <?php foreach($day->getDisruptions() as $disruption): ?>
            <?php if($disruption->getDateEnd() >= new DateTime()): continue; endif; ?>
            <?php include(__DIR__.'/_disruption.php') ?>
        <?php endforeach; ?>
</div>
</body>
</html>

<?php require __DIR__.'/day.php'; ?>
<?php header('Content-Type: text/csv'); ?>
<?php echo $day->toCsvStatuts(); ?>

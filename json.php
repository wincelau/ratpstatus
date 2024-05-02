<?php require __DIR__.'/day.php'; ?>
<?php header('Content-Type: application/json'); ?>
<?php echo $day->toJson(); ?>

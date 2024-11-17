<h4 class="<?php if($disruption->isInProgress()): ?><?php echo $disruption->getCurrentColorClass() ?><?php endif; ?>"><img src="<?php echo $disruption->getLigne()->getImage(); ?>" /> <span><?php if($disruption->getDateEnd() < new DateTime()): ?>De <?php echo $disruption->getDateStart()->format("H\hi") ?> à <?php echo $disruption->getDateEnd()->format("H\hi") ?><?php else: ?>Depuis <?php echo $disruption->getDateStart()->format("H\hi") ?><?php endif; ?></span> <span>⌛ <?php echo $disruption->getDuration()->format("%hh%I"); ?></span></h4>
<ul>
<?php $isFirst = true; ?>
<?php foreach($disruption->getImpactsOptimized() as $i): ?>
<?php if($isFirst && $disruption->getDateEnd() < new DateTime()): ?>
<li><span class="ok"></span> <strong><?php echo $i->getDateEnd()->format("H\hi") ?></strong> <span></span>Fin de l'incident<p></p></li>
<?php $isFirst = false; ?>
<?php endif; ?>
<li><span class="<?php echo $i->getColorClass() ?>"></span> <strong><?php echo $i->getDateStart()->format("H\hi") ?></strong> <span>⌛ <?php echo $i->getDuration()->format("%hh%I"); ?></span> <?php echo $i->getTitle() ?>
        <p><?php echo $i->getMessagePlainText() ?></p></li>
<?php endforeach; ?>
</ul>

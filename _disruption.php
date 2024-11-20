<div data-line="<?php echo $disruption->getLigne()->getId() ?>" class="disruption <?php if($disruption->isInFuture()): ?>future<?php endif; ?> <?php if(!$disruption->isPast()): ?><?php echo $disruption->getCurrentColorClass() ?><?php endif; ?>">
<h4><img src="<?php echo $disruption->getLigne()->getImage(); ?>" /> <span><?php if($disruption->isPast()): ?>De <?php echo $disruption->getDateStart()->format("H\hi") ?> à <?php echo $disruption->getDateEnd()->format("H\hi") ?><?php elseif($disruption->isInProgress()): ?>Depuis <?php echo $disruption->getDateStart()->format("H\hi") ?><?php elseif($disruption->isInFuture()): ?>À <?php echo $disruption->getDateStart()->format("H\hi") ?> jusqu'à <?php echo $disruption->getDateEnd()->format("H\hi") ?><?php endif; ?></span> <span><?php if(!$disruption->isInFuture()): ?>⌛ <?php echo $disruption->getDuration()->format("%hh%I"); ?><?php endif; ?></span></h4>
<ul>
<?php $isFirst = true; ?>
<?php foreach($disruption->getImpactsOptimized() as $i): ?>
<?php if($isFirst && $disruption->getDateEnd() < new DateTime()): ?>
<li><span class="ok"></span> <strong><?php echo $i->getDateEnd()->format("H\hi") ?></strong> <span></span>Fin de l'incident<p></p></li>
<?php $isFirst = false; ?>
<?php endif; ?>
<li><span class="<?php echo $i->getColorClass() ?>"></span> <strong><?php echo $i->getDateStart()->format("H\hi") ?></strong> <span>⌛ <?php echo $i->getDuration()->format("%hh%I"); ?></span> <?php echo $i->getTitle() ?>
        <p><?php echo nl2br(preg_replace("/[\n]+$/i", "", $i->getMessagePlainText())) ?></p></li>
<?php endforeach; ?>
</ul>
</div>

<div data-line="<?php echo $disruption->getLigne()->getId() ?>" id="disruption_<?php echo explode(":", $disruption->getId())[1] ?>" class="disruption <?php if($disruption->isInFuture()): ?>future<?php endif; ?> <?php if(!$disruption->isPast()): ?><?php echo $disruption->getCurrentColorClass() ?><?php endif; ?>">
<h4><img alt="<?php echo $disruption->getLigne()->getName() ?>" src="<?php echo $disruption->getLigne()->getImage(); ?>"/><span><?php if($disruption->isPast()): ?>De <?php echo $disruption->getDateStart()->format("H\hi") ?> à <?php echo $disruption->getDateEnd()->format("H\hi") ?><?php elseif($disruption->isInProgress()): ?>Depuis <?php echo $disruption->getDateStart()->format("H\hi") ?><?php endif; ?></span><span class="mobile_hidden" title="<?php echo $disruption->getOrigine() ?>"><?php if($disruption->getOrigine()): ?>&nbsp;: <?php echo $disruption->getOrigine() ?><?php endif; ?></span><span><?php if($disruption->isInProgress()): ?>⏳ <?php endif; ?><?php echo $disruption->getDurationText(); ?></span><?php if($disruption->getOrigine()): ?><span class="mobile_visible"><?php echo $disruption->getOrigine() ?></span><?php endif; ?></h4>
<ul>
<?php if($disruption->getDateEnd() < new DateTime()): ?>
<li><span class="ok"></span> <strong><?php echo $disruption->getDateEnd()->format("H\hi") ?></strong> <span></span> <span><br /><br /></span>Fin de l'incident<p></p></li>
<?php endif; ?>
<?php foreach($disruption->getImpactsOptimized() as $i): ?>
<li><span class="<?php echo $i->getColorClass() ?>"></span> <strong><?php echo $i->getDateStart()->format("H\hi") ?></strong> <span><?php if($i->isInProgress()): ?>⏳ <?php endif; ?><?php echo $i->getDurationText(); ?></span><span><br /><br /></span><?php echo $i->getTitle() ?>
<p class="ellips"><?php echo nl2br(preg_replace("/[\n]+$/i", "", $i->getMessagePlainText())) ?></p>
</li>
<?php endforeach; ?>
</ul>
</div>

<div data-line="<?php echo $disruption->getLigne()->getId() ?>" class="disruption <?php if($disruption->isInFuture()): ?>future<?php endif; ?> <?php if(!$disruption->isPast()): ?><?php echo $disruption->getCurrentColorClass() ?><?php endif; ?>">
<h4 style="display: block;"><img src="<?php echo $disruption->getLigne()->getImage(); ?>"/><span><?php if($disruption->isPast()): ?>De <?php echo $disruption->getDateStart()->format("H\hi") ?> à <?php echo $disruption->getDateEnd()->format("H\hi") ?><?php elseif($disruption->isInProgress()): ?>Depuis <?php echo $disruption->getDateStart()->format("H\hi") ?><?php endif; ?></span><?php if($disruption->getOrigine()): ?><span class="mobile_hidden" title="<?php echo $disruption->getOrigine() ?>">&nbsp;: <?php echo $disruption->getOrigine() ?></span><?php endif; ?><?php if(strpos($disruption->getId(), 'distruption_id_calculate:') !== false && $disruption->getDateStart() > new DateTime('2024-11-28')): ?><span style="opacity: 0.5; float: right;"> ⚠️</span><?php endif; ?><span><?php if(!$disruption->isInFuture()): ?><?php if($disruption->isInProgress()): ?>⏳ <?php endif; ?><?php echo $disruption->getDurationText(); ?><?php endif; ?></span></h4>
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

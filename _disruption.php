<div class="card my-3">
    <div class="card-header">
        <h5 class="mb-0"><img style="height: 20px;" src="<?php echo $disruption->getLigne()->getImage(); ?>" />
            <span class="badge text-bg-light"><?php if($disruption->getCause() == Impact::CAUSE_TRAVAUX): ?>🚧<?php else: ?>⚠️<?php endif; ?></span>
        <span class="badge text-bg-light">🕥 <?php if($disruption->getDateEnd() < new DateTime()): ?>De <?php echo $disruption->getDateStart()->format("H\hi") ?> à <?php echo $disruption->getDateEnd()->format("H\hi") ?><?php else: ?>Depuis <?php echo $disruption->getDateStart()->format("H\hi") ?><?php endif; ?></span>
        <span class="badge text-bg-light float-end">⌛ <?php echo $disruption->getDuration()->format("%hh%I"); ?></span>
        </h5>
    </div>
    <ul class="list-group list-group-flush">
    <?php $isFirst = true; ?>
    <?php foreach($disruption->getImpacts() as $i): ?>
        <?php if($isFirst && $disruption->getDateEnd() < new DateTime()): ?>
            <li class="list-group-item">
                <strong class="me-2 small"><?php echo $i->getDateEnd()->format("H\hi") ?></strong> <span class="ok text-white strong float-end rounded px-2 small">Fin</span> <?php echo $i->getTitle() ?>
            </li>
            <?php $isFirst = false; ?>
        <?php endif; ?>
            <li class="list-group-item" style="overflow: hidden; max-height: 40px;">
                <strong class="me-2 small"><?php echo $i->getDateStart()->format("H\hi") ?></strong> <span class="<?php echo $i->getColorClass() ?> text-white strong float-end rounded px-2 small"><?php echo $i->getDuration()->format("%hh%I"); ?></span> <?php echo $i->getTitle() ?>
            </li>
    <?php endforeach; ?>
    </ul>
</div>

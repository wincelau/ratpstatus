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
    <?php foreach($disruption->getImpactsOptimized() as $i): ?>
        <?php if($isFirst && $disruption->getDateEnd() < new DateTime()): ?>
            <li class="list-group-item">
                <strong class="me-2 small"><?php echo $i->getDateEnd()->format("H\hi") ?></strong> <span class="ok text-white strong float-end rounded px-2 small text-center" style="width: 60px;">Fin</span><span class="text-muted">Fin de l'incident</span>
            </li>
            <?php $isFirst = false; ?>
        <?php endif; ?>
            <li class="list-group-item">
                <strong class="me-2 small"><?php echo $i->getDateStart()->format("H\hi") ?></strong> <span class="<?php echo $i->getColorClass() ?> text-white strong float-end rounded px-2 small text-center" style="width: 60px;"><?php echo $i->getDuration()->format("%hh%I"); ?></span> <?php echo $i->getTitle() ?>
                <p class="text-muted small mb-0"><?php echo $i->getMessagePlainText() ?></p>
            </li>
    <?php endforeach; ?>
    </ul>
</div>

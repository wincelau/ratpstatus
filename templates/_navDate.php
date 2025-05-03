<h1><span class="mobile_hidden">Suivi de l'état du trafic<span> des transports IDF</span></span><span class="mobile_visible">État du trafic</span></h1>
<h2>
<?php if($period->getDateStartKey() == '12lastmonth'):?>
<a class="disabled">⬅️</a>
<?php else: ?>
<a title="Voir le mois précédent" href="<?php echo View::url("/".$period->getDatePrevious()->format($period->getDateFormat())."/".$mode.".html") ?>">⬅️<span class="visually-hidden">Voir la période précédente</span></a>
<?php endif; ?>
<select id="select-day" onchange="document.location.href=this.value; this.value='';" autocomplete="off">
    <option style="display: none;" value="" selected="selected"><?php if($period->isToday()):?>Aujourd'hui<?php else: ?><?php echo $period->getDateStartLabel(); ?><?php endif; ?></option>
    <?php foreach(View::getDatesChoices() as $group => $choices): ?>
    <optgroup label="<?php echo $group ?>">
    <?php foreach($choices as $dateChoiceKey => $dateChoiceLibelle): ?>
    <option value="<?php echo View::url("/".$dateChoiceKey."/".$mode.".html") ?>"><?php if($period->getDateStartKey() == $dateChoiceKey):?>🔘<?php else: ?>⚪<?php endif; ?> <?php echo $dateChoiceLibelle ?><?php if($dateChoiceKey == date('Ymd')): ?> 🔥<?php endif; ?></option>
    <?php endforeach; ?>
    </optgroup>
    <?php endforeach; ?>
</select>
<?php if($period->getDateStart()->format($period->getDateFormat()) >= date($period->getDateFormat()) || $period->getDateStartKey() == '12lastmonth'):?>
<a class="disabled">➡️</a>
<?php else: ?>
<a title="Voir le jour suivant" href="<?php echo View::url("/".$period->getDateNext()->format($period->getDateFormat())."/".$mode.".html") ?>">➡️<span class="visually-hidden">Voir la période suivante</span></a><?php
endif; ?></h2>

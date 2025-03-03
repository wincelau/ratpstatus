<select id="select-day" style="<?php if($period->isToday()):?>font-weight: bold;<?php endif;?>" onchange="document.location.href=this.value; this.value='';" autocomplete="off">
    <option style="display: none;" value="" selected="selected"><?php echo $libelleToday; ?></option>
    <?php foreach(View::getDatesChoices() as $group => $choices): ?>
    <optgroup label="<?php echo $group ?>">
    <?php foreach($choices as $dateChoiceKey => $dateChoiceLibelle): ?>
    <option value="<?php echo View::url("/".$dateChoiceKey."/".$mode.".html") ?>"><?php if($keyToday == $dateChoiceKey):?>🔘<?php else: ?>⚪<?php endif; ?> <?php echo $dateChoiceLibelle ?> <?php if($dateChoiceKey == date('Ymd')): ?>🔥<?php endif; ?></option>
    <?php endforeach; ?>
    </optgroup>
    <?php endforeach; ?>
</select>

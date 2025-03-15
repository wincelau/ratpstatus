<h3>Résumé</h3>

<p style="color: black; margin-bottom: 0;">
<!--<?php if($ligne == "TOTAL"): ?><?php echo Config::getModeLibelles()[$mode] ?> <small>(toutes les lignes)</small><?php else: ?><img height="14" src="<?php echo Config::getLignes()[$mode][$ligne] ?>" alt="<?php echo $ligne ?>" /> <?php echo $ligne; ?><?php endif ?><br style="margin-bottom: 5px" />
📅 <?php echo $period->getDateStartLabel(); ?> <br style="margin-bottom: 10px" />-->
📊 <?php echo $statuts[($ligne == "TOTAL") ? "total" : $ligne]["total"]["pourcentages"]["OK"] ?>% du temps sans perturbation<br style="margin-bottom: 5px" />
🧮 <?php echo $motifs[$ligne]["TOTAL"]['count'] ?> incidents<br style="margin-bottom: 10px"  />
⌛ <?php echo View::formatDuration($statuts[($ligne == "TOTAL") ? "total" : $ligne]["total"]['minutes']['PB'] + $statuts[($ligne == "TOTAL") ? "total" : $ligne]["total"]['minutes']['BQ']) ?> de perturbations <small>(hors travaux)</small><br style="margin-bottom: 5px" />
🟥 <?php echo View::formatDuration($statuts[($ligne == "TOTAL") ? "total" : $ligne]["total"]['minutes']['BQ']) ?> de blocage ou interruption<br style="margin-bottom: 5px" />
🚧 <?php echo View::formatDuration($statuts[($ligne == "TOTAL") ? "total" : $ligne]["total"]['minutes']['TX']) ?> de travaux<br style="margin-bottom: 10px" />
<!-- <small style="color: #777; font-style: italic;">L'état étant suivi toutes les 2 minutes, un écart de quelques minutes peut être constaté par rapport aux heures relevées dans les incidents.</small> -->
</p>
<!-- <h3 style="margin-top: 20px; margin-bottom: 0px;">Origines</h3> -->
<table>
    <thead>
        <tr>
            <th style="text-align: left;">Motif</th>
            <th style="text-align: center;">Nombre</th>
            <th style="text-align: center;">Durée Totale</th>
            <th style="text-align: center; font-weight: normal;"><small>dont bloquant</small></th>
            <th style="text-align: center;">Durée Moyenne</th>
            <th style="text-align: center; font-weight: normal;"><small>dont bloquant</small></th>
        </tr>
    </thead>
    <tbody>
<?php foreach($motifsLigne as $motif => $stats): ?>
    <?php if($motif == "TOTAL"): continue; endif; ?>
    <tr>
        <td><?php if($motif): ?><?php echo $motif; ?><?php else: ?><em style="color: #444;">Aucun motif détécté</em><?php endif; ?></td>
        <td class="num-right"><?php echo $stats['count']; ?></td>
        <td class="num-right"><?php echo View::formatDuration($stats['total_duration']); ?></td>
        <td class="num-left"><?php if($stats['total_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['total_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration_bloquant'] % 60); ?> de blocage ou d'interruption"><?php echo View::formatDuration($stats['total_duration_bloquant']); ?><i class="bq"></i></small><?php endif; ?></td>
        <td class="num-right"><?php echo View::formatDuration($stats['average_duration']); ?></td>
        <td class="num-left"><?php if($stats['average_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['average_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration_bloquant'] % 60); ?> en moyenne de blocage ou d'interruption"><?php echo View::formatDuration($stats['average_duration_bloquant']); ?><i class="bq"></i></small><?php endif; ?></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>

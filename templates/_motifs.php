<table>
    <thead>
        <tr>
            <th style="text-align: left;">Motif</th>
            <th style="text-align: center;">Durée Totale</th>
            <th style="text-align: center; font-weight: normal;"><small>dont bloquant</small></th>
            <th style="text-align: center;">Durée Moyenne</th>
            <th style="text-align: center; font-weight: normal;"><small>dont bloquant</small></th>
            <th style="text-align: center;">Nombre</th>
        </tr>
    </thead>
    <tbody>
<?php foreach($motifsLigne as $motif => $stats): ?>
    <?php if($motif == "TOTAL"): continue; endif; ?>
    <tr>
        <td><?php if($motif): ?><?php echo $motif; ?><?php else: ?><em style="color: #444;">Aucun motif détécté</em><?php endif; ?></td>
        <td class="num-right"><?php echo View::formatDuration($stats['total_duration']); ?></td>
        <td class="num-left"><?php if($stats['total_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['total_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['total_duration_bloquant'] % 60); ?> de blocage ou d'interruption"><?php echo View::formatDuration($stats['total_duration_bloquant']); ?><i class="bq"></i></small><?php endif; ?></td>
        <td class="num-right"><?php echo View::formatDuration($stats['average_duration']); ?></td>
        <td class="num-left"><?php if($stats['average_duration_bloquant']): ?><small title="dont <?php echo intdiv($stats['average_duration_bloquant'], 60); ?>h<?php echo sprintf("%02d", $stats['average_duration_bloquant'] % 60); ?> en moyenne de blocage ou d'interruption"><?php echo View::formatDuration($stats['average_duration_bloquant']); ?><i class="bq"></i></small><?php endif; ?></td>
        <td class="num-right"><?php echo $stats['count']; ?></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>

<?php

date_default_timezone_set('Europe/Paris');

$jsonFile = $argv[1];

$json = json_decode(file_get_contents($jsonFile));
$jsonOriginal = json_decode(file_get_contents($jsonFile));

$dateFile = new DateTime($json->lastUpdatedDate, new DateTimeZone("UTC"));
$dateFile->setTimeZone(new DateTimeZone(date_default_timezone_get()));

$disruptionIdsDeleted = [];

foreach($json->lines as $indexLine => $line) {
    if($line->mode != "Bus") {
        continue;
    }

    foreach($line->impactedObjects as $indexObject => $object) {
        $disruptionIdsDeleted = array_merge($disruptionIdsDeleted, $object->disruptionIds);
    }
    unset($json->lines[$indexLine]);
}

$disruptionIdsDeleted = array_unique($disruptionIdsDeleted);

foreach($json->disruptions as $indexDisruption => $disruption) {
    if(in_array($disruption->id, $disruptionIdsDeleted)) {
        unset($json->disruptions[$indexDisruption]);
        continue;
    }
    foreach($disruption->applicationPeriods as $indexPeriod => $period) {
        $dateBegin = new DateTime($period->begin);
        $dateBegin->modify('-1 hour');
        $dateEnd = new DateTime($period->end);
        $dateEnd->modify('+1 hour');
        if($dateFile > $dateBegin && $dateFile < $dateEnd) {
            continue;
        }
        unset($disruption->applicationPeriods[$indexPeriod]);
    }
    $disruption->applicationPeriods = array_values($disruption->applicationPeriods);
    if(!count($disruption->applicationPeriods)) {
        $disruptionIdsDeleted[] = $disruption->id;
        unset($json->disruptions[$indexDisruption]);
        continue;
    }
}

$json->disruptions = array_values($json->disruptions);

foreach($json->lines as $indexLine => $line) {
    foreach($line->impactedObjects as $indexObject => $object) {
        foreach($object->disruptionIds as $indexDisruptionId => $diruptionId) {
            if(in_array($diruptionId, $disruptionIdsDeleted)) {
                unset($object->disruptionIds[$indexDisruptionId]);
            }
        }
        $object->disruptionIds = array_values($object->disruptionIds);
        if(!count($object->disruptionIds)) {
            $disruptionIdsDeleted[] = $disruption->id;
            unset($line->impactedObjects[$indexObject]);
        }
    }
    $line->impactedObjects = array_values($line->impactedObjects);
}

$json->lines = array_values($json->lines);

echo json_encode($json, JSON_UNESCAPED_UNICODE);

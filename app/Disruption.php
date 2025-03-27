<?php

class Disruption
{
    protected $impacts = [];
    protected $impacts_optimized = null;
    protected $id;
    protected $dateDayStart;
    protected $ligne;
    protected $relatedDisruption = null;
    protected $relatedDisruptions = null;

    public function __construct($id, $dateDayStart, $ligne) {
        $this->id = $id;
        $this->dateDayStart = $dateDayStart;
        $this->ligne = $ligne;
    }

    public function isInProgress() {
        $current = new DateTime();

        return $current > $this->getDateStart() && $current < $this->getDateEnd();
    }

    public function isPast() {

        return new DateTime() > $this->getDateEnd();
    }

    public function isInFuture() {

        return new DateTime() < $this->getDateStart();
    }

    public function getCurrentColorClass() {
        foreach($this->impacts as $i) {
            return $i->getColorClass();
        }
    }

    public function getDateEnd() {
        $impacts = $this->impacts;

        if(!is_null($this->impacts_optimized)) {
            $impacts = $this->impacts_optimized;
        }

        $dateEnd = null;
        foreach($impacts as $i) {
            if($i->getDateEnd() > $dateEnd) {
                $dateEnd = $i->getDateEnd();
            }
        }

        return $dateEnd;
    }

    public function getDateStart() {
        $impacts = $this->impacts;

        if(!is_null($this->impacts_optimized)) {
            $impacts = $this->impacts_optimized;
        }

        return end($impacts)->getDateStart();

        $dateStart = null;
        foreach($impacts as $i) {
            if(!$dateStart || $i->getDateStart() < $dateStart) {
                $dateStart = $i->getDateStart();
            }
        }

        return $dateStart;
    }

    public function isDurationEmpty() {
        return $this->getDateStart() == $this->getDateEnd();
    }

    public function getDuration() {
        $dateEnd = $this->getDateEnd();

        if($this->getDateEnd() > new DateTime()) {

            $dateEnd = new DateTime();
        }

        return $dateEnd->diff($this->getDateStart());
    }

    public function getDurationText() {

        return Impact::generateDurationText($this->getDuration());
    }

    public function getDurationMinutes() {

        return Impact::generateDurationMinutes($this->getDuration());
    }

    public function getDurationStatutMinutes($statut) {
        $dateStart = null;
        $dateEnd = null;
        $impacts = [];
        foreach($this->getImpactsOptimized() as $i) {
            if($i->getColorClass() != $statut) {
                continue;
            }
            $impacts[] = $i;
        }
        $dateStart = null;
        $dateEnd = null;
        $minutes = null;
        usort($impacts, function($a, $b) { return $a->getDurationMinutes() < $b->getDurationMinutes(); });
        foreach($impacts as $i) {
            if($dateStart >= $i->getDateStart() && $dateStart <= $i->getDateEnd()) {
                continue;
            }
            $minutes += $i->getDurationMinutes();
            if(!$dateStart || $i->getDateStart() < $dateStart) {
                $dateStart = $i->getDateStart();
            }
            if(!$dateEnd ||  $i->getDateEnd() > $dateEnd) {
                $dateEnd = $i->getDateEnd();
            }
        }
        return $minutes;
    }

    public function getOrigine() {
        $impacts = $this->impacts;

        if(!is_null($this->impacts_optimized)) {
            $impacts = $this->impacts_optimized;
        }

        if(end($impacts)) {

            return end($impacts)->getOrigine();
        }
    }

    public function getLigne() {

        return $this->ligne;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getImpacts() {

        return $this->impacts;
    }

    public function addImpact($impact) {
        if(isset($this->impacts[$impact->getId()])) {
            $impact->setDateCreation($this->impacts[$impact->getId()]->getDateCreation());
        }

        $dateKey = $impact->getLastUpdate()->format('Y-m-d H:i:s');
        if($impact->getLastUpdate() < $this->dateDayStart) {
            $dateKey =  $impact->getDateStart()->format('Y-m-d H:i:s');
        }
        $this->impacts[$dateKey.$impact->getId()] = $impact;

        $nextImpact = null;
        krsort($this->impacts);
        foreach($this->impacts as $impact) {
            if($nextImpact && $nextImpact->isSameImpact($impact) && $impact->getDateEnd() > $nextImpact->getDateEnd()) {
                $impact->setDateEnd($nextImpact->getDateStart()->format('Ymd\THis'));
            }

            if($nextImpact && $nextImpact->isSameImpact($impact) && $nextImpact->getDateStart() > $impact->getDateEnd()) {
                $nextImpact = $impact;
                continue;
            }

            if($nextImpact && $nextImpact->isSameImpact($impact) && $impact->getDateEnd() > $nextImpact->getDateEnd()) {
                $impact->setDateEnd($nextImpact->getDateEnd()->format('Ymd\THis'));
            }

            if($nextImpact && $nextImpact->isSameImpact($impact) && $impact->getDateEnd() > $nextImpact->getDateStart()) {
                $impact->setDateEnd($nextImpact->getDateStart()->format('Ymd\THis'));
            }

            if($impact->getDateStart() > $impact->getDateEnd()) {
                $impact->setDateStart($impact->getDateEnd()->format('Ymd\THis'));
            }

            $nextImpact = $impact;
        }
    }

    public function optimize() {
        $this->impacts_optimized = $this->impacts;
        foreach($this->impacts_optimized as $key => $impact) {
            if(!isset($this->impacts_optimized[$key])) {
                continue;
            }
            foreach($this->impacts_optimized as $keyOther => $otherImpact) {
                if($key == $keyOther) {
                    continue;
                }
                if($otherImpact->isInPeriod($impact->getDateStart()) && $impact->getSeverity() == $otherImpact->getSeverity() && $impact->getTitle() == $otherImpact->getTitle()) {
                    $otherImpact->setDateEnd($impact->getDateEnd()->format('Ymd\THis'));
                    $otherImpact->data->message = $impact->data->message;
                    unset($this->impacts_optimized[$key]);
                }
            }
        }
    }

    public function getImpactsOptimized() {
        if(is_null($this->impacts_optimized)) {
            $this->optimize();
            foreach($this->getRelatedDisruptions() as $disruption) {
                foreach($disruption->getImpactsOptimized() as $key => $impact) {
                    $this->impacts_optimized[$key] = $impact;
                }
            }
            uasort($this->impacts_optimized, function($a, $b) { return $a->getDateStart() < $b->getDateStart();  });
        }

        return $this->impacts_optimized;
    }
    public function getRelatedDisruption() {
        return $this->relatedDisruption;
    }
    public function setRelatedDisruption($disruption) {
        $this->relatedDisruption = $disruption;
    }
    public function getRelatedDisruptions() {
        if(is_null($this->relatedDisruptions)) {
            $this->relatedDisruptions = [];
            foreach($this->getLigne()->getDisruptions() as $disruption) {
                if($disruption->getId() == $this->getId()) {
                    continue;
                }
                if($this->getRelatedDisruption() && $this->getRelatedDisruption()->getId() == $disruption->getId()) {
                    continue;
                }
                if($this->isInSameTime($disruption) && $this->getOrigine() == $disruption->getOrigine()) {
                    $disruption->setRelatedDisruption($this->getRelatedDisruption() ? $this->getRelatedDisruption() : $this);
                    $this->relatedDisruptions[$disruption->getId()] = $disruption;
                    $this->relatedDisruptions = array_merge($this->relatedDisruptions, $disruption->getRelatedDisruptions());
                }
            }
        }

        return $this->relatedDisruptions;
    }

    public function isInSameTime($disruption) {
        $dateStart = clone $this->getDateStart();
        $dateEnd = clone $this->getDateEnd();

        if($dateStart >= $disruption->getDateStart() && $dateStart <= $disruption->getDateEnd()) {

            return true;
        }

        if($dateEnd >= $disruption->getDateStart() && $dateEnd <= $disruption->getDateEnd()) {

            return true;
        }

        if($dateStart <= $disruption->getDateStart() && $dateEnd >= $disruption->getDateEnd()) {

            return true;
        }

        return false;
    }

    public function getImpactsInPeriod($date) {
        $impacts = [];

        foreach($this->impacts as $impact) {
            if(!$impact->isInPeriod($date)) {
                continue;
            }
            $impacts[$impact->getDateCreation()->format('YMDHis').$impact->getId()] = $impact;
        }

        return $impacts;
    }
}

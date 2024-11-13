<?php

class Disruption
{
    protected $impacts = [];
    protected $impacts_optimized = null;
    protected $id;
    protected $dateDayStart;
    protected $ligne;

    public function __construct($id, $dateDayStart, $ligne) {
        $this->id = $id;
        $this->dateDayStart = $dateDayStart;
        $this->ligne = $ligne;
    }

    public function getDateEnd() {
        foreach($this->impacts as $i) {
            return $i->getDateEnd();
        }
    }

    public function getDateStart() {

        return end($this->impacts)->getDateStart();
    }

    public function getDuration() {
        $dateEnd = $this->getDateEnd();

        if($this->getDateEnd() > new DateTime()) {

            $dateEnd = new DateTime();
        }

        return $dateEnd->diff($this->getDateStart());
    }

    public function getCause() {
        foreach($this->impacts as $i) {
            return $i->getCause();
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
            if($nextImpact && $impact->getDateEnd() > $nextImpact->getDateEnd()) {
                $impact->setDateEnd($nextImpact->getDateStart()->format('Ymd\THis'));
            }

            if($nextImpact && $nextImpact->getDateStart() > $impact->getDateEnd()) {
                $nextImpact = $impact;
                continue;
            }

            if($nextImpact && $impact->getDateEnd() > $nextImpact->getDateEnd()) {
                $impact->setDateEnd($nextImpact->getDateEnd()->format('Ymd\THis'));
            }

            if($nextImpact && $impact->getDateEnd() > $nextImpact->getDateStart()) {
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
                    unset($this->impacts_optimized[$key]);
                }
            }
        }
    }

    public function getImpactsOptimized() {
        if(is_null($this->impacts_optimized)) {
            $this->optimize();
        }

        return $this->impacts_optimized;
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

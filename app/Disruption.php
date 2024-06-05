<?php

class Disruption
{
    protected $impacts = [];
    protected $id;
    protected $dateDayStart;

    public function __construct($id, $dateDayStart) {
        $this->id = $id;
        $this->dateDayStart = $dateDayStart;
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

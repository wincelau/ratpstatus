<?php

class Line
{
    protected $image = null;
    protected $name = null;
    protected $openingDateTime = null;
    protected $closingDateTime = null;
    protected $impacts = [];

    public function __construct($name) {
        $this->name = $name;
    }

    public function getId() {

        return strtoupper(str_replace(['Métro ', 'Ligne ' ], null, $this->getName()));
    }

    public function buildDisruptions($day) {
        $impactsByUniqTitle = [];
        foreach($this->impacts as $impact) {
            if(!preg_match('/(Métro| T[0-9]+)/', $impact->getTitle())) {
                continue;
            }
            $dateKey = $impact->getLastUpdate()->format('Y-m-d H:i:s');
            if($impact->getLastUpdate() < $day->getDateStart()) {
                $dateKey =  $impact->getDateStart()->format('Y-m-d H:i:s');
            }

            $impactsByUniqTitle[$impact->getUniqueTitle()][$dateKey.$impact->getId()] = $impact;
        }
        foreach($impactsByUniqTitle as $uniqName => $impacts) {
            $nextImpact = null;
            krsort($impacts);
            foreach($impacts as $impact) {
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
    }

    public function addImpact($impact) {
        $this->impacts[$impact->getId()] = $impact;
    }

    public function getName() {

        return $this->name;
    }

    public function setImage($image) {

        return $this->image;
    }

    public function setOpeningDateTime($dateTime) {

        return $this->openingDateTime = $dateTime;
    }

    public function getOpeningDateTime() {

        return $this->openingDateTime;
    }

    public function setClosingDateTime($dateTime) {

        return $this->closingDateTime = $dateTime;
    }

    public function getClosingDateTime() {

        return $this->closingDateTime;
    }

    public function hasOpeningHours() {

        return $this->openingDateTime != null && $this->closingDateTime != null;
    }

    public function isLigneOpen($date) {
        if(!$this->hasOpeningHours()) {

            return true;
        }

        return $date > $this->getOpeningDateTime() && $date < $this->getClosingDateTime();
    }

    public function getImpactsInPeriod($date) {
        $impacts = [];

        foreach($this->impacts as $impact) {
            if(!$impact->isInPeriod($date)) {
                continue;
            }
            $impacts[$impact->getId()] = $impact;
        }

        return $impacts;
    }

    public function getImpacts() {

        return $this->impacts;
    }
}

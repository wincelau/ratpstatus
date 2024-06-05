<?php

class Line
{
    protected $image = null;
    protected $name = null;
    protected $openingDateTime = null;
    protected $closingDateTime = null;
    protected $impacts = [];
    protected $disruptions = [];
    protected $dateDayStart;

    public function __construct($name, $dateDayStart) {
        $this->name = $name;
        $this->dateDayStart = $dateDayStart;
    }

    public function getId() {

        return strtoupper(str_replace(['Métro ', 'Ligne ' ], null, $this->getName()));
    }

    public function addImpact($impact) {
        if(isset($this->disruptions[$impact->getDistruptionId()])) {
            $disruption = $this->disruptions[$impact->getDistruptionId()];
        } else {
            $disruption = new Disruption($impact->getDistruptionId(), $this->dateDayStart);
            $this->disruptions[$disruption->getId()] = $disruption;
        }
        $disruption->addImpact($impact);
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

        foreach($this->disruptions as $disruption) {
            $impacts = array_merge($impacts, $disruption->getImpactsInPeriod($date));
        }

        return $impacts;
    }

    public function getImpacts() {
        $impacts = [];
        foreach($this->disruptions as $disruption) {
            $impacts = array_merge($impacts, $disruption->getImpacts());
        }

        return $impacts;
    }
}

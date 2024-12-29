<?php

class Line
{
    protected $image = null;
    protected $name = null;
    protected $mode = null;
    protected $openingDateTime = null;
    protected $closingDateTime = null;
    protected $disruptions = [];
    protected $dateDayStart;

    public function __construct($name, $mode, $dateDayStart) {
        $this->name = $name;
        $this->mode = $mode;
        $this->dateDayStart = $dateDayStart;
    }

    public function getId() {

        return strtoupper(str_replace(['Métro ', 'Ligne ' ], null, $this->getName()));
    }

    public function getDisruptions() {
        return $this->disruptions;
    }

    public function findDisruption($impact) {
        foreach($this->disruptions as $disruption) {
            $dateStart = $impact->getDateStart();
            $dateEnd = $impact->getDateEnd();
            $dateStart = $dateStart->modify('-10 minutes');
            $dateEnd = $dateEnd->modify('+10 minutes');
            if(!$impact->hasRealDisruptionId() && ($dateStart > $disruption->getDateEnd() || $dateEnd < $disruption->getDateStart())) {
                continue;
            }

            if($impact->getDistruptionId() != $disruption->getId()) {
                continue;
            }

            return $disruption;
        }

        return null;
    }

    public function addImpact($impact) {
        if($impact->getDateEnd() < $this->getOpeningDateTime()) {
            return;
        }
        $disruption = $this->findDisruption($impact);

        if(!$disruption) {
            $disruption = new Disruption($impact->getDistruptionId(), $this->dateDayStart, $this);
            $this->disruptions[] = $disruption;
        }

        $impact->setLigne($this);
        $disruption->addImpact($impact);
    }

    public function getName() {

        return $this->name;
    }

    public function getMode() {

        return $this->mode;
    }

    public function setImage($image) {

        $this->image = $image;
    }

    public function getImage() {

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

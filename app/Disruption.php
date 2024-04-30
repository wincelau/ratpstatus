<?php

class Disruption
{
    public $data = null;

    const CAUSE_TRAVAUX = 'TRAVAUX';
    const CAUSE_PERTURBATION = 'PERTURBATION';
    const SEVERITY_PERTURBEE = 'PERTURBEE';
    const SEVERITY_BLOQUANTE = 'BLOQUANTE';
    const SEVERITY_INFORMATION = 'INFORMATION';

    public function __construct($data) {
        $this->data = $data;
    }

    public function getId() {

        return $this->data->id;
    }

    public function getTitle() {

        return $this->data->title;
    }

    public function getCause() {

        return $this->data->cause;
    }

    public function getSeverity() {

        return $this->data->severity;
    }

    public function getLignes() {

        return isset($this->data->lines) ? $this->data->lines : [];
    }

    public function isToExclude() {
        if($this->getSeverity() == self::SEVERITY_INFORMATION) {
            return true;
        }

        if(preg_match('/(modifications horaires|horaires modifiés)/', $this->getTitle())) {
            return true;
        }

        if(preg_match('/Modification de desserte/', $this->getTitle())) {
            return true;
        }

        if(preg_match('/train court/', $this->getTitle())) {
            return true;
        }

        if($this->getCause() == self::CAUSE_TRAVAUX && $this->getSeverity() == self::SEVERITY_PERTURBEE && preg_match('/Ligne D/', $this->getTitle())) {
            return true;
        }

        if(!count($this->getLignes())) {
            return true;
        }

        return false;
    }

    public function getDateStart() {

        return $this->data->applicationPeriods[0]->begin;
    }

    public function getDateEnd() {

        return $this->data->applicationPeriods[0]->end;
    }

    public function setDateEnd($date) {

        return $this->data->applicationPeriods[0]->end = $date;
    }

    public function isInPeriod(DateTime $date) {
        foreach($this->data->applicationPeriods as $period) {
            if ($date->format('Ymd\THis') >= $period->begin && $date->format('Ymd\THis') <= $period->end) {

                return true;
            }
        }
        return false;
    }
}

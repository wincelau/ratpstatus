<?php

class Disruption
{
    public $data = null;

    const CAUSE_TRAVAUX = 'TRAVAUX';
    const CAUSE_PERTURBATION = 'PERTURBATION';
    const SEVERITY_PERTURBEE = 'PERTURBEE';
    const SEVERITY_BLOQUANTE = 'BLOQUANTE';
    const SEVERITY_INFORMATION = 'INFORMATION';

    const TYPE_PERTURBATION_PARTIELLE = 'PERTURBATION_PARTIELLE';
    const TYPE_PERTURBATION = 'PERTURBATION';
    const TYPE_PERTURBATION_FORTE = 'PERTURBATION_FORTE';
    const TYPE_INTERRUPTION_PARTIELLE = 'INTERRUPTION_PARTIELLE';
    const TYPE_INTERRUPTION_TOTALE = 'INTERRUPTION_TOTALE';
    const TYPE_STATIONS_NON_DESSERVIES = 'STATIONS_NON_DESSERVIES';
    const TYPE_TRAINS_SUPPRIMES = 'CHANGEMENT_HORAIRES';
    const TYPE_CHANGEMENT_HORAIRES = 'TRAINS_SUPPRIMES';
    const TYPE_CHANGEMENT_COMPOSITION = 'CHANGEMENT_COMPOSITION';
    const TYPE_AUCUNE = 'AUCUNE';

    protected $dateStart = null;
    protected $dateEnd = null;

    public function __construct($data) {
        $this->data = $data;
        foreach($this->data->applicationPeriods as $period) {
            $this->dateEnd = $period->end;
            if($this->dateStart && $period->begin > $this->dateStart && $period->begin < $this->dateEnd) {
                continue;
            }
            $this->dateStart = $period->begin;
        }
    }

    public function getId() {

        return $this->data->id;
    }

    public function getTitle() {

        return $this->data->title;
    }

    public function getUniqueTitle() {
        return str_replace([" - Reprise progressive / trafic reste très perturbé", " - Reprise progressive / trafic reste perturbé", " - Arrêt non desservi", " - Reprise progressive"," - Stationnement prolongé", " - Trafic interrompu", " - Trafic perturbé", " - Trafic très perturbé", " - Trains stationnent", " - Train stationne"], "", $this->getTitle());
    }

    public function getSuggestionOrigine() {
        if(preg_match('/Métro/', $this->getTitle())) {

            return preg_replace('/ - .*$/', '', preg_replace('/^[^:]*: /', '', $this->getTitle()));
        }

        return null;
    }

    public function getMessage() {

        return $this->data->message;
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

        if(preg_match('/(Alerte orages|Alerte forte pluies et orages)/', $this->getTitle())) {
            return true;
        }

        if(preg_match('/Modifications de compositions/', $this->getTitle())) {
            return true;
        }

        if(preg_match('/Adaptation/', $this->getTitle())) {
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

        return DateTime::createFromFormat('Ymd\THis', $this->dateStart);
    }

    public function setDateStart($date) {

        return $this->dateStart = $date;
    }

    public function getDateEnd() {

        return DateTime::createFromFormat('Ymd\THis', $this->dateEnd);
    }

    public function setDateEnd($date) {

        return $this->dateEnd = $date;
    }

    public function isInPeriod(DateTime $date) {

        return $date >= $this->getDateStart() && $date <= $this->getDateEnd();
    }

    public function getMessagePlainText() {
        return str_replace('"', '', html_entity_decode(strip_tags(str_replace("<br>", "\n", $this->getMessage()))));
    }

    public function getLastUpdate() {
        return DateTime::createFromFormat('Ymd\THis', $this->data->lastUpdate);
    }
}

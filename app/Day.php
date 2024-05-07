<?php

class Day
{
    protected $dateStart = null;
    protected $dateEnd = null;
    protected $disruptions = [];

    public function __construct($datetime) {
        $this->dateStart = (new DateTime((new DateTime($datetime))->modify('-3 hours')->format('Y-m-d').' 05:00:00'));

        $this->dateEnd = (clone $this->dateStart)->modify('+21 hours');
        $this->load();
    }

    protected function load() {
        $files = $this->getFiles();
        $this->disruptions = [];
        $previousDisruptions = [];
        foreach($files as $filename) {
            $file = new File($filename);
            $currentDisruptions = [];
            foreach($file->getDistruptions() as $disruption) {
                $this->disruptions[$disruption->getId()] = $disruption;
                $currentDisruptions[$disruption->getId()] = $disruption;
            }
            foreach($previousDisruptions as $previousDisruption) {
                if(!isset($currentDisruptions[$previousDisruption->getId()]) && $this->disruptions[$previousDisruption->getId()]->getDateEnd() > $file->getDate()) {
                    $this->disruptions[$previousDisruption->getId()]->setDateEnd($file->getDate()->format('Ymd\THis'));
                }
            }
            $previousDisruptions = $currentDisruptions;
        }
    }

    protected function getFiles() {
        $files = [];
        foreach(scandir(__DIR__.'/../datas/json') as $file) {
            if(!is_file(__DIR__.'/../datas/json/'.$file)) {
                continue;
            }
            $dateFile = DateTime::createFromFormat('YmdHis', explode('_', $file)[0]);
            if($dateFile < $this->getDateStart()) {
                continue;
            }
            if($dateFile > $this->getDateEnd()) {
                continue;
            }
            $files[$dateFile->format('YmdHis')] = $file;
        }

        ksort($files);

        return $files;
    }

    public function getDateStartYesterday() {

        return (clone $this->getDateStart())->modify('-1 day');
    }

    public function getDateStartTomorrow() {

        return (clone $this->getDateStart())->modify('+1 day');
    }

    public function getDateStart() {
        return $this->dateStart;
    }

    public function getDateEnd() {
        return $this->dateEnd;
    }

    public function isToday() {

        return $this->getDateStart()->format('Ymd') == date('Ymd');
    }

    public function getDistruptionsByLigne($ligne) {
        $disruptions = [];

        foreach($this->getDistruptions() as $disruption) {
            if(!preg_match('/(^| )'.str_replace('é', 'e', $ligne).'[^0-9A-Z]+/', str_replace('é', 'e', $disruption->getTitle()))) {
                continue;
            }

            $disruptions[$disruption->getId()] = $disruption;
        }

        return $disruptions;
    }

    public function getDistruptionsByLigneInPeriod($ligne, $date) {
        $disruptions = [];

        foreach($this->getDistruptionsByLigne($ligne) as $disruption) {
            if(!$disruption->isInPeriod($date)) {
                continue;
            }
            $disruptions[$disruption->getId()] = $disruption;
        }

        return $disruptions;
    }

    public function getDistruptions() {

        return $this->disruptions;
    }

    public function getColorClass($nbMinutes, $ligne) {
        $date = (clone $this->getDateStart())->modify("+ ".$nbMinutes." minutes");
        if($date > (new DateTime())) {
            return 'e';
        }
        $cssClass = 'ok';
        foreach($this->getDistruptionsByLigneInPeriod($ligne, $date) as $disruption) {
            if($disruption->getCause() == Disruption::CAUSE_PERTURBATION && $disruption->getSeverity() == Disruption::SEVERITY_BLOQUANTE) {
                return 'bq';
            }
            if($cssClass == 'ok' && $disruption->getCause() == Disruption::CAUSE_TRAVAUX) {
                $cssClass = 'tx';
            }
            if($disruption->getCause() == Disruption::CAUSE_PERTURBATION && $disruption->getSeverity() == Disruption::SEVERITY_PERTURBEE) {
                $cssClass = 'pb';
            }
        }

        return $cssClass;
    }

    public function getInfo($nbMinutes, $ligne) {
        $date = (clone $this->getDateStart())->modify("+ ".$nbMinutes." minutes");
        if($date > (new DateTime())) {
            return null;
        }
        $message = null;
        foreach($this->getDistruptionsByLigneInPeriod($ligne, $date) as $disruption) {
            $message .= ";%".$disruption->getId()."%";
        }

        if($message) {
            return $message;
        }

        return "%ok%";
    }

    public function toJson() {
        $json = [];
        $doublons = [];
        foreach($this->getDistruptions() as $disruption) {
            $json[$disruption->getId()] = "# ".$disruption->getTitle()."\n\n".$disruption->getMessagePlainText();
        }

        return json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function toCsv() {
        $csv = "Date,Ligne,Type de perturbation,Api disruption id\n";
        $now = new DateTime();
        for($i = 0; $i < 1260; $i=$i+2) {
            $date = (clone $this->getDateStart())->modify("+ ".$i." minutes");
            if($date > $now) {
                return $csv;
            }
            foreach(Config::getLignes() as $mode => $lignes) {
                foreach($lignes as $ligne => $ligneImg) {
                    $csv .= $date->format('Y-m-d H:i:s').",".str_replace(['Métro ', 'Ligne ' ], ['M', 'L'], $ligne).",".strtoupper($this->getColorClass($i, $ligne)).",".str_replace(["%ok%", "%", ";"], ["", "", "|"], preg_replace('/^;/', '', $this->getInfo($i, $ligne)))."\n";
                }
            }
        }

        return $csv;
    }

}

<?php

class Day
{
    protected $dateStart = null;
    protected $dateEnd = null;
    protected $disruptions = [];
    protected $opening_hours = [];

    public function __construct($date) {
        if($date == null) {
            $date = (new DateTime())->modify('-3 hours')->format('Y-m-d');
        }
        $this->dateStart = new DateTime($date.' 04:00:00');
        $this->dateEnd = (clone $this->dateStart)->modify('+23 hours');
        $this->loadOpeningHours();
        $this->loadDisruptions();
    }

    protected function loadDisruptions() {
        $files = $this->getDistruptionsFiles();
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
        $disruptionsByUniqTitle = [];
        foreach($this->disruptions as $disruption) {
            if(!preg_match('/(Métro| T[0-9]+)/', $disruption->getTitle())) {
                continue;
            }
            $dateKey = $disruption->getLastUpdate()->format('Y-m-d H:i:s');
            if($disruption->getLastUpdate() < $this->getDateStart()) {
                $dateKey =  $disruption->getDateStart()->format('Y-m-d H:i:s');
            }

            $disruptionsByUniqTitle[$disruption->getUniqueTitle()][$dateKey.$disruption->getId()] = $disruption;
        }
        foreach($disruptionsByUniqTitle as $uniqName => $disruptions) {
            $nextDisruption = null;
            krsort($disruptions);
            foreach($disruptions as $disruption) {
                if($nextDisruption && $disruption->getDateEnd() > $nextDisruption->getDateEnd()) {
                    $disruption->setDateEnd($nextDisruption->getDateStart()->format('Ymd\THis'));
                }

                if($nextDisruption && $nextDisruption->getDateStart() > $disruption->getDateEnd()) {
                    $nextDisruption = $disruption;
                    continue;
                }

                if($nextDisruption && $disruption->getDateEnd() > $nextDisruption->getDateEnd()) {
                    $disruption->setDateEnd($nextDisruption->getDateEnd()->format('Ymd\THis'));
                }

                if($nextDisruption && $disruption->getDateEnd() > $nextDisruption->getDateStart()) {
                    $disruption->setDateEnd($nextDisruption->getDateStart()->format('Ymd\THis'));
                }

                if($disruption->getDateStart() > $disruption->getDateEnd()) {
                    $disruption->setDateStart($disruption->getDateEnd()->format('Ymd\THis'));
                }

                $nextDisruption = $disruption;
            }
        }
    }

    protected function loadOpeningHours() {
        $this->opening_hours = [];
        $data = null;
        foreach(scandir(__DIR__.'/../datas/jsonlines') as $file) {
            if(!is_file(__DIR__.'/../datas/jsonlines/'.$file)) {
                continue;
            }
            if(explode('_', $file)[0] < (clone $this->getDateStart())->modify('-2 hours')->format('YmdHis')) {
                continue;
            }
            if(explode('_', $file)[0] > (clone $this->getDateEnd())->modify('-3 hours')->format('YmdHis')) {
                continue;
            }
            $data = json_decode(file_get_contents(__DIR__.'/../datas/jsonlines/'.$file));

            break;
        }

        if(is_null($data))  {
            return;
        }

        foreach($data->lines as $line) {
            $line->name = strtoupper($line->name);
            $this->opening_hours[$line->name] = new stdClass();
            $this->opening_hours[$line->name]->opening_date = DateTime::createFromFormat('YmdHis', $this->getDateStart()->format('Ymd').$line->opening_time);
            $this->opening_hours[$line->name]->closing_date = DateTime::createFromFormat('YmdHis', $this->getDateEnd()->format('Ymd').$line->closing_time);
        }
    }

    protected function getDistruptionsFiles() {
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

    public function isTomorrow() {
        return $this->getDateStartTomorrow() > (new DateTime())->modify('+1 hours');
    }

    public function isTodayTomorrow() {
        return date_format($this->getDateStartTomorrow(), "Ymd") == date_format((new DateTime()), "Ymd");
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

    public function isLigneOpen($ligne, $date) {
        $shortLine = str_replace(['Métro ', 'Ligne ' ], null, $ligne);
        if(!isset($this->opening_hours[$shortLine])) {

            return true;
        }

        $hours = $this->opening_hours[$shortLine];

        return $date > $hours->opening_date && $date < $hours->closing_date;
    }

    public function getColorClass($nbMinutes, $ligne) {
        $date = (clone $this->getDateStart())->modify("+ ".$nbMinutes." minutes");
        if($date > (new DateTime())) {
            return 'e';
        }
        if(!$this->isLigneOpen($ligne, $date)) {
            return 'no';
        }
        $cssClass = 'ok';
        foreach($this->getDistruptionsByLigneInPeriod($ligne, $date) as $disruption) {
            if($disruption->getCause() == Impact::CAUSE_PERTURBATION && $disruption->getSeverity() == Impact::SEVERITY_BLOQUANTE) {
                return 'bq';
            }
            if($cssClass == 'ok' && $disruption->getCause() == Impact::CAUSE_TRAVAUX) {
                $cssClass = 'tx';
            }
            if($disruption->getCause() == Impact::CAUSE_PERTURBATION && $disruption->getSeverity() == Impact::SEVERITY_PERTURBEE) {
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
        if(!$this->isLigneOpen($ligne, $date)) {
            return '%no%';
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
        for($i = 0; $i < 1380; $i=$i+2) {
            $date = (clone $this->getDateStart())->modify("+ ".$i." minutes");
            foreach(Config::getLignes() as $mode => $lignes) {
                foreach($lignes as $ligne => $ligneImg) {
                    $statut = strtoupper($this->getColorClass($i, $ligne));
                    if(in_array($statut, ['NO', 'E'])) {
                        continue;
                    }
                    $csv .= $date->format('Y-m-d H:i:s').",".str_replace(['Métro ', 'Ligne ' ], ['M', 'L'], $ligne).",".$statut .",".str_replace(["%ok%", "%", ";"], ["", "", "|"], preg_replace('/^;/', '', $this->getInfo($i, $ligne)))."\n";
                }
            }
        }

        return $csv;
    }

}

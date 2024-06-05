<?php

class Day
{
    protected $dateStart = null;
    protected $dateEnd = null;
    protected $lignes = [];
    protected $lastFile = null;

    public function __construct($date) {
        if($date == null) {
            $date = (new DateTime())->modify('-3 hours')->format('Y-m-d');
        }
        $this->dateStart = new DateTime($date.' 04:00:00');
        $this->dateEnd = (clone $this->dateStart)->modify('+23 hours');
        $this->loadLines();
        $this->loadOpeningHours();
        $this->loadDisruptions();
    }

    protected function loadLines() {
        foreach(Config::getLignes() as $mode => $lignes) {
            foreach($lignes as $ligneName => $ligneImg) {
                $ligne = new Line($ligneName, $this->dateStart);
                $ligne->setImage($ligneImg);
                $this->lignes[$ligne->getId()] = $ligne;
            }
        }
    }

    protected function loadOpeningHours() {
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

        foreach($data->lines as $dataLine) {
            if(!isset($this->lignes[strtoupper($dataLine->name)])) {
                continue;
            }
            $ligne = $this->lignes[strtoupper($dataLine->name)];
            $ligne->setOpeningDateTime(DateTime::createFromFormat('YmdHis', $this->getDateStart()->format('Ymd').$dataLine->opening_time));
            $ligne->setClosingDateTime(DateTime::createFromFormat('YmdHis', $this->getDateEnd()->format('Ymd').$dataLine->closing_time));
        }
    }

    protected function addImpact($impact) {
        if(!isset($this->lignes[$impact->getLigneId()])) {
            return;
        }

        $this->lignes[$impact->getLigneId()]->addImpact($impact);
    }

    protected function loadDisruptions() {
        $files = $this->getDistruptionsFiles();
        $previousDisruptions = [];
        foreach($files as $file) {
            $file = new File($file);
            $currentDisruptions = [];
            foreach($file->getDistruptions() as $disruption) {
                $this->addImpact($disruption);
                $currentDisruptions[$disruption->getId()] = $disruption;
            }
            foreach($previousDisruptions as $previousDisruption) {
                if(!isset($currentDisruptions[$previousDisruption->getId()]) && $previousDisruption->getDateEnd() > $file->getDate()) {
                    $previousDisruption->setDateEnd($file->getDate()->format('Ymd\THis'));
                }
            }
            $previousDisruptions = $currentDisruptions;
        }
        if(isset($file)) {
            $this->lastFile = $file;
        }
    }

    protected function getDistruptionsFiles() {
        $files = [];
        foreach(glob("{".__DIR__."/../datas/json/*.json,".__DIR__."/../datas/json/*/*.json}", GLOB_BRACE) as $file) {
            $dateFile = DateTime::createFromFormat('YmdHis', explode('_', basename($file))[0]);
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

    public function getLastFile() {

        return $this->lastFile;
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

    public function getColorClass($nbMinutes, $ligneName) {
        $ligne = $this->lignes[strtoupper(str_replace(['Métro ', 'Ligne ' ], null, $ligneName))];
        $date = (clone $this->getDateStart())->modify("+ ".$nbMinutes." minutes");
        if($date > (new DateTime())) {
            return 'e';
        }
        if(!$ligne->isLigneOpen($date)) {
            return 'no';
        }
        $cssClass = 'ok';
        foreach($ligne->getImpactsInPeriod($date) as $impact) {
            if($impact->getCause() == Impact::CAUSE_PERTURBATION && $impact->getSeverity() == Impact::SEVERITY_BLOQUANTE) {
                return 'bq';
            }
            if($cssClass == 'ok' && $impact->getCause() == Impact::CAUSE_TRAVAUX) {
                $cssClass = 'tx';
            }
            if($impact->getCause() == Impact::CAUSE_PERTURBATION && $impact->getSeverity() == Impact::SEVERITY_PERTURBEE) {
                $cssClass = 'pb';
            }
        }

        return $cssClass;
    }

    public function getInfo($nbMinutes, $ligneName) {
        $ligne = $this->lignes[strtoupper(str_replace(['Métro ', 'Ligne ' ], null, $ligneName))];
        $date = (clone $this->getDateStart())->modify("+ ".$nbMinutes." minutes");
        if($date > (new DateTime())) {
            return null;
        }
        if(!$ligne->isLigneOpen($date)) {
            return '%no%';
        }
        $message = null;
        foreach($ligne->getImpactsInPeriod($date) as $impact) {
            $message .= ";%".$impact->getId()."%";
        }

        if($message) {
            return $message;
        }

        return "%ok%";
    }

    public function toJson() {
        $json = [];
        $doublons = [];
        foreach($this->lignes as $ligne) {
            foreach($ligne->getImpacts() as $disruption) {
                $json[$disruption->getId()] = "# ".$disruption->getTitle()."\n\n".$disruption->getMessagePlainText();
            }
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
                    $csv .= $date->format('Y-m-d H:i:s').",".str_replace(['Métro ', 'Ligne ' ], ['M', 'L'], $ligne).",".$statut .",".str_replace(["%ok%", "%", ";"], ["", "", "|"], preg_replace('/^;/', '', $this->getInfo($i, $ligne)))."\n";                }
            }
        }

        return $csv;
    }

}

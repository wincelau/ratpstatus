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
                if(isset($disruptions[$disruption->getId()])) {
                    $this->disruptions[$disruption->getId()] = $disruption;
                    $currentDisruptions[$disruption->getId()] = $disruption;
                    continue;
                }
                $this->disruptions[$disruption->getId()] = $disruption;
                $currentDisruptions[$disruption->getId()] = $disruption;
            }
            foreach($previousDisruptions as $previousDisruption) {
                if(!isset($currentDisruptions[$previousDisruption->getId()]) && $this->disruptions[$previousDisruption->getId()]->getDateEnd() > $file->getDate()->format('Ymd\THis')) {
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

    public function getDistruptionsByLigneInPeriod($ligne, $date) {
        $disruptions = [];

        foreach($this->getDistruptions() as $disruption) {
            if(!preg_match('/(^| )'.$ligne.'[^0-9A-Z]+/', $disruption->getTitle())) {
                continue;
            }
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
            return strip_tags($message);
        }

        return "%ok%";
    }

    public function getModeLibelles() {
        return ["metros" => "Ⓜ️ <span>Métros</span>", "trains" => "🚆 <span>RER/Trains</span>", "tramways" => "🚈 <span>Tramways</span>"];
    }

    public function getLignes() {
        $baseUrlLogo = "/images/lignes/";

        return [
            "metros" => [
                "Métro 1" => $baseUrlLogo."/1.svg",
                "Métro 2" => $baseUrlLogo."/2.svg",
                "Métro 3" => $baseUrlLogo."/3.svg",
                "Métro 3B" => $baseUrlLogo."/3b.svg",
                "Métro 4" => $baseUrlLogo."/4.svg",
                "Métro 5" => $baseUrlLogo."/5.svg",
                "Métro 6" => $baseUrlLogo."/6.svg",
                "Métro 7" => $baseUrlLogo."/7.svg",
                "Métro 7B" => $baseUrlLogo."/7b.svg",
                "Métro 8" => $baseUrlLogo."/8.svg",
                "Métro 9" => $baseUrlLogo."/9.svg",
                "Métro 10" => $baseUrlLogo."/10.svg",
                "Métro 11" => $baseUrlLogo."/11.svg",
                "Métro 12" => $baseUrlLogo."/12.svg",
                "Métro 13" => $baseUrlLogo."/13.svg",
                "Métro 14" => $baseUrlLogo."/14.svg",
            ],
            "trains" => [
                "Ligne A" => $baseUrlLogo."/a.svg",
                "Ligne B" => $baseUrlLogo."/b.svg",
                "Ligne C" => $baseUrlLogo."/c.svg",
                "Ligne D" => $baseUrlLogo."/d.svg",
                "Ligne E" => $baseUrlLogo."/e.svg",
                "Ligne H" => $baseUrlLogo."/h.svg",
                "Ligne J" => $baseUrlLogo."/j.svg",
                "Ligne K" => $baseUrlLogo."/k.svg",
                "Ligne L" => $baseUrlLogo."/l.svg",
                "Ligne N" => $baseUrlLogo."/n.svg",
                "Ligne P" => $baseUrlLogo."/p.svg",
                "Ligne R" => $baseUrlLogo."/r.svg",
                "Ligne U" => $baseUrlLogo."/u.svg",
            ],
            "tramways" => [
                "T1" => $baseUrlLogo."/t1.svg",
                "T2" => $baseUrlLogo."/t2.svg",
                "T3A" => $baseUrlLogo."/t3a.svg",
                "T3B" => $baseUrlLogo."/t3b.svg",
                "T4" => $baseUrlLogo."/t4.svg",
                "T5" => $baseUrlLogo."/t5.svg",
                "T6" => $baseUrlLogo."/t6.svg",
                "T7" => $baseUrlLogo."/t7.svg",
                "T8" => $baseUrlLogo."/t8.svg",
                "T9" => $baseUrlLogo."/t9.svg",
                "T10" => $baseUrlLogo."/t10.svg",
                "T11" => $baseUrlLogo."/t11.svg",
                "T12" => $baseUrlLogo."/t12.svg",
                "T13" => $baseUrlLogo."/t13.svg",
            ]
        ];
    }

    public function toJson() {
        $json = [];
        $doublons = [];
        foreach($this->getDistruptions() as $disruption) {
            $uniqueKey = $disruption->getTitle().$disruption->getSeverity().$disruption->getCause().implode(',', $disruption->getLignes()).$disruption->getDateStart();
            if(isset($doublons[$uniqueKey])) {
                //$json[$doublons[$uniqueKey]] = null;
            }
            $doublons[$uniqueKey] = $disruption->getId();
            $json[$disruption->getId()] = "# ".$disruption->getTitle()."\n\n".str_replace('"', '', html_entity_decode(strip_tags($disruption->getMessage())));
        }

        return json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

}

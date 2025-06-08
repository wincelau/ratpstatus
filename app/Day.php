<?php

class Day extends Period
{
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
                $ligne = new Line($ligneName, $mode, $this->dateStart);
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

        if(!is_null($data))  {
            foreach($data->lines as $dataLine) {
                if(!isset($this->lignes[strtoupper($dataLine->name)])) {
                    continue;
                }
                if($dataLine->opening_time == "000000") {
                    continue;
                }
                $ligne = $this->lignes[strtoupper($dataLine->name)];
                $ligne->setOpeningDateTime(DateTime::createFromFormat('YmdHis', $this->getDateStart()->format('Ymd').$dataLine->opening_time));
                if($dataLine->closing_time > "120000") {
                    $ligne->setClosingDateTime(DateTime::createFromFormat('YmdHis', $this->getDateStart()->format('Ymd').$dataLine->closing_time));
                } else {
                    $ligne->setClosingDateTime(DateTime::createFromFormat('YmdHis', $this->getDateEnd()->format('Ymd').$dataLine->closing_time));
                }
            }
        }

        $config = Config::getOpeningTime();
        foreach($this->lignes as $ligne) {
            $configLine = [];
            if(isset($config[$ligne->getMode()])) {
                $configLine = array_merge($configLine, $config[$ligne->getMode()]);
            }
            if(isset($config[$ligne->getName()])) {
                $configLine = array_merge($configLine, $config[$ligne->getName()]);
            }

            if(!count($configLine)) {
                continue;
            }
            $configLineKey = '*';
            if(isset($configLine['(Fri|Sat)']) && preg_match('/^(Fri|Sat)$/', $this->getDateStart()->format('D'))) {
                $configLineKey = '(Fri|Sat)';
            }
            if(!is_null($ligne->getOpeningDateTime()) && $configLine[$configLineKey][0] == 'API') {
                continue;
            }
            if(is_null($ligne->getOpeningDateTime()) && $configLine[$configLineKey][0] == 'API') {
                $configLineKey = 'FALLBACK';
            }

            $ligne->setOpeningDateTime(DateTime::createFromFormat('Y-m-d H:i:s', $this->getDateStart()->format('Y-m-d')." ".$configLine[$configLineKey][0]));
            $ligne->setClosingDateTime(DateTime::createFromFormat('Y-m-d H:i:s', $this->getDateEnd()->format('Y-m-d')." ".$configLine[$configLineKey][1]));
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
        $ids = $this->getDistruptionsIds();
        $previousDisruptions = [];
        foreach($files as $file) {
            $file = new File($file);
            $currentDisruptions = [];
            foreach($file->getImpacts() as $impact) {
                if(!isset($impact->data->disruption_id) && isset($ids[$impact->getId()])) {
                    $impact->data->disruption_id = $ids[$impact->getId()];
                }
                $this->addImpact($impact);
                $currentDisruptions[$impact->getId()] = $impact;
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
        foreach($this->lignes as $ligne) {
            foreach($ligne->getDisruptions() as $disruption) {
                $disruption->getImpactsOptimized();
            }
        }
    }

    protected function getDistruptionsIds() {
        $idsCsvFile = __DIR__.'/../datas/disruptions_ids/'.$this->getDateStart()->format('Ymd').'_disruptions_ids.csv';
        if(!is_file($idsCsvFile)) {
            return [];
        }

        $ids = [];
        foreach(file($idsCsvFile) as $line) {
            $line = str_replace("\n", "", $line);
            if(!$line) {
                continue;
            }
            $ids[explode(",", $line)[0]] = explode(",", $line)[1];
        }

        return $ids;
    }

    protected function getDistruptionsFiles() {
        $files = [];
        $previousFilesize = null;
        foreach(glob("{".__DIR__."/../datas/json/*/*.json,".__DIR__."/../datas/json/*/*.json.gz}", GLOB_BRACE) as $file) {
            if($previousFilesize && filesize($file) == $previousFilesize) {
                //continue;
            }
            $dateFile = DateTime::createFromFormat('YmdHis', explode('_', basename($file))[0]);
            if($dateFile < $this->getDateStart()) {
                continue;
            }
            if($dateFile > $this->getDateEnd()) {
                continue;
            }
            $files[$dateFile->format('YmdHis')] = $file;
            $previousFilesize = filesize($file);
        }

        ksort($files);

        return $files;
    }

    public function getDateFormat() {

        return 'Ymd';
    }

    public function getDateStartLabel() {

        return $this->getDateStart()->format("d/m/Y");
    }

    public function getDateEnd() {
        return $this->dateEnd;
    }

    public function getLastFile() {

        return $this->lastFile;
    }

    public function getDatePrevious() {

        return (clone $this->getDateStart())->modify('-1 day');
    }

    public function getDateNext() {

        return (clone $this->getDateStart())->modify('+1 day');
    }

    public function getDisruptions($mode) {
        $disruptions = [];
        foreach($this->lignes as $ligne) {
            if(!isset(Config::getLignes()[$mode][$ligne->getName()])) {
                continue;
            }
            $disruptions = array_merge($disruptions, $ligne->getDisruptions());
        }
        $disruptions = array_filter($disruptions, function ($a) { return !$a->isDurationEmpty(); });
        uasort($disruptions, function($a, $b) { return $a->getDateStart() < $b->getDateStart(); });
        return $disruptions;
    }
    public function isSameColorClassForFive($nbMinutes, $ligneName) {
        $dateInf = (clone $this->getDateStart())->modify("+ ".($nbMinutes + 5)." minutes");
        $dateSup = (clone $this->getDateStart())->modify("+ ".max($nbMinutes - 5, 0)." minutes");
        if($dateInf > new DateTime() && $dateSup < new DateTime()) {
            return false;
        }
        $class = null;
        for($i = 0; $i < 5; $i++) {
            $newClass = $this->getColorClass($nbMinutes + ($i*2), $ligneName);
            if($class && $newClass && $newClass != $class) {
                return false;
            }
            $class = $newClass;
        }
        return true;
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

    public function getInfo($nbMinutes, $ligneName, $length = 1) {
        $ligne = $this->lignes[strtoupper(str_replace(['Métro ', 'Ligne ' ], null, $ligneName))];
        $date = (clone $this->getDateStart())->modify("+ ".$nbMinutes." minutes");
        if($date > (new DateTime())) {
            return null;
        }
        if(!$ligne->isLigneOpen($date)) {
            return null;
        }
        $ids = [];
        $messages = [];
        for($i=1; $i <= $length; $i++) {
            foreach($ligne->getImpactsInPeriod($date) as $impact) {
                $origine = $impact->getOrigine();
                $messages[$origine] = $origine;
                $id = explode(":", $impact->getDistruptionId())[1];
                $ids[$id] = $id;
            }
            $date = $date->modify("+ 2 minutes");
        }

        return [$ids, $messages];
    }

    public function getCurrentStatutsCount($mode) {
        if(!$this->isToday()) {
            return [];
        }
        $statuts = [];
        $currentStatut = null;
        foreach(Config::getLignes()[$mode] as $ligne => $ligneImg) {
            for($i = 0; $i < 1380; $i=$i+2) {
                $newStatut = $this->getColorClass($i, $ligne);
                if($currentStatut && $newStatut == 'e') {
                    if(!isset($statuts[$currentStatut])) {
                        $statuts[$currentStatut] = 0;
                    }
                    $statuts[$currentStatut] += 1;
                    break;
                }
                $currentStatut = $newStatut;
            }
        }
        arsort($statuts);

        return $statuts;
    }

    public function getPourcentages($mode) {
        $repartitions = [$mode => ["OK" => 0, "PB" => 0, "BQ" => 0, "TX" => 0]];
        for($i = 0; $i < 1380; $i=$i+2) {
            foreach(Config::getLignes()[$mode] as $ligne => $ligneImg) {
                if(!isset($repartitions[$ligne])) {
                    $repartitions[$ligne] = ["OK" => 0, "PB" => 0, "BQ" => 0, "TX" => 0];
                }
                $statut = strtoupper($this->getColorClass($i, $ligne));
                if(in_array($statut, ['NO', 'E'])) {
                    continue;
                }
                $repartitions[$ligne][$statut]++;
                $repartitions[$mode][$statut]++;
            }
        }
        $pourcentage = [];
        foreach($repartitions as $key => $repartition) {
            $total = array_sum($repartition);
            $pourcentages[$key] = array_map(function($a) use ($total) { return $total > 0 ? round($a / $total * 100, 2) : 0; }, $repartition);

            $pourcentages[$key]["OK"] = round(100 - $pourcentages[$key]["PB"] - $pourcentages[$key]["BQ"] - $pourcentages[$key]["TX"], 2);
        }

        return $pourcentages;
    }

    public function fopenIncidentsCsvFile() {
        $handle = fopen("php://memory", "rw");
        fwrite($handle, $this->toCsvIncidents());
        rewind($handle);
        return $handle;
    }

    public function fopenStatutsCsvFile() {
        $handle = fopen("php://memory", "rw");
        fwrite($handle, $this->toCsvStatuts());
        rewind($handle);
        return $handle;
    }

    public function toCsvIncidents() {
        $csv = "date journee;mode;ligne;date de début de l'incident;date de fin de l'incident;duree incident total (minutes);duree perturbation total (minutes);duree blocage total (minutes);duree travaux total (minutes);origine incident;index evenement;date de début evenement;date de fin evenement;duree evenement total (minutes);statut evenement;origine evenement;titre evenement;message evenement;id incident;id evenement\n";
        foreach(Config::getLignes() as $mode => $lignes) {
            foreach(array_reverse($this->getDisruptions($mode)) as $disruption) {
                $i = 0;
                foreach(array_reverse($disruption->getImpactsOptimized()) as $impact) {
                    $csv .= implode(";",[
                        $this->getDateStart()->format('Y-m-d'),
                        $mode,
                        $impact->getLigne()->getName(),
                        $disruption->getDateStart()->format('Y-m-d H:i:s'),
                        $disruption->getDateEnd()->format('Y-m-d H:i:s'),
                        $disruption->getDurationMinutes(),
                        $disruption->getDurationStatutMinutes('pb'),
                        $disruption->getDurationStatutMinutes('bq'),
                        $disruption->getDurationStatutMinutes('tx'),
                        '"'.str_replace(['"', "\n"], ['\"', '\n'], $disruption->getOrigine()).'"',
                        $i,
                        $impact->getDateStart()->format('Y-m-d H:i:s'),
                        $impact->getDateEnd()->format('Y-m-d H:i:s'),
                        $impact->getDurationMinutes(),
                        $impact->getColorClass(),
                        '"'.str_replace(['"', "\n", ";"], ['\"', '\n', '.'], $impact->getOrigine()).'"',
                        '"'.str_replace(['"', "\n", ";"], ['\"', '\n', '.'], $impact->getTitle()).'"',
                        '"'.str_replace(['"', "\n", ";"], ['\"', '\n', '.'], $impact->getMessagePlainText()).'"',
                        explode(":", $disruption->getId())[1],
                        $impact->getId(),
                    ])."\n";
                    $i++;
                }
            }
        }
        return $csv;
    }

    public function toCsvStatuts() {
        $csv = "date journee;mode;ligne;date de début du statut;date de fin du statut;statut;ids incidents;origines\n";
        foreach(Config::getLignes() as $mode => $lignes) {
            foreach($lignes as $ligne => $ligneImg) {
                $statut = null;
                $dateStart = null;
                $origines = null;
                $ids = null;
                for($i = 0; $i < 1380; $i=$i+2) {
                    $date = (clone $this->getDateStart())->modify("+ ".$i." minutes");
                    $newStatut = strtoupper($this->getColorClass($i, $ligne));
                    $infos = $this->getInfo($i, $ligne);
                    $newOrigines = null;
                    $newIds = null;
                    if($infos && count($infos[0])) {
                        $newIds = implode("|", $infos[0]);
                    }
                    if($infos && count($infos[1])) {
                        $newOrigines = implode("|", $infos[1]);
                    }
                    if($statut == $newStatut && $origines == $newOrigines && $ids == $newIds) {
                        continue;
                    }
                    if($dateStart && $statut) {
                        $csv .= $this->getDateStart()->format('Y-m-d').";".$mode.";".$ligne.";".$dateStart->format('Y-m-d H:i:s').";".(clone $date)->format('Y-m-d H:i:s').";".$statut.";".$ids.";".$origines."\n";
                    }
                    if(in_array($newStatut, ['NO', 'E'])) {
                        $statut = null;
                        $origines = null;
                        $ids = null;
                        $dateStart = null;
                        continue;
                    }
                    $statut = $newStatut;
                    $origines = $newOrigines;
                    $ids = $newIds;
                    $dateStart = $date;
                }
                if($dateStart && $statut) {
                    $csv .= $this->getDateStart()->format('Y-m-d').";".$mode.";".$ligne.";".$dateStart->format('Y-m-d H:i:s').";".(clone $date)->modify('-1 second')->format('Y-m-d H:i:s').";".$statut.";".$ids.";".$origines."\n";
                }
            }
        }
        return $csv;
    }

}

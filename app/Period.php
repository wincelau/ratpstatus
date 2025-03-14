<?php

abstract class Period
{
    protected $dateStart = null;

    public function isToday() {

        $date = (new DateTime())->modify('-3 hours');

        return $this->getDateStart()->format('Ymd') == $date->format('Ymd');
    }

    public function getDateStart() {

        return $this->dateStart;
    }

    public function getDateStartKey() {

        return $this->getDateStart()->format($this->getDateFormat());
    }

    public function fopenIncidentsCsvFile() {
        return fopen(__DIR__.'/../datas/export/historique_incidents.csv', 'r');
    }

    public function fopenStatutsCsvFile() {
        return fopen(__DIR__.'/../datas/export/historique_statuts.csv', 'r');
    }

    public function getMotifs($mode) {
        $motifs = [];
        $handle = $this->fopenIncidentsCsvFile();
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if(strpos($data[0], 'date') === 0) {
                continue;
            }
            if(strpos(str_replace("-", "", $data[0]), $this->getDateStartKey()) !== 0) {
                continue;
            }
            if($data[10] != 0) {
                continue;
            }
            if($mode != $data[1]) {
                continue;
            }

            $motifs["TOTAL"]["TOTAL"]['count']++;
            $motifs["TOTAL"][$data[9]]['count']++;
            $motifs["TOTAL"][$data[9]]['total_duration']+=floatval($data[5]);
            $motifs["TOTAL"][$data[9]]['total_duration_bloquant']+=floatval($data[7]);

            $motifs[$data[2]]["TOTAL"]['count']++;
            $motifs[$data[2]][$data[9]]['count']++;
            $motifs[$data[2]][$data[9]]['total_duration']+=floatval($data[5]);
            $motifs[$data[2]][$data[9]]['total_duration_bloquant']+=floatval($data[7]);
        }
        fclose($handle);
        foreach($motifs as $ligne => $motifsLigne) {
            $motifs[$ligne] = array_map(function($a) {
                $a['total_duration'] = round($a['total_duration']);
                $a['total_duration_bloquant'] = round($a['total_duration_bloquant']);
                $a['average_duration'] = round($a['total_duration'] / $a['count']);
                $a['average_duration_bloquant'] = round($a['total_duration_bloquant'] / $a['count']);
                return $a;}, $motifsLigne);
            uasort($motifs[$ligne], function($a, $b) { return $a['total_duration'] < $b['total_duration']; });
        }

        uksort($motifs, function($a, $b) use ($mode) {
            if($a == "TOTAL") {
                return false;
            }

            if($b == "TOTAL") {
                return true;
            }

            $indexA = array_search($a, array_keys(Config::getLignes()[$mode]));
            $indexB = array_search($b, array_keys(Config::getLignes()[$mode]));

            return $indexA > $indexB;
        });

        return $motifs;
    }

    public function getStatuts($mode) {
        $handle = $this->fopenStatutsCsvFile();

        $statuts = [];
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if(strpos($data[0], 'date') === 0) {
                continue;
            }
            if($data[1] != $mode) {
                continue;
            }
            if(strpos(str_replace('-', '', $data[0]), $this->getDateStartKey()) !== 0) {
                continue;
            }
            $dateStart = new DateTime($data[3]);
            $dateEnd = new DateTime($data[4]);
            $duration = $dateEnd->diff($dateStart);
            if($this->getDateFormat() == 'Y') {
                $dateKey = explode("-", $data[0])[0]."-".explode("-", $data[0])[1];
            } else {
                $dateKey = $data[0];
            }

            if(!isset($statuts[$data[2]][$dateKey]["minutes"][$data[5]])) {
                $statuts[$data[2]][$dateKey]["minutes"][$data[5]] = 0;
            }
            if(!isset($statuts[$data[2]]["total"]["minutes"][$data[5]])) {
                $statuts[$data[2]]["total"]["minutes"][$data[5]] = 0;
            }
            if(!isset($statuts["total"][$dateKey]["minutes"][$data[5]])) {
                $statuts["total"][$dateKey]["minutes"][$data[5]] = 0;
            }
            if(!isset($statuts["total"]["total"]["minutes"][$data[5]])) {
                $statuts["total"]["total"]["minutes"][$data[5]] = 0;
            }

            $nbMinutes = ($duration->d * 24 * 60) + ($duration->h * 60) + $duration->i;
            $statuts[$data[2]][$dateKey]["minutes"][$data[5]] += $nbMinutes;
            $statuts[$data[2]]["total"]["minutes"][$data[5]] += $nbMinutes;
            $statuts["total"][$dateKey]["minutes"][$data[5]] += $nbMinutes;
            $statuts["total"]["total"]["minutes"][$data[5]] += $nbMinutes;
        }
        foreach($statuts as $ligne => $dates) {
            foreach($dates as $date => $data) {
                $total = array_sum($data["minutes"]);
                $pourcentages = array_map(function($a) use ($total) { return $total > 0 ? round($a / $total * 100) : 0; }, $data["minutes"]);
                if(!isset($pourcentages["OK"])) {
                    $pourcentages["OK"] = 0;
                }
                if(!isset($pourcentages["PB"])) {
                    $pourcentages["PB"] = 0;
                }
                if(!isset($pourcentages["TX"])) {
                    $pourcentages["TX"] = 0;
                }
                if(!isset($pourcentages["BQ"])) {
                    $pourcentages["BQ"] = 0;
                }
                $pourcentages["OK"] = round(100 - $pourcentages["PB"] - $pourcentages["BQ"] - $pourcentages["TX"], 2);
                $statuts[$ligne][$date]["pourcentages"] = $pourcentages;
            }
        }
        fclose($handle);

        return $statuts;
    }
}

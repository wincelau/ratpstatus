<?php

class View {

    public static function url($url) {
        if($GLOBALS['isStaticResponse']) {

            return $url;
        }

        preg_match('|/?([^/]*)/([^/]*).html|', $url, $matches);

        $script = "index.php";

        if(strlen($matches[1]) == "6") {
            $script = "month.php";
        }

        if(strlen($matches[1]) == "4") {
            $script = "year.php";
        }

        return $script."?".http_build_query(['date' => $matches[1], 'mode' => $matches[2]]);
    }

    public static function getDatesChoices() {
        $date = (new DateTime())->modify('-3 hours');
        $dates = [];
        $dates["Par jour"][$date->format('Ymd')] = "Aujourd'hui";
        $date->modify('-1 day');
        $dates["Par jour"][$date->format('Ymd')] = "Hier";
        while($date->format('Ym') >= "202404") {
            $dates["Par année"][$date->format('Y')] = $date->format('Y');
            $dates["Par mois"][$date->format('Ym')] = self::displayDateMonthToFr($date).' '.$date->format('Y');
            $date->modify('-1 month');
        }
        return $dates;
    }

    public static function displayDateMonthToFr($date, $substr = null) {
        $labels = [
            "01" => "Janvier",
            "02" => "Février",
            "03" => "Mars",
            "04" => "Avril",
            "05" => "Mai",
            "06" => "Juin",
            "07" => "Juillet",
            "08" => "Août",
            "09" => "Septembre",
            "10" => "Octobre",
            "11" => "Novembre",
            "12" => "Décembre",
        ];

        $labelShort = [
            "01" => "Janv",
            "02" => "Févr",
            "03" => "Mars",
            "04" => "Avril",
            "05" => "Mai",
            "06" => "Juin",
            "07" => "Juil",
            "08" => "Août",
            "09" => "Sept",
            "10" => "Oct",
            "11" => "Nov",
            "12" => "Déc",
        ];

        $label = str_replace(array_keys($labels), array_values($labels), $date->format('m'));
        if($substr) {
            $label = str_replace(array_keys($labelShort), array_values($labelShort), $date->format('m'));
        }

        return $label;
    }
}
